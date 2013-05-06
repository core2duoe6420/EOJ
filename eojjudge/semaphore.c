/* semaphore.c
 * Auther: King
 *
 * This file is a part of eojjudge.
 * It is a copy of eojdaemon/semaphore.c
 *
 * Note that eojjudge should NOT call
 * create_semaphore().
 *
 */

#include <errno.h>
#include "eojjudge.h"

sem_t * create_semaphore(char * name,int value) {
	sem_t * semid;
	if ((semid = sem_open(name, O_RDWR | O_CREAT | O_EXCL, 0666, value))
			== SEM_FAILED ) {
		//sem already exists
		eoj_log("Create semaphore fail.Trying to unlink");
		int errsv = errno;
		if (errsv == EEXIST) {
			if (sem_unlink(name)) {
				eoj_log("Unlink semahpore fail.exiting");
				return SEM_FAILED ;
			} else {
				eoj_log("Unlink semaphore succeed");
			}
		}
		//OK sem already unlinked.create again.
		//this time,we return whatever the result.
		semid = sem_open(name, O_RDWR | O_CREAT | O_EXCL, 0666, value);
	}
	if (semid == SEM_FAILED )
		eoj_log("Trying to get semahpore fail.daemon exiting");
	return semid;
}

sem_t * get_semaphore(char * name) {
	sem_t * semid;
	if((semid = sem_open(name,0)) == SEM_FAILED)
		eoj_log("Get semaphore fail");
	return semid;
}

void p_semaphore(sem_t * sem) {
	sem_wait(sem);
}

void v_semaphore(sem_t * sem) {
	sem_post(sem);
}

void del_semaphore(char * name,sem_t * sem) {
	sem_close(sem);
	sem_unlink(name);
}
