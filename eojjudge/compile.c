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

static off_t get_code_len(char * fname) {
	struct stat buf;
	if (stat(fname, &buf) != 0) {
		eoj_log("can't get file length %s: %s", fname, strerror(errno));
		return 0;
	}
	return buf.st_size;
}

enum result compile(struct request * req) {
	int i = 0;
	pid_t pid;
	char * argv[32];
	char dest_file[EOJ_PATH_MAX];

	req->codelen = get_code_len(req->src_fname_withdir);
	if(req->codelen > CODELEN_MAX)
		return CODELEN_LIMIT_EXCEED;

	snprintf(dest_file, EOJ_PATH_MAX, "%s%s%s", req->out_dir, req->fname_nosx,
			req->cpl->execsuffix);

	argv[i++] = req->cpl->name;
	for (; i < req->cpl->params_nr + 1; i++)
		argv[i] = req->cpl->params[i - 1];
	argv[i++] = "-o";
	argv[i++] = dest_file;
	argv[i++] = req->src_fname_withdir;
	argv[i++] = NULL;

	pid = fork();
	if (pid == 0) {
		if (execv(req->cpl->execfile, argv) == -1) {
			eoj_log("exec %s fail: %s", req->cpl->execfile, strerror(errno));
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

	if (WEXITSTATUS(status) != 0)
		return COMPILE_ERR;

	add_file_records(&fcreat_record, dest_file);
	return RNORMAL;
}
