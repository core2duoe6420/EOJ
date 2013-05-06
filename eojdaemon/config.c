/* config.c
 * Auther: King
 *
 * This file is a part of eojdaemon.
 * It reads configurations of eojdaemon from
 * an xml file using libxml2.
 * The xml config file should contains those
 * nodes:
 * <compiler> includes children <name><suffix><execfile><execsuffix>
 * <database> includes children <host><username><passwd><usedb>
 * note that other children of <database> is sql command for
 * specified using,such as <getProLimit><storeRunResult>
 *
 * The xml file and resources allocated during parsing
 * is freed after parsing and results are stored in a structure
 * shared_config.
 *
 * the shared_config structure variable configs will be later copied
 * to a shared memory area so that the configs can be read by the judge
 * program.
 *
 */

#include <string.h>
#include <libxml/parser.h>

#include "eoj.h"

extern char * check_dir(char * dir);
struct shared_config configs;

static void config_set_check_dir(struct config_set * set) {
	for (int i = 0; i < set->config_nr; i++) {
		struct attr * attr = set->attrs + i;
		if (strstr(attr->name, "dir"))
			check_dir(attr->value);
	}
}

static struct compiler * _get_compiler(struct compiler_set * compilers,
		char * suffix) {
	for (int i = 0; i < compilers->count; i++)
		if (strncmp(compilers->cpls[i].suffix, suffix, EOJ_SUFFIX_MAX) == 0)
			return &compilers->cpls[i];
	return NULL ;
}

struct compiler * get_compiler(char * suffix) {
	return _get_compiler(&configs.compilers, suffix);
}

static int config_set_add(struct config_set * set, const char * name,
		const char * value) {
	struct attr * attr;
	if (set->config_nr >= ATTR_MAX)
		return 1;
	attr = set->attrs + set->config_nr++;
	strncpy(attr->name, name, ATTR_NAME_MAX);
	strncpy(attr->value, value, ATTR_VALUE_MAX);
	return 0;
}

static void config_set_clear(struct config_set * set) {
	for (int i = 0; i < set->config_nr; i++) {
		strcpy(set->attrs[i].name, "");
		strcpy(set->attrs[i].value, "");
	}
	set->config_nr = 0;
}

static void _config_set_print(struct config_set * set) {
	for (int i = 0; i < set->config_nr; i++) {
		struct attr * attr_ptr;
		attr_ptr = set->attrs + i;
		eoj_log("%d %s = %s\n", i, attr_ptr->name, attr_ptr->value);
	}
}

void config_set_print() {
	_config_set_print(&configs.global_config);
}

char * config_get_value(struct config_set * set, char * attrname) {
	for (int i = 0; i < set->config_nr; i++) {
		if (strncmp(attrname, set->attrs[i].name, ATTR_NAME_MAX) == 0)
			return set->attrs[i].value;
	}
	return NULL ;
}

char * global_config_get_value(char * attrname) {
	return config_get_value(&configs.global_config, attrname);
}

static int compiler_add_param(struct compiler * cpl, char * param) {
	if (cpl->params_nr >= EOJ_PARAMS_MAX)
		return 1;

	strncpy(cpl->params[cpl->params_nr++], param, EOJ_CMD_MAX);
	return 0;
}

static int setup_database(struct db_config * db, struct config_set * set) {
	int has_host = 0, has_user = 0, has_passwd = 0, has_db = 0;
	//default;
	db->timeout = 10;

	for (int i = 0; i < set->config_nr; i++) {
		struct attr * attr;
		attr = set->attrs + i;
		if (strncmp(attr->name, "host", ATTR_NAME_MAX) == 0) {
			strncpy(db->host, attr->value, sizeof(db->host));
			has_host = 1;
		} else if (strncmp(attr->name, "username", ATTR_NAME_MAX) == 0) {
			strncpy(db->username, attr->value, sizeof(db->username));
			has_user = 1;
		} else if (strncmp(attr->name, "passwd", ATTR_NAME_MAX) == 0) {
			strncpy(db->passwd, attr->value, sizeof(db->passwd));
			has_passwd = 1;
		} else if (strncmp(attr->name, "usedb", ATTR_NAME_MAX) == 0) {
			strncpy(db->usedb, attr->value, sizeof(db->usedb));
			has_db = 1;
		} else if (strncmp(attr->name, "timeout", ATTR_NAME_MAX) == 0) {
			db->timeout = atoi(attr->value);
		} else {
			config_set_add(&db->sqls, attr->name, attr->value);
		}
	}

	if (has_host && has_user && has_passwd && has_db)
		return 0;
	else
		return 1;
}

