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
static int     converse            (pam_handle_t *pamh, 
				    struct pam_message **message,
				    struct pam_response **response);
static char *  _pam_delete         (register char *xx);
int            conversation        (pam_handle_t *pamh);


#define LDOMAIN 512
#define LUSER 256
typedef struct whatuser_t {
  char login[LUSER];
  char domain[LDOMAIN];
  char password[LUSER];
  int expires;
} whatuser_t;

#endif /* INCLUDE_PAM_SQL_MISC */
