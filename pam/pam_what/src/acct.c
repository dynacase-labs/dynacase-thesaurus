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
#include <time.h>
#include <security/pam_modules.h>

#include "db.h"
#include "misc.h"
#include "options.h"
#include "pam_sql.h"



PAM_EXTERN int pam_sm_acct_mgmt (pam_handle_t *pamh, int flags,
				 int argc, const char **argv)
{
  int retval=PAM_AUTH_ERR;
  whatuser_t wu;

  retval = what_getuser(pamh,flags,argc,argv,&wu);

  if ( retval != PAM_SUCCESS ) return retval;

  /* see if password has expired */
  if ((wu.expires > 0) && (wu.expires < time(NULL))) return PAM_ACCT_EXPIRED;

  return retval;
}

