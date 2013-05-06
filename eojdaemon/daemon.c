/* daemon.c
 * Auther: King
 *
 * This file is a part of eojdaemon.
 * It contains the main subroutine daemon().
 * The function daemonize() is copied from apue.
 * main() will call daemonize() at the beginning,
 * and the process becomes a deamon process.
 *
 * daemon() reads into work_dir(which figured by configs),
 * to every file in work_dir,daemon() first calls test_file_accessible()
 * to test whether file can be moved using flock()(the file may be
 * flocked by php). If the file pass the test, daemon() calls
 * fill_request() to get an instance of struct run_request.
 * fill_request() gets info by parsing the file name and configs,
 * it also get limitations of problems by reading prepared buffer.
 * If the problem is out of buffer, it will read info from database,
 * and update buffer.Then the file will be moved to dest_dir, the
 * dest_dir contains more info includes problem_id and compiler name.
 * At last, the judge program is executed to finish the run request.
 * After the dir traversed, the proccess will sleep for 1 sec, and repeat
 * the routine.
 *
 */

#include <time.h>
#include <string.h>
#include <dirent.h>
#include <sys/file.h>
#include <sys/wait.h>
#include <sys/resource.h>

#include "eoj.h"

struct run_request {
	struct compiler * cpl;
	char pro_id[16];
	char run_id[16];
	char user_id[16];
	char suffix[EOJ_SUFFIX_MAX];
	char fname_nosx[EOJ_FILENAME_MAX];
	char complete_dest_file[EOJ_PATH_MAX];
	char complete_src_file[EOJ_PATH_MAX];
	char time_limit[16];
	char mem_limit[16];
};

/* on success return 0, error return 1 */
static int test_file_accessible(char * complete_file) {
	FILE * fp;
	int fd, ret;
	fp = fopen(complete_file, "r");
	//open fail. file might not exist.
	if (!fp)
		return 1;

	fd = fileno(fp);
	if (flock(fd, LOCK_EX | LOCK_NB)) {
		//get lock fail.
		ret = 1;
	} else {
		//success. we can move on
		flock(fd, LOCK_UN);
		ret = 0;
	}
	fclose(fp);
	return ret;
}

/* if the c_dir exists,then ok.
 * if not, try to create, if success, ok.
 * if create fail,return 1.
 */
static int test_dir_create(char * c_dir) {
	DIR * dir;
	dir = opendir(c_dir);
	if (dir) {
		closedir(dir);
		return 0;
	}
	//don't exist?
	if (mkdir(c_dir, 0775) != 0)
		//mk fail? might exist as a file
		return 1;
	else
		return 0;
}

/* truncate filename suffix to suffix array.
 * note that suffix in filename array is removed.
 */
static int split_suffix(char * filename, char * suffix, int limit) {
	int len, i;
	len = strlen(filename);
	for (i = len - 1; i >= 0; i--) {
		if (filename[i] == '.') {
			strncpy(suffix, filename + i, limit);
			filename[i] = 0;
			return 0;
		}
	}
	return 1;
}

static int get_limit(struct run_request * req, int pro_id) {
	snprintf(req->time_limit, sizeof(req->time_limit), "%d", 1000);
	snprintf(req->mem_limit, sizeof(req->mem_limit), "%d", 1000);
	return 0;
}

static int fill_request(struct run_request * req, char * ori_filename) {
	char tmp[EOJ_FILENAME_MAX];

	strncpy(tmp, ori_filename, EOJ_FILENAME_MAX);
	strncpy(req->fname_nosx, ori_filename, EOJ_FILENAME_MAX);
	split_suffix(req->fname_nosx, req->suffix, EOJ_SUFFIX_MAX);
	snprintf(req->complete_src_file, EOJ_PATH_MAX, "%s%s%s", work_dir,
			req->fname_nosx, req->suffix);

	req->cpl = get_compiler(req->suffix);
	if (req->cpl == NULL ) {
		eoj_log("unkonwn file type %s", req->complete_src_file);
		return 1;
	}

	char * ptr = tmp, *seg;

	seg = strsep(&ptr, "-");
	if (!seg)
		return 1;
	strncpy(req->run_id, seg, sizeof(req->run_id));

	seg = strsep(&ptr, "-");
	if (!seg)
		return 1;
	strncpy(req->pro_id, seg, sizeof(req->pro_id));

	seg = strsep(&ptr, "-");
	if (!seg)
		return 1;
	strncpy(req->user_id, seg, sizeof(req->user_id));

	char dest_pro_dir[EOJ_PATH_MAX];
	char dest_cpl_dir[EOJ_PATH_MAX];
	snprintf(dest_pro_dir, EOJ_PATH_MAX, "%s%d/", dest_dir, atoi(req->pro_id));
	snprintf(dest_cpl_dir, EOJ_PATH_MAX, "%s%s/", dest_pro_dir, req->cpl->name);

	if (test_dir_create(dest_pro_dir)) {
		eoj_log("access dir %s fail", dest_pro_dir);
		return 1;
	}
	if (test_dir_create(dest_cpl_dir)) {
		eoj_log("access dir %s fail", dest_cpl_dir);
		return 1;
	}

	snprintf(req->complete_dest_file, EOJ_PATH_MAX, "%s%s%s", dest_cpl_dir,
			req->fname_nosx, req->suffix);

	if (get_limit(req, atoi(req->pro_id))) {
		eoj_log("get problem %s limit fail", req->pro_id);
		return 1;
	}

	return 0;
}

