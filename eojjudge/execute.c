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
#include <limits.h>

#include "eojjudge.h"

#ifdef __i386__
#define WORDLEN 4
#define SYSCALL_NR 348
#define ORIG_AX ORIG_EAX
#else
#define WORDLEN 8
#define SYSCALL_NR 312
#define ORIG_AX ORIG_RAX
#endif

static int check_syscall(long syscall)
{
#ifdef __i386__
	static long allows[] = { SYS_read, SYS_write, SYS_brk, SYS_exit, SYS_execve,
	                         SYS_mmap2, SYS_access, SYS_open, SYS_close, SYS_fstat64, SYS_mprotect,
	                         SYS_munmap, SYS_exit_group
	                       };
#else
	static long allows[] = { SYS_read, SYS_write, SYS_brk, SYS_exit, SYS_execve,
	                         SYS_mmap, SYS_access, SYS_open, SYS_close, SYS_fstat, SYS_mprotect,
	                         SYS_arch_prctl, SYS_munmap, SYS_exit_group
	                       };
#endif
	                       
	static int times[SYSCALL_NR] = { 0, };
	static int allowtime[SYSCALL_NR] = { [SYS_execve] = 1, [SYS_open] = 4 };
	
	if (syscall == -1) {
		for (int i = 0; i < sizeof(allows) / sizeof(allows[0]); i++)
			if (allowtime[allows[i]] == 0)
				allowtime[allows[i]] = INT_MAX;
		for (int i = 0; i < sizeof(times) / sizeof(times[0]); i++)
			times[i] = 0;
		return 1;
	}
	times[syscall]++;
	if (times[syscall] > allowtime[syscall])
		return 1;
		
	return 0;
}

static enum result check_mem_rusage(struct rusage * usage,
                                    struct run_result * rused, unsigned int lmem)
{
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

static int io_redirect(char * c_stdin, char * c_stdout)
{
	if (freopen(c_stdin, "r", stdin) == NULL )
		return 1;
	if (freopen(c_stdout, "w", stdout) == NULL )
		return 1;
		
	return 0;
}

static int set_limit(unsigned int ltime, unsigned int loutput)
{
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

enum result execute(struct request * req, struct run_result * rused)
{
	pid_t pid;
	char outfile[EOJ_PATH_MAX];
	char complete_fname[EOJ_PATH_MAX];
	char fstdout[EOJ_PATH_MAX];
	unsigned int ltime = req->time_limit, lmem = req->mem_limit;
	
	//reset syscall times
	check_syscall(-1);
	snprintf(outfile, EOJ_PATH_MAX, "%s%s", req->fname_nosx,
	         req->cpl->execsuffix);
	snprintf(complete_fname, EOJ_PATH_MAX, "%s%s", req->out_dir, outfile);
	snprintf(fstdout, EOJ_PATH_MAX, "%s%s%s", req->out_dir, req->fname_nosx,
	         ".result");
	//add record only at first time
	if (access(fstdout, F_OK))
		add_file_records(&fcreat_record, fstdout);
		
	pid = fork();
	if (pid == 0) {
		if (set_limit(ltime, 1024)) {
			eoj_log("set rlimit error: %s", strerror(errno));
			exit(1);
		}
		if (io_redirect(req->input_file, fstdout)) {
			eoj_log("ioredirect error: %s", strerror(errno));
			exit(1);
		}
		
		ptrace(PTRACE_TRACEME, 0, NULL, NULL );
		if (execl(complete_fname, outfile, NULL ) == -1) {
			eoj_log("exec fail: %s", strerror(errno));
			exit(1);
		}
	}
	enum result result;
	int status;
	struct rusage rusage;
	long orig_ax;
	while (1) {
		if (wait4(pid, &status, 0, &rusage) < 0) {
			eoj_log("wait error: %s", strerror(errno));
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
		orig_ax = ptrace(PTRACE_PEEKUSER, pid, WORDLEN * ORIG_AX, NULL );
		if (orig_ax >= 0 && check_syscall(orig_ax)) {
			eoj_log("run illegal system call %ld", orig_ax);
			kill(pid, SIGKILL);
			result = RUN_TIME_ERR;
			break;
		}
		if (ptrace(PTRACE_SYSCALL, pid, NULL, NULL ) < 0) {
			eoj_log("ptrace error: %s", strerror(errno));
			kill(pid, SIGKILL);
			result = SYS_ERROR;
			break;
		}
	}
	
	return result;
}
