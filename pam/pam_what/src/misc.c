/*
 * Mike Glover
 * mpg4@duluoz.net
 *
 * misc.c
 * PAM module for authenticating to an SQL database
 *
 * Loosely based on pam_mysql by:
 *   Gunay ARSLAN <arslan@gunes.medyatext.com.tr>
 *   James O'Kane <jo2y@midnightlinux.com>
 *
 * The conv(), _pam_delete, and conversation() functions are:
 *   Copyright (c) Cristian Gafton <gafton@redhat.com>, 1999
 */


#define _BSD_SOURCE

#include <config.h>

#include <stdlib.h>
#include <string.h>
#include <syslog.h>

#include <security/pam_modules.h>
#include <security/_pam_macros.h>

#include "pam_sql.h"
#include "options.h"
#include "db.h"
#include "misc.h"

extern char DEBUG; /* global to log debug */

void pam_sql_opt_free (pam_handle_t *pamh, void *data, int error)
{
  free (data);
}


/*
 * dummy conversation function sending exactly one prompt
 * and expecting exactly one response from the other party
 */
static int converse(pam_handle_t *pamh,
		    struct pam_message **message,
		    struct pam_response **response)
{
    int retval;
    const struct pam_conv *conv;

    retval = pam_get_item(pamh, PAM_CONV, (const void **) &conv ) ;
    if (retval == PAM_SUCCESS)
	retval = conv->conv(1, (const struct pam_message **)message,
			    response, conv->appdata_ptr);
	
    return retval; /* propagate error status */
}



static char *_pam_delete (register char *xx)
{
    _pam_overwrite(xx);
    _pam_drop(xx);
    return NULL;
}


/*
 * This is a conversation function to obtain the user's password
 */
int conversation(pam_handle_t *pamh)
{
    struct pam_message msg[2],*pmsg[2];
    struct pam_response *resp;
    int retval;
    char * token = NULL;    
    
    pmsg[0] = &msg[0];
    msg[0].msg_style = PAM_PROMPT_ECHO_OFF;
    msg[0].msg = "Password: ";

    /* so call the conversation expecting i responses */
    resp = NULL;
    retval = converse(pamh, pmsg, &resp);

    if (resp != NULL) {
	const char * item;
	/* interpret the response */
	if (retval == PAM_SUCCESS) {     /* a good conversation */
	    token = x_strdup(resp[0].resp);
	    if (token == NULL) {
		return PAM_AUTHTOK_RECOVER_ERR;
	    }
	}

	/* set the auth token */
	retval = pam_set_item(pamh, PAM_AUTHTOK, token);
	token = _pam_delete(token);   /* clean it up */
	if ( (retval != PAM_SUCCESS) ||
	     (retval = pam_get_item(pamh, PAM_AUTHTOK, (const void **)&item))
	     != PAM_SUCCESS ) {
	    return retval;
	}
	
	_pam_drop_reply(resp, 1);
    } else {
	retval = (retval == PAM_SUCCESS)
	    ? PAM_AUTHTOK_RECOVER_ERR:retval ;
    }

    return retval;
}

/**
 * @param eflag if 1 =>with expire field
 *               if 0 =>no expire field
 */
PAM_EXTERN int what_getuser (pam_handle_t *pamh, int eflag,
			     int argc, const char **argv, whatuser_t* wu) { 

  opt_t *opts;
  db_conn *conn;
  db_result *result;
  const char *userdomain;
  char user[LUSER],optdomain[50+LDOMAIN],*stok,domain[LDOMAIN], userdomaintmp[LUSER+LDOMAIN+1];
  char escaped_user[LUSER*2+1],escaped_domain[LDOMAIN*2+1];
  size_t len;
  char query[BUFLEN];
  int retval;
  static whatuser_t lwu;
  char status[16]; /* the status of user */
  int i,j;
  
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

  /* add slashes */
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
    len = PQescapeString(escaped_domain, domain, strlen(domain));
    if( len < strlen(domain) ) {
      syslog(LOG_NOTICE, "error escaping domain string (%d < %d)", len, strlen(domain));
      db_close(conn);
      return PAM_AUTH_ERR;
    }
    /* set up the query string */
    snprintf (query, BUFLEN-1, 
	      "select iddomain from domain where name='%s' ", 
	      escaped_domain);

    result = db_exec (conn, query);
    if ( ! result ) {
      syslog (LOG_ERR, "Query failed %s",query);
      db_close(conn);
      return PAM_SERVICE_ERR;
    }
  
    if ( db_numrows(result) != 1 ) {
      if (DEBUG) syslog (LOG_DEBUG, "Authentication failed for user %s [%s]", user,query);
      db_free_result(result);
      db_close(conn);
      return PAM_USER_UNKNOWN;
    }
    
    sprintf(optdomain, "AND iddomain = '%s'", db_getvalue(result));
    db_free_result(result);
  } else {
    strcpy(optdomain,"");
  }

  /* talk to the user on the other end */
  /* FIXME: error check? */
  conversation(pamh);

  
  /* set up the query string */

  len = PQescapeString(escaped_user, user, strlen(user));
  if( len < strlen(user) ) {
    syslog(LOG_NOTICE, "error escaping user string (%d < %d)", len, strlen(user));
    db_close(conn);
    return PAM_AUTH_ERR;
  }
  if (eflag) {
    snprintf (query, BUFLEN-1, 
	      "select %s, %s, status from %s where %s='%s' %s order by iddomain limit 1", 
	      opts->passcol, opts->expcol, opts->table, opts->usercol, escaped_user, optdomain);
  } else {
    snprintf (query, BUFLEN-1, 
	      "select %s  from %s where %s='%s' %s order by iddomain limit 1", 
	      opts->passcol,  opts->table, opts->usercol, escaped_user, optdomain);
  }
    
  

  result = db_exec (conn, query);

  if ( ! result ) {
    syslog (LOG_ERR, "Query failed");
    db_free_result(result);
    db_close(conn);
    return PAM_SERVICE_ERR;
  }


  /* could be more than one row but get only the first*/
  if ( db_numrows(result) != 1 ) {
    db_free_result(result);
    db_close(conn);
    return PAM_USER_UNKNOWN;
  }
  

  if (eflag) {
    /* verify that the status is not 'D' */
    strcpy(status,db_getNvalue(result,2));
    lwu.status=status[0];    
    lwu.expires=atoi(db_getNvalue(result,1)) ;
  }

  strcpy(lwu.login, user);
  strcpy(lwu.domain, domain );
  strcpy(lwu.password, db_getNvalue(result,0) );
    

  db_free_result(result);
  db_close(conn);

  strcpy(wu->login, lwu.login);
  strcpy(wu->domain, lwu.domain);
  strcpy(wu->password, lwu.password);
  wu->expires=lwu.expires;
  wu->status=lwu.status;
  
  return PAM_SUCCESS;
}
