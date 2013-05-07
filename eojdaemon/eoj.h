/* eoj.h
 * Auther: King
 *
 * This file is a part of eojdaemon.
 */

#ifndef _EOJ_H
#define _EOJ_H

#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <fcntl.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <semaphore.h>

#define CONFIG_EFILE 100
#define CONFIG_EASSIGN 101

#define LOG_EFILE 200

#define EOJ_SUFFIX_MAX 32
#define EOJ_FILENAME_MAX 256
#define EOJ_PATH_MAX 512
#define EOJ_PARAMS_MAX 32
#define EOJ_CMD_MAX 64

//defined in config.c
#define ATTR_MAX 32
#define ATTR_NAME_MAX 128
#define ATTR_VALUE_MAX 128

struct attr {
	char name[ATTR_NAME_MAX];
	char value[ATTR_VALUE_MAX];
};

struct config_set {
	int config_nr;
	struct attr attrs[ATTR_MAX];
};

#define COMPILER_MAX 8

struct compiler {
	int id;
	char name[32];
	char suffix[EOJ_SUFFIX_MAX];
	char execfile[EOJ_PATH_MAX];
	char execsuffix[EOJ_SUFFIX_MAX];
	char params[EOJ_PARAMS_MAX][EOJ_CMD_MAX];
	int params_nr;
};

struct compiler_set {
	int count;
	struct compiler cpls[COMPILER_MAX];
};

struct db_config {
	unsigned int timeout;
	char host[16];
	char username[ATTR_VALUE_MAX];
	char passwd[ATTR_VALUE_MAX];
	char usedb[ATTR_VALUE_MAX];
	struct config_set sqls;
};

struct shared_config {
	struct config_set global_config;
	struct compiler_set compilers;
	struct db_config db_config;
};

extern struct shared_config configs;

extern int xml_config(char * xmlfile);
extern void config_set_print();
extern char * config_get_value(struct config_set * set, char * attrname);
extern char * global_config_get_value(char * attrname);
extern void compilers_initial();
extern struct compiler * get_compiler(char * suffix);

//defined in param.c
extern const char * work_dir;
extern const char * dest_dir;
extern const char * input_dir;
extern const char * answer_dir;
extern const char * out_dir;
extern const char * err_dir;
extern const char * judge_exec;
extern int concurrency;
extern int prob_max;

struct prob_limit {
	int exist;
	unsigned int time;
	unsigned int memory;
};

struct problems {
	int max;
	struct prob_limit * limits;
};

extern struct prob_limit * get_prob_limit(int prob_id);
extern int prob_alloc(struct problems * prob);
//defined in mysql.c
extern int get_all_prob_limit(struct problems * probs);
extern int update_prob_limit(struct problems * probs, int prob_id);
extern void param_initial();

//defined in log.c
extern int log_initial();
extern void eoj_log(const char * msg, ...);
extern void log_close();

//defined in daemon.c
extern void daemonize(char * cmd);
extern int eoj_daemon();
extern int already_running();

//defined in semaphore.c
extern sem_t * create_semaphore(char * name, int value);
extern sem_t * get_semaphore(char * name);
extern void p_semaphore(sem_t * sem);
extern void v_semaphore(sem_t * sem);
extern void del_semaphore(char * name, sem_t * sem);

//defined in sharedmem.c
struct shared_mem {
	int shmid;
	void * addr;
	size_t size;
	key_t key;
};
extern int configs_share();
extern int shared_mem_create(struct shared_mem * shm, key_t key, size_t size);
extern int shared_mem_get(struct shared_mem * shm, key_t key, size_t size);
extern int shared_mem_dt(struct shared_mem * shm);
extern void shared_mem_read(struct shared_mem * shm, void * dest, size_t size);
extern void shared_mem_write(struct shared_mem * shm, void * src, size_t size);
extern int shared_mem_remove(struct shared_mem * shm);

#endif // _EOJ_CONFIG_H
