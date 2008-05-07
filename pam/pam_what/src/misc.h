/*
 * Mike Glover
 * mpg4@duluoz.net
 *
 * pam_sql
 * pam_sql.h 
 */
#ifndef INCLUDE_PAM_SQL_MISC
#define INCLUDE_PAM_SQL_MISC 1


#include <security/pam_modules.h>


void           pam_sql_opt_free    (pam_handle_t *pamh,
				    void *data,
				    int error);

int            conversation        (pam_handle_t *pamh);


#define LDOMAIN 512
#define LUSER 256
typedef struct whatuser_t {
  char login[LUSER];
  char domain[LDOMAIN];
  char password[LUSER];
  int expires;
  char status;
} whatuser_t;
int what_getuser (pam_handle_t *pamh, int eflag,
		  int argc, const char **argv, whatuser_t* wu);
#endif /* INCLUDE_PAM_SQL_MISC */
