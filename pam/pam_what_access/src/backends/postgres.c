/*
 * Mike Glover
 * mpg4@duluoz.net
 *
 * pam_sql
 * backends/postgres.c
 */

#include <config.h>

#include <stdlib.h>
#include <stdio.h>
#include <string.h>
#include <syslog.h>
#include <pgsql/libpq-fe.h>

#include "db.h"
#include "pam_sql.h"


/*
 * db_connect
 * connect to the auth database, 
 * and return the connection handle
 */
db_conn *db_connect (opt_t *opts)
{
  db_conn *conn;
  char conn_str[BUFLEN];
  int len;

  len = 0;

  /* assemble the connect string */
  if ( strlen(opts->host) ) {
    len += snprintf (conn_str+len, BUFLEN-len-1, "host=%s ", opts->host);
  }
  if ( strlen(opts->port) ) {
    len += snprintf (conn_str+len, BUFLEN-len-1, "port=%s ", opts->port);
  }
  snprintf (conn_str+len, BUFLEN-len-1, "dbname=%s user=%s password=%s",
	   opts->dbname, opts->dbuser, opts->dbpass);
    

  /* connect to the database */
  syslog (LOG_DEBUG, "Connect string: %s", conn_str);
  conn = PQconnectdb (conn_str);
  if ( PQstatus(conn) != CONNECTION_OK ) {
    syslog (LOG_ERR, "Database connection failed: %s", PQerrorMessage(conn));
    return NULL;
  }


  /* done */
  return conn;
}



inline void db_close (db_conn *conn) 
{
  PQfinish (conn);
}



db_result *db_exec (db_conn *conn, char *query)
{
  db_result *result;

  syslog (LOG_DEBUG, "Executing query: %s", query);
  result = PQexec(conn, query);
  if ( result == NULL ) {
    syslog (LOG_CRIT, "Insufficient memory to allocate query result");
    return NULL;
  }

  if ( PQresultStatus(result) != PGRES_TUPLES_OK ) {
    PQclear (result);
    syslog (LOG_ERR, "Query failed :%s", PQerrorMessage(conn));
    return NULL;
  }

  /* success */
  return result;
}

char* db_getvalue (db_result * res)
{
  return(PQgetvalue(res, 0, 0));
}

inline void db_free_result (db_result *result) 
{
  PQclear (result);
}


inline int db_numrows (db_result *result) 
{
  return PQntuples(result);
}

