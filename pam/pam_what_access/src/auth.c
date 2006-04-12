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

#define PAM_SM_AUTH

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

#define LDOMAIN 512
#define LUSER 256

/* 
 * pam_sm_authenticate
 * get the username and password from the application
 * and check against the database
 */

extern char DEBUG; /* global to log debug */

db_conn *conn;

int GetGroup(int iduser,
	     int *groups,
	     int *nb)
{

  char query[BUFLEN];
  db_result *result=NULL;
  int i;
  /* set up the query string */
  *nb=0;
  snprintf (query, BUFLEN-1, "select idgroup from groups where iduser= %d",
	    iduser);

  result = db_exec (conn, query);
  if ( ! result ) {
    if (DEBUG) syslog (LOG_DEBUG, "Query failed [%s]", query);

    return 0;
  }
 /* */
  *nb=PQntuples(result);
  for (i = 0; i < PQntuples(result); i++)
    {        
      groups[i] = atoi(PQgetvalue(result, i, 0));
      
    }

    db_free_result(result);
    return (*nb);

  
}



int HasPrivilege(int iduser,
		 int idacl) 
{
    
  char query[BUFLEN];
  db_result *result=NULL;
  /* set up the query string */
  int groups[100];
  int i,nbgroup;



  /*---------- search privilege in user  ----------*/
  snprintf (query, BUFLEN-1, 
	    "select id_acl from permission where (id_user=%d) and (id_acl=%d)",
	    iduser,
	    idacl);

  result = db_exec (conn, query);
  if ( ! result ) {
    if (DEBUG) syslog (LOG_DEBUG, "Query failed [%s]", query);

    return 0;
  }

  if ( db_numrows(result) == 1 ) {
    if (DEBUG) syslog (LOG_DEBUG, "Access granted for user %d [%s]", iduser,query);
    db_free_result(result);

    return 1;
  }
  db_free_result(result);



  /*---------- search UNprivilege in user  ----------*/
  snprintf (query, BUFLEN-1, 
	    "select id_acl from permission where (id_user=%d) and (id_acl=%d)",
	    iduser,
	    -idacl);

  result = db_exec (conn, query);
  if ( ! result ) {
    if (DEBUG) syslog (LOG_DEBUG, "Query failed [%s]", query);

    return 0;
  }

  if ( db_numrows(result) > 0 ) { // The user has explicitly a unprivilege
    if (DEBUG) syslog (LOG_DEBUG, "Access failed for user %d [%s]", iduser,query);
    db_free_result(result);

    return 0;
  }
  db_free_result(result);
  

  /*---------- search privilege in user group ----------*/
  GetGroup(iduser, groups, &nbgroup);
  
  for (i = 0; i < nbgroup; i++) {

    if (HasPrivilege (groups[i], idacl)) return 1;
    
  }

  /*---------- end group searches ----------*/




  return 0;
  

 
}



PAM_EXTERN int pam_sm_authenticate (pam_handle_t * pamh, int flags,
				    int argc, const char **argv)
{
  int retval;
  const char  *userdomain;
  char user[LUSER],optdomain[50+LDOMAIN],*stok,domain[LDOMAIN], userdomaintmp[LUSER+LDOMAIN+1];
  char query[BUFLEN];
  db_result *result=NULL;
  opt_t *opts=NULL;
  int userid=0;
  int aclid=0;



#ifdef HAVE_PAM_FAIL_DELAY
  pam_fail_delay (pamh, 2500);
#endif

  /* load options */
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
 /*   } */


  /* get the username */
  retval = pam_get_user (pamh, &userdomain, NULL);
  if ( ( retval != PAM_SUCCESS ) || ( ! userdomain ) ) {
    syslog (LOG_NOTICE, "Failed fetching username");
    return retval;
  } 


  // test limits
  if (strlen(userdomain) > (LDOMAIN+LUSER)) {
    syslog (LOG_NOTICE, "user login name and domain too long");
    return PAM_AUTH_ERR;
  }


  /* connect to the database */
  /* db_connect logs it's own errors */
  conn = db_connect (opts);
  if ( ! conn ) {
    return PAM_AUTHINFO_UNAVAIL;
  }

  /* user can be composed : user@zou.com  */
  PQescapeString(userdomaintmp, userdomain, strlen(userdomain));

  stok=strtok(userdomaintmp,"@");  

  if (stok) {
    if (strlen(stok) >= (LUSER)) {
      syslog (LOG_NOTICE, "user login name too long");
      db_close(conn);
      return PAM_AUTH_ERR;
    }
    strcpy(user,stok);
  }

  stok=strtok(NULL,"@");
  if (stok) {
    if (strlen(stok) >= (LDOMAIN)) {
      syslog (LOG_NOTICE, "user domain name too long");
      db_close(conn);
      return PAM_AUTH_ERR;
    }
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
      if (DEBUG) syslog (LOG_DEBUG, "Authentication failed for user %s [%s]", user,query);
      db_free_result(result);
      db_close(conn);
      return PAM_AUTH_ERR;
    }
    
    sprintf(optdomain, "AND iddomain = '%s'", db_getvalue(result));
    db_free_result(result);
  } else {
    strcpy(optdomain,"");
  }

  /* talk to the user on the other end */
  /* FIXME: error check? */
  conversation(pamh);
  
  
  

  /*------------------------------ SEARCH USER ID ------------------------------ */
  
  /* set up the query string */
  snprintf (query, BUFLEN-1, 
	    "select id from users where login='%s' %s order by iddomain limit 1", 
	       user, optdomain);

    
  

  result = db_exec (conn, query);
  if ( ! result ) {
    if (DEBUG) syslog (LOG_DEBUG, "Query failed [%s]", query);
    db_close(conn);
    return PAM_AUTH_ERR;
  }

  
  if ( db_numrows(result) != 1 ) {
    if (DEBUG) syslog (LOG_DEBUG, "Authentication failed for user %s [%s]", user,query);
    db_free_result(result);
    db_close(conn);
    return PAM_AUTH_ERR;
  }
 

  /* compare cryted passwords */
  userid = atoi( db_getvalue(result));
  db_free_result(result);

  /*------------------------------ SEARCH ACL ID ------------------------------ */
  /* set up the query string */
  snprintf (query, BUFLEN-1, 
	    "select t0.\"id\", t1.\"name\" from \"acl\" t0,\"application\" t1  where  (t1.\"id\"=t0.\"id_application\") and (t1.\"name\" = '%s') and (t0.\"name\" = '%s')",
	    opts->application,
	    opts->access );

    
  

  result = db_exec (conn, query);
  if ( ! result ) {
    if (DEBUG) syslog (LOG_DEBUG, "Query failed [%s]", query);
    db_close(conn);
    return PAM_AUTH_ERR;
  }

  
  if ( db_numrows(result) != 1 ) {
    if (DEBUG) syslog (LOG_DEBUG, "Unknow ACL [%s]", query);
    db_free_result(result);
    db_close(conn);
    return PAM_AUTH_ERR;
  }
 

  /*  */
  aclid = atoi( db_getvalue(result));
  db_free_result(result);

  /*---------------------------- COMPUTE PRIVILEGE ---------------------------- */
  /* set up the query string */
  if ((!userid) ||  
      (! HasPrivilege(userid, aclid))) {
    db_close(conn);
    return PAM_AUTH_ERR;
    
  }

  db_close(conn);
  return PAM_SUCCESS;  
}



/* just a dummy function */
PAM_EXTERN int pam_sm_setcred (pam_handle_t *pamh, int flags, 
			       int argc, const char **argv)
{
  return PAM_SUCCESS;
}

