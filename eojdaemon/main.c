/* main.c
 * Auther: King
 *
 * This file is a part of eojdaemon.
 * It contains the entry of process main().
 * daemonize process,read xmlconfig,set shared memory,
 * and run deamon(). DO NOT mix the order.
 */

#include "eoj.h"

int main() {
	daemonize("eoj");
	if (xml_config("/home/corei7/Project/eojdaemon/eoj.xml"))
		exit(1);
	config_set_print();
	param_initial();
	configs_share();
	if (eoj_daemon()) {
		eoj_log("Daemon error\n");
		exit(1);
	}
	return 0;
}
