/* log.c
 * Auther: King
 *
 * This file is a part of eojjudge.
 * It is a copy of eojdaemon/log.c
 */

#include <string.h>
#include <stdarg.h>
#include <syslog.h>
#include "eojjudge.h"


int log_initial(char * cmd)
{
	openlog(cmd, LOG_CONS, 0);
	return 0;
}

void eoj_log(const char * msg, ...)
{
	char strbuf[1024];
	
	va_list ap;
	va_start(ap, msg);
	vsprintf(strbuf, msg, ap);
	va_end(ap);
	
	syslog(LOG_USER | LOG_DEBUG, "%s", strbuf);
}

void log_close()
{
	closelog();
}
