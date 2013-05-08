/* compare.c
 * Auther: King
 *
 * This file is a part of eojjudge.
 * compare() is the last step of judgement.
 * It uses tool diff to compare the right answer
 * and result generated by the program.
 */

#include <errno.h>
#include <string.h>
#include <dirent.h>
#include <sys/wait.h>
#include "eojjudge.h"

enum result compare(struct request * req) {
	pid_t pid;
	char answer[EOJ_PATH_MAX];
	char result_file[EOJ_PATH_MAX];
	snprintf(result_file, EOJ_PATH_MAX, "%s%s%s", req->out_dir, req->fname_nosx,
			".result");

	DIR * dir;
	struct dirent * dirent;
	dir = opendir(req->real_answer_dir);
	if (dir == NULL ) {
		eoj_log("can't open answer dir %s: %s", req->real_answer_dir,
				strerror(errno));
		return SYS_ERROR;
	}

	/* normal problem should only have one answer file
	 * but special problem can have several answer files
	 * whatever problem, if there is one answer file
	 * meet the result,we judge it as ACCPET.
	 */
	while ((dirent = readdir(dir))) {
		if (strncmp(dirent->d_name, ".", 2) == 0
				|| strncmp(dirent->d_name, "..", 3) == 0)
			continue;

		snprintf(answer, EOJ_PATH_MAX, "%s%s", req->real_answer_dir,
				dirent->d_name);

		pid = fork();
		if (pid == 0) {
			if (execl("/usr/bin/diff", "diff", "-B", "-w", answer, result_file,
					NULL ) == -1) {
				eoj_log("exec %s fail: %s", "diff", strerror(errno));
				exit(1);
			}
		}

		int status;
		if (waitpid(pid, &status, 0) != pid) {
			eoj_log("wait pid %d fail: %s", pid, strerror(errno));
			return SYS_ERROR;
		}

		if (!WIFEXITED(status))
			return SYS_ERROR;

		if (WEXITSTATUS(status) == 0)
			return ACCEPT;
	}
	closedir(dir);
	return WRONG_ANSWER;
}
