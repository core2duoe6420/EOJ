/* mysql.c
 * Auther: King
 *
 * This file is a part of eojdaemon.
 * The part of encapsulation for mysql is the same
 * as eojjudge/mysql.c
 *
 * It is used to read problem limitation from
 * mysql database. When daemon starts or restarts
 * get_all_prob_limit() will read all the problems
 * and put them in the buffer.
 *
 * If a new problem is inserted into the database
 * NOTHING done by daemon. But a problem request
 * which is not in buffer will lead daemon connect
 * database again and read one row about problem.
 * This is done by update_prob_limit().
 */

#include <mysql.h>
#include <stdarg.h>
#include <string.h>
#include <errno.h>
#include "eoj.h"

static MYSQL mysql_con;

int eoj_mysqlcon_init() {
	struct db_config * dbc;
	dbc = &configs.db_config;

	if (mysql_init(&mysql_con) == NULL ) {
		eoj_log("mysql connection init fail");
		return 1;
	}

	if (mysql_options(&mysql_con, MYSQL_OPT_CONNECT_TIMEOUT,
			(char *) &dbc->timeout)) {
		eoj_log("mysql connection set timeout fail");
		return 1;
	}

	if (mysql_real_connect(&mysql_con, dbc->host, dbc->username, dbc->passwd,
			dbc->usedb, 0, NULL, 0) == NULL ) {
		eoj_log("mysql connection fail: %s", mysql_error(&mysql_con));
		return 1;
	}

	return 0;
}

void eoj_mysqlcon_close() {
	mysql_close(&mysql_con);
}

int query_sql(const char * sql, ...) {
	char sqlbuf[1024];

	va_list ap;
	va_start(ap, sql);
	vsprintf(sqlbuf, sql, ap);
	va_end(ap);

	return mysql_query(&mysql_con, sqlbuf);
}

int update_prob_limit(struct problems * probs, int prob_id) {
	int success = 0;
	if (eoj_mysqlcon_init()) {
		eoj_log("can't connect with mysql");
		return 1;
	}
	char * sql;
	MYSQL_RES * res_ptr;
	MYSQL_ROW row;
	sql = config_get_value(&configs.db_config.sqls, "getProLimitById");
	if (query_sql(sql, prob_id)) {
		eoj_log("query sql fail: %s", mysql_error(&mysql_con));
		return 1;
	}
	res_ptr = mysql_use_result(&mysql_con);
	if (res_ptr) {
		while ((row = mysql_fetch_row(res_ptr))) {
			while (prob_id >= probs->max) {
				eoj_log("problem id %d beyond buffer. reallocing", prob_id);
				if (prob_alloc(probs)) {
					mysql_free_result(res_ptr);
					eoj_mysqlcon_close();
					return 1;
				}
			}
			probs->limits[prob_id].exist = 1;
			probs->limits[prob_id].time = atoi(row[0]);
			probs->limits[prob_id].memory = atoi(row[1]);
			success = 1;
		}
		mysql_free_result(res_ptr);
	}
	eoj_mysqlcon_close();
	if (success)
		return 0;
	else
		return 1;
}

int get_all_prob_limit(struct problems * probs) {
	int prob_id;
	if (eoj_mysqlcon_init()) {
		eoj_log("can't connect with mysql");
		return 1;
	}
	char * sql;
	MYSQL_RES * res_ptr;
	MYSQL_ROW row;
	sql = config_get_value(&configs.db_config.sqls, "getProLimitAll");
	if (query_sql(sql)) {
		eoj_log("query sql fail : %s", mysql_error(&mysql_con));
		return 1;
	}
	res_ptr = mysql_use_result(&mysql_con);
	if (res_ptr) {
		while ((row = mysql_fetch_row(res_ptr))) {
			prob_id = atoi(row[0]);
			while (prob_id >= probs->max) {
				eoj_log("problem id %d beyond buffer. reallocing", prob_id);
				if (prob_alloc(probs)) {
					mysql_free_result(res_ptr);
					eoj_mysqlcon_close();
					return 1;
				}
			}
			probs->limits[prob_id].exist = 1;
			probs->limits[prob_id].time = atoi(row[1]);
			probs->limits[prob_id].memory = atoi(row[2]);
		}
		mysql_free_result(res_ptr);
	}
	eoj_mysqlcon_close();
	return 0;
}

