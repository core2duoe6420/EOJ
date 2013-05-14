/* param.c
 * Auther: King
 *
 * This file is part of eojdaemon.
 * It reads parameters from structure config_set
 * global variable configs.global_config which has
 * been filled by xml config file.
 *
 * We have some default value for each parameter,
 * they are hard coded.If the parameter doesn't exist
 * in the config_set, we user default value.
 *
 * Note that concurrency determines the max run request
 * at the same time. It has an upper bound CONCURRENCY_MAX,
 * the concurrency value can't be above that value.
 */

#include <string.h>

#include "eoj.h"

#define WORK_DIR_DEFAULT "/home/corei7/Project/eojback/work/"
#define DEST_DIR_DEFAULT "/home/corei7/Project/eojback/codes/"
#define INPUT_DIR_DEFAULT "/home/corei7/Project/eojback/input/"
#define ANSWER_DIR_DEFAULT "/home/corei7/Project/eojback/answer/"
#define OUT_DIR_DEFAULT "/home/corei7/Project/eojback/tmp/"
#define ERR_DIR_DEFAULT "/home/corei7/Project/eojback/err/"
#define JUDGE_EXEC_DEFAULT "/home/corei7/Project/eojjudge/Debug/eojjudge"
#define CONCURRENCY_MAX 256
#define CONCURRENCY_DEFAULT 32

#define PROB_INCREMENT 5000

const char * work_dir;
const char * dest_dir;
const char * input_dir;
const char * answer_dir;
const char * out_dir;
const char * err_dir;
const char * judge_exec;
int concurrency;

struct problems probs = { 0, 0, };

struct prob_limit * get_prob_limit(int prob_id)
{
	if (prob_id >= probs.max || probs.limits[prob_id].exist == 0) {
		if (update_prob_limit(&probs, prob_id)) {
			eoj_log("get problem limitation fail");
			return NULL ;
		}
	}
	return probs.limits + prob_id;
}

int prob_alloc(struct problems * prob)
{
	int ori_max = prob->max;
	prob->limits = realloc(prob->limits,
	                       (prob->max + PROB_INCREMENT) * sizeof(struct prob_limit));
	if (!prob->limits) {
		eoj_log("alloc problems fail");
		return 1;
	}
	prob->max += PROB_INCREMENT;
	for (int i = ori_max; i < prob->max; i++)
		prob->limits[i].exist = 0;
	return 0;
}

char * check_dir(char * dir)
{
	struct stat statbuf;
	if (stat(dir, &statbuf))
		return NULL ;
		
	if (!S_ISDIR(statbuf.st_mode))
		return NULL ;
		
	int len = strlen(dir);
	if (dir[len - 1] != '/') {
		dir[len] = '/';
		dir[len + 1] = 0;
	}
	return dir;
}

/*
 static char * get_dir(char * buf, int limit, char * key, char * init) {
 char * dir;
 dir = global_config_get_value(key);
 if (!dir)
 dir = init;

 strncpy(buf, dir, limit);

 return check_dir(buf);
 }
 */
static int get_concurrency()
{
	int con;
	char * con_config;
	con_config = global_config_get_value("concurrency");
	if (!con_config)
		return CONCURRENCY_DEFAULT;
		
	con = atoi(con_config);
	if (con > CONCURRENCY_MAX || con <= 0)
		con = CONCURRENCY_MAX;
	return con;
}

void param_initial()
{
	const char * value;
	value = global_config_get_value("work_dir");
	work_dir = value ? value : WORK_DIR_DEFAULT;
	value = global_config_get_value("dest_dir");
	dest_dir = value ? value : DEST_DIR_DEFAULT;
	value = global_config_get_value("input_dir");
	input_dir = value ? value : INPUT_DIR_DEFAULT;
	value = global_config_get_value("answer_dir");
	answer_dir = value ? value : ANSWER_DIR_DEFAULT;
	value = global_config_get_value("out_dir");
	out_dir = value ? value : OUT_DIR_DEFAULT;
	value = global_config_get_value("err_dir");
	err_dir = value ? value : ERR_DIR_DEFAULT;
	value = global_config_get_value("judge_exec");
	judge_exec = value ? value : JUDGE_EXEC_DEFAULT;
	/*
	 get_dir(work_dir, EOJ_PATH_MAX, "work_dir", WORK_DIR_DEFAULT);
	 get_dir(dest_dir, EOJ_PATH_MAX, "dest_dir", DEST_DIR_DEFAULT);
	 get_dir(input_dir, EOJ_PATH_MAX, "input_dir", INPUT_DIR_DEFAULT);
	 get_dir(answer_dir, EOJ_PATH_MAX, "answer_dir", ANSWER_DIR_DEFAULT);
	 get_dir(out_dir, EOJ_PATH_MAX, "out_dir", OUT_DIR_DEFAULT);
	 get_dir(err_dir,EOJ_PATH_MAX,"err_dir",ERR_DIR_DEFAULT);
	 */
	concurrency = get_concurrency();
	
	if (get_all_prob_limit(&probs))
		exit(1);
}