/* there may be several compilers */
static int setup_compiler(struct compiler_set * cpl_set,
		struct config_set * set) {
	if (cpl_set->count >= COMPILER_MAX)
		return 1;

	int has_id = 0, has_name = 0, has_suffix = 0, has_exec = 0,
			has_exec_suf = 0;

	struct compiler * cpl;
	cpl = &cpl_set->cpls[cpl_set->count];

	for (int i = 0; i < set->config_nr; i++) {
		struct attr * attr;
		attr = set->attrs + i;
		if (strncmp(attr->name, "id", ATTR_NAME_MAX) == 0) {
			cpl->id = atoi(attr->value);
			has_id = 1;
		} else if (strncmp(attr->name, "name", ATTR_NAME_MAX) == 0) {
			strncpy(cpl->name, attr->value, sizeof(cpl->name));
			has_name = 1;
		} else if (strncmp(attr->name, "suffix", ATTR_NAME_MAX) == 0) {
			strncpy(cpl->suffix, attr->value, sizeof(cpl->suffix));
			has_suffix = 1;
		} else if (strncmp(attr->name, "execfile", ATTR_NAME_MAX) == 0) {
			strncpy(cpl->execfile, attr->value, sizeof(cpl->execfile));
			has_exec = 1;
		} else if (strncmp(attr->name, "execsuffix", ATTR_NAME_MAX) == 0) {
			strncpy(cpl->execsuffix, attr->value, sizeof(cpl->execsuffix));
			has_exec_suf = 1;
		} else if (strncmp(attr->name, "param", ATTR_NAME_MAX) == 0) {
			compiler_add_param(cpl, attr->value);
		}
	}

	if (has_id && has_exec && has_suffix && has_exec && has_exec_suf) {
		cpl_set->count++;
		return 0;
	} else {
		return 1;
	}
}

static void xml_get_children(xmlNodePtr node, struct config_set * set) {
	xmlNodePtr gnode;
	xmlChar * key;
	gnode = node->xmlChildrenNode;
	while (gnode) {
		key = xmlNodeGetContent(gnode);
		config_set_add(set, (char *) gnode->name, (char *) key);
		gnode = gnode->next;
		xmlFree(key);
	}
	_config_set_print(set);
}

int xml_config(char * xmlfile) {
	xmlParserCtxtPtr ctxt;
	xmlDocPtr doc;
	xmlNodePtr cur, l_cur;
	xmlKeepBlanksDefault(0);
	ctxt = xmlNewParserCtxt();
	doc = xmlCtxtReadFile(ctxt, xmlfile, "UTF-8",
			XML_PARSE_DTDATTR | XML_PARSE_NOERROR);
	if (!doc) {
		eoj_log("Can't parse the content: %s", xmlfile);
		return 1;
	}
	cur = xmlDocGetRootElement(doc);
	if (!cur || xmlStrcmp(cur->name, BAD_CAST "eoj")) {
		eoj_log("Can't get the root element: %s", xmlfile);
		xmlFreeDoc(doc);
		xmlFreeParserCtxt(ctxt);
		return 1;
	}

	int err = 0;
	l_cur = cur->xmlChildrenNode;
	while (l_cur) {
		xmlChar * key;
		struct config_set tmp_config;
		if (xmlStrcmp(l_cur->name, BAD_CAST "compiler") == 0) {
			xml_get_children(l_cur, &tmp_config);
			if (setup_compiler(&configs.compilers, &tmp_config)) {
				eoj_log("config compilers fail");
				err = 1;
				break;
			}
			config_set_clear(&tmp_config);
		} else if (xmlStrcmp(l_cur->name, BAD_CAST "database") == 0) {
			xml_get_children(l_cur, &tmp_config);
			if (setup_database(&configs.db_config, &tmp_config)) {
				eoj_log("config database fail");
				err = 1;
				break;
			}
			config_set_clear(&tmp_config);
		} else {
			key = xmlNodeGetContent(l_cur);
			config_set_add(&configs.global_config, (char *) l_cur->name,
					(char *) key);
			xmlFree(key);
		}
		l_cur = l_cur->next;
	}
	config_set_check_dir(&configs.global_config);
	xmlFreeDoc(doc);
	xmlFreeParserCtxt(ctxt);
	xmlCleanupParser();
	return err;
}
