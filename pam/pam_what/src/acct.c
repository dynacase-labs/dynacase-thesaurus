/*
 * Mike Glover
 * mpg4@duluoz.net
 *
 * pam_sql
 * PAM module for authenticating to an SQL database
 *
 * Loosely based on pam_mysql by:
 *   Gunay ARSLAN <arslan@gunes.medyatext.com.tr>
 *   James O'Kane <jo2y@midnightlinux.com>
 */

#define PAM_SM_ACCOUNT

#include <config.h>

#include <stdio.h>
#include <stdlib.h>
#define __USE_XOPEN
#include <unistd.h>
#include <syslog.h>
#include <string.h>
#include <security/pam_modules.h>

#include "db.h"
#include "misc.h"
#include "options.h"
#include "pam_sql.h"



PAM_EXTERN int pam_sm_acct_mgmt (pam_handle_t *pamh, int flags,
				 int argc, const char **argv)
{
  opt_t *opts;
  db_conn *conn;
  db_result *result;
  const char *userdomain;
  char user[256],optdomain[50],*stok,domain[512], userdomaintmp[256+512+1];
  char query[BUFLEN];
  int retval;


  
  /* load the options */
  /*
   * retval = pam_get_data (pamh, _PAM_SQL_OPT_HANDLE, (const void **) &opts);
   * if ( ( retval != PAM_SUCCESS ) || ( ! opts ) ) {
   */
    opts = options_parse(argc, argv);
    if ( ! opts ) {
      syslog (LOG_ALERT, "Parse error. Exiting");
      return PAM_AUTHINFO_UNAVAIL;
    }
    pam_set_data (pamh, _PAM_SQL_OPT_HANDLE, (void *) opts, pam_sql_opt_free);
    /*}*/


  /* connect to the database */
  /* db_connect logs it's own errors */
  conn = db_connect (opts);
  if ( ! conn ) {
    return PAM_AUTHINFO_UNAVAIL;
  }

  /* get the username */
  retval = pam_get_user (pamh, &userdomain, NULL);
  if ( ( retval != PAM_SUCCESS ) || ( ! userdomain ) ) {
    syslog (LOG_NOTICE, "Failed fetching username");
    db_close(conn);
    return retval;
  } 


  /* user can be composed : user@zou.com or user_zou.com */
  strcpy(userdomaintmp, userdomain);
  stok=strtok(userdomaintmp,"@_");
  if (stok) strcpy(user,stok);
  stok=strtok(NULL,"@_");
  if (stok) {
    strcpy(domain,stok);
    /* set up the query string */
    snprintf (query, BUFLEN-1, 
	      "select iddomain from domain where name='%s' ", 
	      domain);

    result = db_exec (conn, query);
    if ( ! result ) {
      syslog (LOG_ERR, "Query failed %s",query);
      db_close(conn);
      return PAM_AUTH_ERR;
    }
  
    if ( db_numrows(result) != 1 ) {
      syslog (LOG_WARNING, "Authentication failed for user %s [%s]", user,query);
      db_close(conn);
      return PAM_AUTH_ERR;
    }
    
    sprintf(optdomain, "AND iddomain = '%s'", db_getvalue(result));
  } else {
    strcpy(optdomain,"");
  }

  /* talk to the user on the other end */
  /* FIXME: error check? */
  conversation(pamh);
  

  

  
  /* set up the query string */
  snprintf (query, BUFLEN-1, 
	    "select %s from %s where %s='%s' %s", 
	    opts->passcol, opts->table, opts->usercol, user, optdomain);

    
  

  result = db_exec (conn, query);
  if ( ! result ) {
    syslog (LOG_ERR, "Query failed");
    db_close(conn);
    return PAM_AUTH_ERR;
  }

  
 

  /* should be exactly one row */
  if ( db_numrows(result) != 1 ) {
    syslog (LOG_NOTICE, "Account expired for user %s [%s]", user, query);
    db_close(conn);
    return PAM_AUTH_ERR;
  }

  

  db_close(conn);
  return PAM_SUCCESS;
}

