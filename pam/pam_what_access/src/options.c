/*
 * Mike Glover
 * mpg4@duluoz.net
 *
 * options.c 
 * read in the command-line options 
 * and set up the global opt structure
 */

#include <config.h>

#include <stdlib.h>
#include <string.h>

#include <security/pam_modules.h>

#include "options.h"


#define OPT_SEPCHAR '='

#define min(x,y) ((x) < (y)) ? (x) : (y)


char DEBUG=0; /* to say if debug mode required */

/*
 * options_parse
 * parse the config line,
 * and set up the opt_t structure
 */
opt_t *options_parse (int argc, const char **argv) 
{
  opt_t *opts;
  char optname[OPT_STRLEN], optval[OPT_STRLEN];
  char *chptr, *element;
  int i, len;



  
  opts = (opt_t *) malloc (sizeof(opt_t));
  if ( ! opts ) {
    syslog (LOG_CRIT, "Insufficient memory for opt_t");
    return NULL;
  }


  /* set some reasonable defaults */
  opts->host[0]   = '\0';
  opts->port[0]   = '\0';
  opts->dbname[0] = '\0';
  opts->dbuser[0] = '\0';
  opts->dbpass[0] = '\0';
  opts->application[0] = '\0';
  opts->access[0] = '\0';
  opts->user[0] = '\0';


  for (i=0; i<argc; i++) {
    
    /* break up the arg into name/value pairs */
    chptr = strchr (argv[i], OPT_SEPCHAR);
    len = min (OPT_STRLEN-1,chptr-argv[i]);
    strncpy (optname, argv[i], len);
    optname[len] = '\0';
    if ( *(++chptr) ) {
      strncpy (optval, chptr, OPT_STRLEN);
    }
    else {
      optval[0] = '\0';
    }

    
    /* what option is this? */
    if ( ! strcmp ("host", optname) ) {
      element = opts->host;
    }
    else if ( ! strcmp("port", optname) ) {
      element = opts->port;
    } 
    else if ( ! strcmp("dbname", optname) ) {
      element = opts->dbname;
    }
    else if ( ! strcmp("dbuser", optname) ) {
      element = opts->dbuser;
    }
    else if ( ! strcmp("dbpass", optname) ) {
      element = opts->dbpass;
    }
    else if ( ! strcmp("application", optname) ) {
      element = opts->application;
    }
    else if ( ! strcmp("access", optname) ) {
      element = opts->access;
    }
    else if ( ! strcmp("debug", optname) ) {
      element = opts->debug;
      DEBUG=atoi(optval);
    }
    else {
      syslog (LOG_ERR, "Unknown option type '%s'", optname);
      return NULL;
    }
    /* copy the option value into the struct */
    strncpy (element, optval, OPT_STRLEN-1);
    
  } /* for */

  /* check mandatory fields */
  if ( ( ! opts->application[0] ) || ( ! opts->access[0] )  ) {
    syslog (LOG_ERR, "Missing required field(s) in config");
    free (opts);
    return NULL;
  }

  return opts;
}

