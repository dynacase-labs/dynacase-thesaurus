/*
 * PAM module for MySQL 
 *
 * Original Version written by: Gunay ARSLAN <arslan@gunes.medyatext.com.tr>
 * This version by: James O'Kane <jo2y@midnightlinux.com>
 * Modified to support PostgreSQL by: Mike Glover <mpg4@duluoz.net>
 *
 */

#include <stdlib.h>
#include <stdio.h>
#include <mysql/mysql.h>
#include <security/pam_modules.h>

#include "pam_sql.h"



int db_connect (MYSQL * auth_sql_server)
{
  int retvalue = PAM_AUTH_ERR;
  MYSQL *mysql_auth = NULL;
  
  D (("called."));
  mysql_init (auth_sql_server);
  mysql_auth = mysql_real_connect (auth_sql_server,
				   options.host,
				   options.dbuser,
				   options.dbpasswd,
				   options.database, 0, NULL, 0);
  
  if (mysql_auth != NULL) {
    if (!mysql_select_db (auth_sql_server, options.database)) {
      retvalue = PAM_SUCCESS;
    }
  }
  D (("returning."));
  return retvalue;
}

static int db_checkpasswd (MYSQL * auth_sql_server, const char *user, 
			   const char *passwd)
{
  char *sql, *dbpasswd, *cryptpasswd;
  MYSQL_RES *result;
  MYSQL_ROW sql_row;
  int retvalue = PAM_AUTH_ERR;
  
  D (("called."));
  
  sql = (char *) malloc (110);
  sprintf (sql, "select %s from %s where %s='%s'", options.passwdcolumn,
	   options.table, options.usercolumn, user);
  D ((sql));
  mysql_query (auth_sql_server, sql);
  free (sql);
  result = mysql_store_result (auth_sql_server);
  if (!result) {
    D ((mysql_error (auth_sql_server)));
    D (("returning."));
    return PAM_AUTH_ERR;
  }
  if (mysql_num_rows (result) >= 1) {
    sql_row = mysql_fetch_row (result);
    dbpasswd = (char *) malloc (strlen (sql_row[0]) * sizeof (char) + 1);
    
    /* is the + 1 above needed? */
    strcpy (dbpasswd, sql_row[0]);
    /* crypt user-supplied password via mysql */
    sql = (char *) malloc (110);
    sprintf (sql, "select PASSWORD('%s')", passwd);
    D ((sql));
    mysql_query (auth_sql_server, sql);
    free (sql);
    result = mysql_store_result (auth_sql_server);
    if (!result) {
      D ((mysql_error (auth_sql_server)));
      D (("returning."));
      return PAM_AUTH_ERR;
    }
    if (mysql_num_rows (result) >= 1) {
      sql_row = mysql_fetch_row (result);
      cryptpasswd = (char *) malloc (strlen (sql_row[0]) * sizeof (char) + 1);
      
      strcpy (cryptpasswd, sql_row[0]);
      if (!strcmp (dbpasswd, cryptpasswd)) {
	retvalue = PAM_SUCCESS;
      }
    }
  }
  free (sql);
  D (("returning."));
  return retvalue;
}
