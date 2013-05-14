/* eojjudge.c
 * Auther: King
 *
 * This file is a part of eojjudge.
 * It contains the entry main() for eojjudge.
 * The arguments order is defined in eojdaemon/daemon.c
 * run_request().
 * The dir info,compilers and database configurations
 * are stored in the shared area.
 *
 */

#include <signal.h>
#include <sys/wait.h>
#include <errno.h>
#include <string.h>
#include <dirent.h>
#include <sys/time.h>

#include "eojjudge.h"

struct file_records fcreat_record;
struct shared_config * configs;

void add_file_records(struct file_records * fr, char * fname)
{
	if (fr->nr >= 32) {
		eoj_log("add file records fail: reach file uplimit");
		return;
	}
	
	strncpy(fr->records[fr->nr], fname, EOJ_PATH_MAX);
	fr->nr++;
}

void file_records_clean(struct file_records * fr)
{
	for (int i = 0; i < fr->nr; i++) {
		if (access(fr->records[i], F_OK) != 0) {
			eoj_log("clean error. file %s not exist", fr->records[i]);
			continue;
		}
		
		if (remove(fr->records[i]) != 0)
			eoj_log("trying to delete file %s fail: %s", fr->records[i],
			        strerror(errno));
			        
	}
}

char * config_get_value(struct config_set * set, char * attrname)
{
	for (int i = 0; i < set->config_nr; i++) {
		if (strncmp(attrname, set->attrs[i].name, ATTR_NAME_MAX) == 0)
			return set->attrs[i].value;
	}
	return NULL ;
}

static struct compiler * get_compiler(char * suffix)
{
	for (int i = 0; i < configs->compilers.count; i++)
		if (strncmp(configs->compilers.cpls[i].suffix, suffix, EOJ_SUFFIX_MAX)
		    == 0)
			return &configs->compilers.cpls[i];
	return NULL ;
}

static void fill_request(struct request * req, char * argv[])
{
	req->pro_id = atoi(argv[1]);
	req->run_id = atoi(argv[2]);
	req->user_id = atoi(argv[3]);
	req->fname_nosx = argv[4];
	req->suffix = argv[5];
	req->src_fname_withdir = argv[6];
	req->input_dir = config_get_value(&configs->global_config, "input_dir");
	req->answer_dir = config_get_value(&configs->global_config, "answer_dir");
	req->out_dir = config_get_value(&configs->global_config, "out_dir");
	req->time_limit = atoi(argv[7]);
	req->mem_limit = atoi(argv[8]);
	req->spec = atoi(argv[9]);
	req->cpl = get_compiler(req->suffix);
}

static void test_file_exist(char * fname)
{
	int repeat = 16;
	while (access(fname, F_OK) != 0) {
		repeat--;
		if (repeat == 0) {
			eoj_log("file %s not exists", fname);
			exit(1);
		}
		sleep(1);
	}
}

static char * strresult(enum result result)
{
	switch (result) {
	case SYS_ERROR:
			return "SYS_ERROR";
	case RNORMAL:
		return "NORMAL";
	case ACCEPT:
		return "ACCEPTED";
	case COMPILE_ERR:
		return "COMPILE ERROR";
	case RUN_TIME_ERR:
		return "RUNTIME ERROR";
	case MEM_LIMIT_EXCEED:
		return "MEMORY LIMIT EXCEEDED";
	case TIME_LIMIT_EXCEED:
		return "TIME LIMIT EXCEEDED";
	case OUTPUT_LIMIT_EXCEED:
		return "OUTPUT LIMIT EXCEEDED";
	case WRONG_ANSWER:
		return "WRONG ANSWER";
	case CODELEN_LIMIT_EXCEED:
		return "CODE LENGTH LIMIT EXCEED";
	}
	return NULL ;
}

/* used for multi-test */
static int get_next_run_file(struct request * req)
{
	static int idx = 1;
	snprintf(req->input_file, sizeof(req->input_file), "%s%d/%d",
	         req->input_dir, req->pro_id, idx);
	snprintf(req->real_answer_dir, sizeof(req->real_answer_dir), "%s%d/%d/",
	         req->answer_dir, req->pro_id, idx);
	         
	if (access(req->input_file, F_OK))
		return 1;
		
	DIR * dir;
	dir = opendir(req->real_answer_dir);
	if (dir == NULL ) {
		eoj_log("can't open dir %s: %s", req->real_answer_dir, strerror(errno));
		return 1;
	}
	closedir(dir);
	idx++;
	return 0;
}

extern enum result compile(struct request * req);
extern enum result execute(struct request * req, struct run_result * rused);
extern enum result compare(struct request * req);
extern int update_database(struct request * req, struct run_result * rr);

int main(int argc, char * argv[])
{
	signal(SIGCHLD, SIG_DFL );
	
	if (argc != 10)
		exit(1);
		
	char log_name[32];
	//argv[4] is fname_nosx,if argv changes,this must be changed.
	snprintf(log_name, sizeof(log_name), "eojgcc %s", argv[4]);
	log_initial(log_name);
	
	struct shared_mem shm;
	if (shared_mem_get(&shm, 10000, sizeof(struct shared_config))) {
		eoj_log("get shared mem fail");
		exit(1);
	}
	
	sem_t * sem;
	if ((sem = get_semaphore("eoj")) == SEM_FAILED ) {
		shared_mem_dt(&shm);
		exit(1);
	}
	
	struct request req;
	struct run_result rr = { 0, 0, 0 };
	configs = (struct shared_config *) shm.addr;
	
	fill_request(&req, argv);
	
	//in case the file is being moved
	test_file_exist(req.src_fname_withdir);
	
	if ((rr.result = compile(&req)) == RNORMAL) {
		while (get_next_run_file(&req) == 0) {
			if ((rr.result = execute(&req, &rr)) == RNORMAL) {
				rr.result = compare(&req);
				if (rr.result != ACCEPT)
					break;
			} else {
				break;
			}
		}
	}
	
	eoj_log("mem: %ukb time: %ums result: %s", rr.memory, rr.time,
	        strresult(rr.result));
	        
	file_records_clean(&fcreat_record);
	
	if(update_database(&req, &rr))
		eoj_log("update database error");
		
	shared_mem_dt(&shm);
	sem_post(sem);
	sem_close(sem);
	return 0;
}
