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


