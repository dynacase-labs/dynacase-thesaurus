/*
 * Mike Glover
 * mpg4@duluoz.net
 *
 * pam_sql
 * db.h
 */
#ifndef INCLUDE_PAM_SQL_DB
#define INCLUDE_PAM_SQL_DB 1

#include <config.h>

#include "options.h"



/* DBMS-dependent information */
#if USE_MYSQL
#error mysql support is broken!
#include <mysql/mysql.h>
typedef MYSQL db_conn;
#elif USE_POSTGRES
#include <libpq-fe.h>
typedef PGconn db_conn;
typedef PGresult db_result;
#endif



/* function prototypes */
db_conn *      db_connect     (opt_t *opts);
inline void    db_close       (db_conn *conn);

db_result *    db_exec        (db_conn *conn, char *query);
inline int     db_numrows     (db_result *result);
inline void    db_free_result (db_result *result);


char* db_getvalue (db_result * res);
char* db_getNvalue (db_result * res, int n);

#endif /* INCLUDE_PAM_SQL_DB */
