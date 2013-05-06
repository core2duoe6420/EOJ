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
#include <sys/wait.h>
#include "eojjudge.h"

enum result compare(struct request * req) {
	pid_t pid;
	char answer[EOJ_PATH_MAX];
	char result_file[EOJ_PATH_MAX];

	snprintf(answer, EOJ_PATH_MAX, "%s%d", req->answer_dir, req->pro_id);
	snprintf(result_file, EOJ_PATH_MAX, "%s%s%s", req->out_dir, req->fname_nosx,
			".result");

	pid = fork();
	if (pid == 0) {
		if (execl("/usr/bin/diff", "diff", "-B", "-w", answer, result_file,
				NULL ) == -1) {
			eoj_log("exec %s fail : %s", "diff", strerror(errno));
			exit(1);
		}
	}

	int status;
	if (waitpid(pid, &status, 0) != pid) {
		eoj_log("wait pid %d fail", pid);
		return SYS_ERROR;
	}

	if (!WIFEXITED(status))
		return SYS_ERROR;

	if (WEXITSTATUS(status) != 0)
		return WRONG_ANSWER;

	return ACCEPT;
}
