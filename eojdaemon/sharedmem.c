/* sharedmem.c
 * Auther: King
 *
 * This file is a part of eojdaemon.
 * It is a simple encapsulation for shm.
 * We use shm to share configurations include
 * dirs, compiler attributes and database parameters.
 * Thus we can avoid unneccessary copy bewteen
 * processes.
 *
 * Note that the child process should only read
 * shared area becuase we DO NOT take any action to
 * protect critical resource. We use flag SHM_RDONLY
 * to avoid illegal write in shared_mem_get(). Child
 * process should only use shared_mem_get() to open and
 * attach shared area. The key used to create shm
 * is hard coded.
 */

#include <sys/shm.h>
#include <errno.h>
#include <string.h>
#include "eoj.h"

static struct shared_mem shm_config;
extern struct shared_config configs;

//mark whether daemon is restarting or origin start
static int firststart = 1;

int shared_mem_create(struct shared_mem * shm, key_t key, size_t size) {
	firststart = 0;
	shm->size = size;
	shm->key = key;
	if ((shm->shmid = shmget(key, size, IPC_CREAT | 0666)) == -1) {
		eoj_log("Create shmid fail.");
		return 1;
	}
	if ((shm->addr = shmat(shm->shmid, NULL, 0)) == (void*) -1) {
		eoj_log("shm attach fail.%s", strerror(errno));
		return 1;
	}
	return 0;
}

int shared_mem_get(struct shared_mem * shm, key_t key, size_t size) {
	shm->size = size;
	shm->key = key;
	if ((shm->shmid = shmget(key, size, 0666)) == -1) {
		eoj_log("Get shmid fail.");
		return 1;
	}
	if ((shm->addr = shmat(shm->shmid, NULL, SHM_RDONLY)) == (void*) -1) {
		eoj_log("shm attach fail.%s", strerror(errno));
		return 1;
	}
	return 0;
}

int shared_mem_dt(struct shared_mem * shm) {
	int retval = 0;
	if ((retval = shmdt(shm->addr)) == -1)
		eoj_log("shm detach fail.");
	return retval;
}

int shared_mem_remove(struct shared_mem * shm) {
	int retval = 0;
	if ((retval = shmctl(shm->shmid, IPC_RMID, NULL )) == -1)
		eoj_log("shm remove fail.");
	return retval;
}

void shared_mem_write(struct shared_mem * shm, void * src, size_t size) {
	memcpy(shm->addr, src, size);
}

void shared_mem_read(struct shared_mem * shm, void * dest, size_t size) {
	memcpy(dest, shm->addr, size);
}

int configs_share() {
	if (!firststart) {
		shared_mem_dt(&shm_config);
		shared_mem_remove(&shm_config);
	}
	if ((shared_mem_create(&shm_config, 10000, sizeof(struct shared_config))))
		return 1;

	shared_mem_write(&shm_config, &configs, sizeof(struct shared_config));
	return 0;
}
