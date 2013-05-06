/* log.c
 * Auther: King
 *
 * This file is a part of eojdaemon.
 * It is a encapsulation for syslog.
 * Note that we use flag LOG_USER|LOG_DEBUG,
 * the rsyslogd in server is configured as
 * user.debug /var/log/eoj.log,
 *
 */

#include <stdarg.h>
#include <syslog.h>
#include "eoj.h"


int log_initial(char * cmd) {
	openlog(cmd,LOG_CONS,0);
	return 0;
}

void eoj_log(const char * msg, ...) {
	char strbuf[1024];

	va_list ap;
	va_start(ap, msg);
	vsprintf(strbuf, msg, ap);
	va_end(ap);

	syslog(LOG_USER | LOG_DEBUG,"%s",strbuf);
}

void log_close() {
	closelog();
}

