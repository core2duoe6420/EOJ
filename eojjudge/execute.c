/* execute.c
 * Auther: King
 *
 * This file is a part of eojjudge.
 * execute() is the middle step of judgement.
 * It execute the excutable file compiled by
 * compile() and redirect the output to specified
 * file.
 *
 * The main problem is to keep the execution safe.
 * We restrict system calls, set the time limit and
 * output limit. The memory use is checked every time
 * the process runs a system call.
 *
 * Note that unit of time is ms,memory is kb.
 */

#include <signal.h>
#include <syscall.h>
#include <sys/ptrace.h>
#include <sys/wait.h>
#include <errno.h>
#include <sys/user.h>
#include <sys/reg.h>
#include <sys/syscall.h>
#include <string.h>
#include <sys/time.h>
#include <sys/resource.h>
#include <asm/unistd_64.h>

#include "eojjudge.h"

static int check_syscall(long syscall) {
	static long allows[] = { __NR_read, __NR_write, __NR_brk, __NR_exit,
			__NR_execve, __NR_mmap, __NR_access, __NR_open, __NR_close,
			__NR_fstat, __NR_mprotect, __NR_arch_prctl, __NR_munmap,
			__NR_exit_group };

	for (int i = 0; i < sizeof(allows) / sizeof(allows[0]); i++)
		if (allows[i] == syscall)
			return 0;
	return 1;
}

static enum result check_mem_rusage(struct rusage * usage, struct rused * rused,
		unsigned int lmem) {
	unsigned int time, mem;
	time = (usage->ru_stime.tv_sec + usage->ru_utime.tv_sec) * 1000
			+ (usage->ru_stime.tv_usec + usage->ru_utime.tv_usec) / 1000;
	mem = usage->ru_maxrss;
//	mem = usage->ru_minflt * (getpagesize() / 1024);
	if (rused->memory < mem)
		rused->memory = mem;
	if (rused->time < time)
		rused->time = time;

	if (mem > lmem)
		return MEM_LIMIT_EXCEED;
	return RNORMAL;
}

static int io_redirect(char * c_stdin, char * c_stdout) {
	if (freopen(c_stdin, "r", stdin) == NULL )
		return 1;
	if (freopen(c_stdout, "w", stdout) == NULL )
		return 1;

	return 0;
}

static int set_limit(unsigned int ltime, unsigned int loutput) {
	struct rlimit limit;

	//time
	limit.rlim_max = (ltime + 999) / 1000 + 1;
	limit.rlim_cur = (ltime + 999) / 1000;
	if (setrlimit(RLIMIT_CPU, &limit) < 0)
		return 1;

	//stack
	limit.rlim_max = 4 * 1024 * 1024;
	limit.rlim_cur = 4 * 1024 * 1024;
	if (setrlimit(RLIMIT_STACK, &limit) < 0)
		return 1;

	//output
	limit.rlim_max = loutput * 1024;
	limit.rlim_cur = loutput * 1024;
	if (setrlimit(RLIMIT_FSIZE, &limit) < 0)
		return 1;

	return 0;
}

enum result execute(struct request * req, struct rused * rused) {
	pid_t pid;
	char outfile[EOJ_PATH_MAX];
	char complete_fname[EOJ_PATH_MAX];
	char fstdin[EOJ_PATH_MAX];
	char fstdout[EOJ_PATH_MAX];
	unsigned int ltime = req->time_limit, lmem = req->mem_limit;

	snprintf(outfile, EOJ_PATH_MAX, "%s%s", req->fname_nosx,
			req->cpl->execsuffix);
	snprintf(complete_fname, EOJ_PATH_MAX, "%s%s", req->out_dir, outfile);
	snprintf(fstdin, EOJ_PATH_MAX, "%s%d", req->input_dir, req->pro_id);
	snprintf(fstdout, EOJ_PATH_MAX, "%s%s%s", req->out_dir, req->fname_nosx,
			".result");
	add_file_records(&fcreat_record, fstdout);

	pid = fork();
	if (pid == 0) {
		if (set_limit(ltime, 1024)) {
			eoj_log("set rlimit error");
			exit(1);
		}
		if (io_redirect(fstdin, fstdout)) {
			eoj_log("ioredirect error");
			exit(1);
		}

		ptrace(PTRACE_TRACEME, 0, NULL, NULL );
		if (execl(complete_fname, outfile, NULL ) == -1) {
			eoj_log("exec fail : %s", strerror(errno));
			exit(1);
		}
	}
	enum result result;
	int status;
	struct rusage rusage;
	long orig_rax;
	while (1) {
		if (wait4(pid, &status, 0, &rusage) < 0) {
			eoj_log("wait error");
			return SYS_ERROR;
		}

		result = check_mem_rusage(&rusage, rused, lmem);
		if (result == MEM_LIMIT_EXCEED) {
			kill(pid, SIGKILL);
			//eoj_log("memory limit exceed");
			break;
		}

		if (WIFEXITED(status)) {
			result = RNORMAL;
			break;
		} else if (WSTOPSIG(status) != 5) {
			int sig = WSTOPSIG(status);
			switch (sig) {
			case SIGXCPU:
			case SIGKILL:
				//eoj_log("time limt exceed");
				result = TIME_LIMIT_EXCEED;
				break;
			case SIGXFSZ:
				//eoj_log("output limit exceed");
				result = OUTPUT_LIMIT_EXCEED;
				break;
			default:
				eoj_log("unknown error,sig %s", strsignal(sig));
				result = RUN_TIME_ERR;
				break;
			}
			kill(pid, SIGKILL);
			break;
		}

		/* it doesn't work.very strange */
//		else if (WIFSIGNALED(status)) {
//			int sig = WTERMSIG(status);
//			switch (sig) {
//			case SIGKILL:
//			case SIGXCPU:
//				eoj_log("%s time limt exceed", req->fname_nosx);
//				result = TIME_LIMIT_EXCEED;
//				break;
//			case SIGXFSZ:
//				result = OUTPUT_LIMIT_EXCEED;
//				eoj_log("%s output limit exceed", req->fname_nosx);
//				break;
//			default:
//				result = RUN_TIME_ERR;
//				break;
//			}
//			break;
//		}
		orig_rax = ptrace(PTRACE_PEEKUSER, pid, 8 * ORIG_RAX, NULL );
		if (orig_rax >= 0 && check_syscall(orig_rax)) {
			eoj_log("run illegal system call %ld", orig_rax);
			kill(pid, SIGKILL);
			result = RUN_TIME_ERR;
			break;
		}
		if (ptrace(PTRACE_SYSCALL, pid, NULL, NULL ) < 0) {
			//eoj_log("ptrace error");
			kill(pid, SIGKILL);
			result = SYS_ERROR;
			break;
		}

	}
	eoj_log("mem : %u kb time : %u ms", rused->memory, rused->time);
	return result;
}
