/* eojjudge.h
 * Auther: King
 *
 * This file is a part of eojjudge.
 * It is similar to eojdaemon/eoj.h,
 * but has something more, such as
 * enum result to defined the constant
 * of run result. It should be same as
 * the other part in the system,like php.
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

#define ATTR_MAX 32
#define ATTR_NAME_MAX 128
#define ATTR_VALUE_MAX 128
#define COMPILER_MAX 8

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

extern char * config_get_value(struct config_set * set, char * attrname);

extern struct shared_config * configs;

//defined in log.c
extern int log_initial();
extern void eoj_log(const char * msg, ...);
extern void log_close();

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
extern int compiler_set_share();
extern int shared_mem_create(struct shared_mem * shm, key_t key, size_t size);
extern int shared_mem_get(struct shared_mem * shm, key_t key, size_t size);
extern int shared_mem_dt(struct shared_mem * shm);
extern void shared_mem_read(struct shared_mem * shm, void * dest, size_t size);
extern void shared_mem_write(struct shared_mem * shm, void * src, size_t size);
extern int shared_mem_remove(struct shared_mem * shm);

//1MB
#define CODELEN_MAX (1024*1024)

struct request {
	struct compiler * cpl;
	int pro_id;
	int run_id;
	int user_id;
	off_t codelen;	//unit:kb
	unsigned int time_limit;
	unsigned int mem_limit;
	char * fname_nosx;
	char * suffix;
	char * src_fname_withdir;
	const char * input_dir;
	const char * answer_dir;
	const char * out_dir;
};

enum result {
	SYS_ERROR = -1,
	RNORMAL = 0,
	ACCEPT = 1,
	COMPILE_ERR = 2,
	RUN_TIME_ERR = 3,
	TIME_LIMIT_EXCEED = 4,
	MEM_LIMIT_EXCEED = 5,
	OUTPUT_LIMIT_EXCEED = 6,
	WRONG_ANSWER = 7,
	CODELEN_LIMIT_EXCEED =8,
};

struct run_result {
	unsigned int memory;	//kb
	unsigned int time;		//ms
	enum result result;
};

struct file_records {
	int nr;
	char records[32][EOJ_PATH_MAX];
};

extern void add_file_records(struct file_records * fr, char * fname);
extern struct file_records fcreat_record;

//defined in mysql.c
extern int eoj_mysqlcon_init();
extern void eoj_mysqlcon_close();
#endif // _EOJ_CONFIG_H