static int move_file(const char * dest, const char * src) {
	pid_t pid;
	pid = fork();
	if (pid == 0) {
		int ret;
		ret = execl("/bin/mv", "mv", src, dest, NULL );
		if (ret == -1) {
			eoj_log("exec fail");
			return 1;
		}
	}
	return 0;
	/*
	 * the process is already a daemon, and in order to
	 * prevent zombie child proc, the handler for SIGCHLD
	 * is set to SIGIGN, which makes the daemon gives no
	 * response to SIGCHLD and wait can't work.
	 *
	 * The action may cause a problem, that is the judge
	 * program is already running and the file hasn't finished
	 * move action, the responsibility to check the file
	 * is transferred to judge program.
	 */
	/*
	 int exitcode;

	 if (waitpid(pid, &exitcode, 0) != pid) {
	 eoj_log("wait fail\n");
	 return 1;
	 }
	 if (!WIFEXITED(exitcode) || WEXITSTATUS(exitcode) != 0) {
	 eoj_log("mv fail\n");
	 return 1;
	 }
	 return 0;
	 */
}

static int run_request(struct run_request * req) {
	pid_t pid;
	char * argv[32];
	argv[0] = "eojgcc";
	argv[1] = req->pro_id;
	argv[2] = req->run_id;
	argv[3] = req->user_id;
	argv[4] = req->fname_nosx;
	argv[5] = req->suffix;
	argv[6] = req->complete_dest_file;
	argv[7] = req->time_limit;
	argv[8] = req->mem_limit;
	argv[9] = NULL;
	pid = fork();
	if (pid == 0) {
		if (execv(judge_exec, argv) == -1) {
			eoj_log("Run request fail");
			return 1;
		}
	}
	return 0;
}

int eoj_daemon() {
	struct run_request request;
	struct dirent * dirent;
	DIR * dir;
	sem_t * sem;

	if ((sem = create_semaphore("eoj", concurrency)) == SEM_FAILED )
		return 1;

	while (1) {
		dir = opendir(work_dir);
		while ((dirent = readdir(dir)) != NULL ) {
			if (strncmp(dirent->d_name, ".", EOJ_FILENAME_MAX) == 0
					|| strncmp(dirent->d_name, "..", EOJ_FILENAME_MAX) == 0)
				continue;
			int reqfail = fill_request(&request, dirent->d_name);
			if (test_file_accessible(request.complete_src_file)) {
				eoj_log("file %s unaccessible", request.complete_src_file);
				continue;
			}
			if (reqfail) {
				eoj_log("fill request %s fail", request.complete_src_file);
				move_file(err_dir, request.complete_src_file);
				continue;
			}

			p_semaphore(sem);
			eoj_log("%s", request.complete_src_file);
			move_file(request.complete_dest_file, request.complete_src_file);
			run_request(&request);
		}
		closedir(dir);
		sleep(1);
	}
	return 0;
}

//apue p343
void daemonize(char * cmd) {
	int i, fd0, fd1, fd2;
	pid_t pid;
	struct rlimit rl;
	struct sigaction sa;

	umask(0);

	if (getrlimit(RLIMIT_NOFILE, &rl) < 0) {
		printf("%s : can't get file limit\n", cmd);
		exit(EXIT_FAILURE);
	}

	if ((pid = fork()) < 0) {
		printf("%s : can't fork\n", cmd);
		exit(EXIT_FAILURE);
	} else if (pid != 0) {
		exit(EXIT_SUCCESS);
	}
	setsid();

	sa.sa_handler = SIG_IGN;
	sigemptyset(&sa.sa_mask);
	sa.sa_flags = 0;
	if (sigaction(SIGHUP, &sa, NULL ) < 0) {
		printf("%s: can't ignore SIGHUP\n", cmd);
		exit(EXIT_FAILURE);
	}
	if (sigaction(SIGCHLD, &sa, NULL ) < 0) {
		printf("%s: can't ignore SIGCHLD\n", cmd);
		exit(EXIT_FAILURE);
	}

	if ((pid = fork()) < 0) {
		printf("%s : can't fork\n", cmd);
		exit(EXIT_FAILURE);
	} else if (pid != 0) {
		exit(EXIT_SUCCESS);
	}

	if (chdir("/") < 0) {
		printf("%s : can't change directory to /\n", cmd);
		exit(EXIT_FAILURE);
	}

	if (rl.rlim_max == RLIM_INFINITY )
		rl.rlim_max = 1024;
	for (i = 0; i < rl.rlim_max; i++)
		close(i);

	fd0 = open("/dev/null", O_RDWR);
	fd1 = dup(0);
	fd2 = dup(0);

	log_initial(cmd);
	if (fd0 != 0 || fd1 != 1 || fd2 != 2) {
		eoj_log("unexpected file descriptors %d %d %d", fd0, fd1, fd2);
		exit(EXIT_FAILURE);
	}
}
