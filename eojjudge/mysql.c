#include <mysql.h>

#include "eojjudge.h"

static MYSQL mysql_con;

int eoj_mysqlcon_init() {
	if (mysql_init(&mysql_con) == NULL ) {
		eoj_log("mysql connection init fail");
		return 1;
	}

	unsigned int timeout = 10;
	if (mysql_options(&mysql_con, MYSQL_OPT_CONNECT_TIMEOUT,
			(char *) &timeout)) {
		eoj_log("mysql connection set timeout fail");
		return 1;
	}

	if (mysql_real_connect(&mysql_con, "127.0.0.1", "eojapp", "ecust", "eojdb",
			0, NULL, 0) == NULL ) {
		eoj_log("mysql connection fail");
		return 1;
	}

	return 0;
}
