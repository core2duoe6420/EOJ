#include <mysql.h>
#include <stdarg.h>
#include <string.h>
#include <errno.h>
#include "eojjudge.h"

static MYSQL mysql_con;

int eoj_mysqlcon_init() {
	struct db_config * dbc;
	dbc = &configs->db_config;

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
		eoj_log("mysql connection fail");
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

static off_t get_code_len(char * fname) {
	struct stat buf;
	if (stat(fname, &buf) != 0) {
		eoj_log("can't get file length %s : %s", fname, strerror(errno));
		return 0;
	}
	return buf.st_size;
}

static int store_run_result(struct request * req, struct run_result * rr) {
	int ans_id, mcost, tcost, codetype, codelen, result;
	char * codeloc;

	mcost = rr->memory;
	tcost = rr->time;
	codeloc = req->complete_dest_file;
	codetype = req->cpl->id;
	codelen = get_code_len(req->complete_dest_file);
	result = rr->result;
	ans_id = req->run_id;

	char * sql;
	sql = config_get_value(&configs->db_config.sqls, "storeRunResult");

	int ret = query_sql(sql, mcost, tcost, codeloc, codetype, codelen, result,
			ans_id);
	if (ret == 0)
		eoj_log("store run result succeed");
	else
		eoj_log("store run result fail : %s", mysql_error(&mysql_con));
	return ret;
}

static int inc_prob_data(struct request * req, struct run_result * rr) {
	char * sql;
	if (rr->result == ACCEPT)
		sql = config_get_value(&configs->db_config.sqls, "incProbAC");
	else
		sql = config_get_value(&configs->db_config.sqls, "incProErr");

	int ret = query_sql(sql, req->pro_id);
	if (ret == 0)
		eoj_log("increase problem data succeed");
	else
		eoj_log("increase problem data fail : %s", mysql_error(&mysql_con));
	return ret;
}

int update_database(struct request * req,struct run_result * rr) {
	return store_run_result(req,rr) || inc_prob_data(req,rr);
}

