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

#include "misc.h"
#include "pam_sql.h"
#include "options.h"
#include "db.h"


#define LDOMAIN 512
#define LUSER 256

extern char DEBUG; /* global to log debug */

/* 
 * pam_sm_authenticate
 * get the username and password from the application
 * and check against the database
 */
PAM_EXTERN int pam_sm_authenticate (pam_handle_t * pamh, int flags,
				    int argc, const char **argv)
{
  int retval, i;
  const char   *passwd;
   whatuser_t wu;
  char passwdk[50];
  char salt[3]; // to crypt passwd

  passwd = NULL; /* ?? */

#ifdef HAVE_PAM_FAIL_DELAY
  pam_fail_delay (pamh, 3000000);
#endif


  retval = what_getuser(pamh,flags,argc,argv,&wu);
  
  if ( retval != PAM_SUCCESS ) return retval;
  

  /* get the password */
  retval = pam_get_item (pamh, PAM_AUTHTOK, (const void **) &passwd);
  if ( retval != PAM_SUCCESS ) {
    syslog (LOG_NOTICE, "No authtoken provided: %s", 
	    pam_strerror(pamh, retval));
    
    return retval;
  }
  
  
 

  /* compare cryted passwords */
  strcpy(passwdk, wu.password);
  salt[0]=passwdk[0];
  salt[1]=passwdk[1];
  salt[2]='\0';

  if (strcmp(crypt(passwd, salt) , passwdk)) {
    if (DEBUG) syslog (LOG_DEBUG, "Authentication failed for user %s", wu.login);
   
    return PAM_AUTH_ERR;
  }
  if (DEBUG) syslog (LOG_DEBUG, "Success authentication for user %s", wu.login);
  
  return PAM_SUCCESS;  
}



/* just a dummy function */
PAM_EXTERN int pam_sm_setcred (pam_handle_t *pamh, int flags, 
			       int argc, const char **argv)
{
  return PAM_SUCCESS;
}

