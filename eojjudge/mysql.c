/* mysql.c
 * Auther: King
 *
 * This file is a part of eojjudge
 * The part of encapsulation for mysql is the same
 * as eojdaemon/mysql.c.
 *
 * It is used to update result in mysql database
 * after the request finishes running.
 *
 * To one run request,the job of eojdaemon and eojjudge
 * is done. Later php will read result from database
 * and show on the web page.
 */
#include <mysql.h>
#include <stdarg.h>
#include <string.h>
#include <errno.h>
#include "eojjudge.h"

static MYSQL mysql_con;

int eoj_mysqlcon_init()
{
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
	                       dbc->usedb, dbc->port, NULL, CLIENT_MULTI_STATEMENTS) == NULL ) {
		eoj_log("mysql connection fail: %s", mysql_error(&mysql_con));
		return 1;
	}
	
	return 0;
}

void eoj_mysqlcon_close()
{
	mysql_close(&mysql_con);
}

int query_sql(const char * sql, ...)
{
	char sqlbuf[1024];
	
	va_list ap;
	va_start(ap, sql);
	vsnprintf(sqlbuf, sizeof(sqlbuf), sql, ap);
	va_end(ap);
	
	return mysql_query(&mysql_con, sqlbuf);
}

int query_real_sql(const char * sql, ...)
{
	char sqlbuf[1024];
	int len;
	
	va_list ap;
	va_start(ap, sql);
	len = vsnprintf(sqlbuf, sizeof(sqlbuf), sql, ap);
	va_end(ap);
	
	return mysql_real_query(&mysql_con, sqlbuf, len);
}

static int store_run_result(struct request * req, struct run_result * rr)
{
	int run_id, pro_id, user_id, mcost, tcost, codetype, codelen, result;
	char * codeloc;
	
	mcost = rr->memory;
	tcost = rr->time;
	codeloc = req->src_fname_withdir;
	codetype = req->cpl->id;
	codelen = req->codelen;
	result = rr->result;
	run_id = req->run_id;
	pro_id = req->pro_id;
	user_id = req->user_id;
	
	char * sql;
	sql = config_get_value(&configs->db_config.sqls, "storeResultRunProc");
	if(query_real_sql(sql, run_id, pro_id, user_id, mcost,
	                  tcost, codeloc, codetype, codelen, result)) {
		eoj_log("run store procedure fail: %s", mysql_error(&mysql_con));
		return 1;
	}
	/*
	sql = config_get_value(&configs->db_config.sqls, "storeResultProcGetExitcode");
	if(query_sql(sql)) {
		eoj_log("get store procedure result fail: %s", mysql_error(&mysql_con));
		return 1;
	}
	*/
	MYSQL_RES * res_ptr;
	MYSQL_ROW row;
	int proc_ret = 1;
	res_ptr = mysql_use_result(&mysql_con);
	if (res_ptr) {
		row = mysql_fetch_row(res_ptr);
		proc_ret = atoi(row[0]);
		mysql_free_result(res_ptr);
	}
	if(proc_ret)
		eoj_log("store procedure return error. check database");
	return proc_ret;
}

int update_database(struct request * req, struct run_result * rr)
{
	int ret = 1;
	if (eoj_mysqlcon_init() == 0) {
		ret = store_run_result(req, rr);
		eoj_mysqlcon_close();
	}
	return ret;
}
