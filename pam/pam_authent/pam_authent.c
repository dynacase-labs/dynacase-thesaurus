/*
 * $Id: pam_authent.c,v 1.1 2002/03/19 16:00:47 marc Exp $
 * Anakeen 2002 - GPL 2
 * Marc Claverie <marc.claverie@anakeen.com>
 * 
 * This program was based on a Shane Watts contribution
 * 
 * You need to check pam_authent installation in /etc/pam.d
 *
 *
 * Usage : pam_authent user password
 *
 */
#include <security/pam_appl.h>
#include <security/pam_misc.h>
#include <stdio.h>
#include <syslog.h>

#define PAM_AUTHENT  "pam-authent"

const char *user="nobody";
const char *password="nobody";
static int pam_conv(int num_msg, const struct pam_message **msg,
                    struct pam_response **resp, void *appdata_ptr)
{
  int i = 0;
  struct pam_response *repl = NULL;

  repl = malloc(sizeof(struct pam_response) * num_msg);
  if (!repl) return PAM_CONV_ERR;

  for (i=0; i<num_msg; i++)
    switch (msg[i]->msg_style) {
    case PAM_PROMPT_ECHO_ON:
      repl[i].resp_retcode = PAM_SUCCESS;
      repl[i].resp = strdup(user);
      if (!repl[i].resp)
	{
	  perror("strdup");
	  exit(1);
	}
      break;
    case PAM_PROMPT_ECHO_OFF:
      repl[i].resp_retcode = PAM_SUCCESS;
      repl[i].resp = strdup(password);
      if (!repl[i].resp)
	{
	  perror("strdup");
	  exit(1);
	}
      break;
    case PAM_TEXT_INFO:
    case PAM_ERROR_MSG:
      write(2, msg[i]->msg, strlen(msg[i]->msg));
      write(2, "\n", 1);
      repl[i].resp_retcode = PAM_SUCCESS;
      repl[i].resp = NULL;
      break;
    default:
      free (repl);
      return PAM_CONV_ERR;
    }

  *resp=repl;
  return PAM_SUCCESS;
}
static struct pam_conv conv = {
  pam_conv,
  NULL
};

void _logmsg(char *msg, const char *puser)
{
#ifdef DEBUG
  fprintf(stderr, "pam_authent<debug> %s %s\n",msg,puser);
#endif
  syslog (LOG_AUTHPRIV|LOG_NOTICE, "%s %s", msg,puser);
}

int main(int argc, char *argv[])
{
  pam_handle_t *pamh=NULL;
  int retval;

  if (argc != 3) {
    _logmsg("Usage: pam_authent username password", "");
    exit(1);
  }

  if (argc == 3) {
    user = argv[1];
    password = argv[2];
  }

  retval = pam_start(PAM_AUTHENT, user, &conv, &pamh);
              
  if (retval == PAM_SUCCESS)
    retval = pam_authenticate(pamh, 0);    /* is user really user? */

  if (retval == PAM_SUCCESS)
    retval = pam_acct_mgmt(pamh, 0);       /* permitted access? */

  /* This is where we have been authorized or not. */

  if (retval == PAM_SUCCESS) {
    _logmsg("Authentication accepted for user ", user );
  } else {
    _logmsg("Authentication failed for user ", user);
  }

  if (pam_end(pamh,retval) != PAM_SUCCESS) {     /* close Linux-PAM */
    pamh = NULL;
    _logmsg("Failed to release authenticator", "");
    exit(1);
  }

  return ( retval == PAM_SUCCESS ? 0:1 );       /* indicate success */
}
