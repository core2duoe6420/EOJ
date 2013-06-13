/* main.c
 * Auther: King
 *
 * This file is a part of eojdaemon.
 * It contains the entry of process main().
 * daemonize process,read xmlconfig,set shared memory,
 * and run deamon(). DO NOT mix the order.
 */

#include "eoj.h"

extern void config_initial();

int main()
{
	daemonize("eoj");
	if(already_running()) {
		eoj_log("eojdaemon already running");
		exit(1);
	}
	int daemon_ret;
	do {
		config_initial();
		if (xml_config("$EOJ_FILE_PATH/eoj.xml"))
			exit(1);
		config_set_print();
		param_initial();
		if (configs_share())
			exit(1);
		if ((daemon_ret = eoj_daemon()) == 1) {
			eoj_log("daemon start error");
			exit(1);
		}
	} while (daemon_ret == 2);
	return 0;
}
