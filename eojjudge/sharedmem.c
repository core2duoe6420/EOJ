/* sharedmem.c
 * Auther: King
 *
 * This file is a part of eojjudge.
 * It is a copy of eojdaemon/sharedmem.c.
 *
 * Note that eojjudge should NOT call
 * shared_mem_create().
 */

#include <sys/shm.h>
#include <errno.h>
#include <string.h>
#include "eojjudge.h"

int shared_mem_create(struct shared_mem * shm, key_t key, size_t size) {
	shm->size = size;
	shm->key = key;
	if ((shm->shmid = shmget(key, size, IPC_CREAT | 0666)) == -1) {
		eoj_log("create shmid fail: %s", strerror(errno));
		return 1;
	}
	if ((shm->addr = shmat(shm->shmid, NULL, 0)) == (void*) -1) {
		eoj_log("shm attach fail: %s", strerror(errno));
		return 1;
	}
	return 0;
}

int shared_mem_get(struct shared_mem * shm, key_t key, size_t size) {
	shm->size = size;
	shm->key = key;
	if ((shm->shmid = shmget(key, size, 0666)) == -1) {
		eoj_log("get shmid fail: %s", strerror(errno));
		return 1;
	}
	if ((shm->addr = shmat(shm->shmid, NULL, SHM_RDONLY)) == (void*) -1) {
		eoj_log("shm attach fail: %s", strerror(errno));
		return 1;
	}
	return 0;
}

int shared_mem_dt(struct shared_mem * shm) {
	int retval = 0;
	if ((retval = shmdt(shm->addr)) == -1)
		eoj_log("shm detach fail: %s", strerror(errno));
	return retval;
}

int shared_mem_remove(struct shared_mem * shm) {
	int retval = 0;
	if ((retval = shmctl(shm->shmid, IPC_RMID, NULL )) == -1)
		eoj_log("shm remove fail: %s", strerror(errno));
	return retval;
}

void shared_mem_write(struct shared_mem * shm, void * src, size_t size) {
	memcpy(shm->addr, src, size);
}

void shared_mem_read(struct shared_mem * shm, void * dest, size_t size) {
	memcpy(dest, shm->addr, size);
}

