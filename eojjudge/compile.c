/* compile.c
 * Auther: King
 *
 * This file is part of eojjudge.
 * compile() is the first step of judgement.
 * The compiler info is sotred in the structure
 * request variable req,include compiling parameters.
 */

#include <errno.h>
#include <string.h>
#include <sys/wait.h>
#include "eojjudge.h"

enum result compile(struct request * req) {
	int i = 0;
	pid_t pid;
	char * argv[32];
	char dest_file[EOJ_PATH_MAX];

	snprintf(dest_file, EOJ_PATH_MAX, "%s%s%s", req->out_dir, req->fname_nosx,
			req->cpl->execsuffix);

	argv[i++] = req->cpl->name;
	for (; i < req->cpl->params_nr + 1; i++)
		argv[i] = req->cpl->params[i - 1];
	argv[i++] = "-o";
	argv[i++] = dest_file;
	argv[i++] = req->complete_dest_file;
	argv[i++] = NULL;

	pid = fork();
	if (pid == 0) {
		if (execv(req->cpl->execfile, argv) == -1) {
			eoj_log("exec %s fail : %s", req->cpl->execfile, strerror(errno));
			return SYS_ERROR;
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
		return COMPILE_ERR;

	add_file_records(&fcreat_record, dest_file);
	return RNORMAL;
}
