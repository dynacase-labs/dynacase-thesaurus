/*
 * Mike Glover
 * mpg4@duluoz.net
 *
 * options.h
 */
#ifndef INCLUDE_PAM_SQL_OPTIONS 
#define INCLUDE_PAM_SQL_OPTIONS


#define OPT_STRLEN 64


struct _opt_t {
  char host[OPT_STRLEN];
  char port[OPT_STRLEN];
  char dbname[OPT_STRLEN];
  char dbuser[OPT_STRLEN];
  char dbpass[OPT_STRLEN];
  char table[OPT_STRLEN];
  char usercol[OPT_STRLEN];
  char passcol[OPT_STRLEN];
  char expcol[OPT_STRLEN];
};
typedef struct _opt_t opt_t;


opt_t *options_parse (int argc, const char **argv);

#define options_delete(opts) if (opts) free (opts)

#endif /* INCLUDE_PAM_SQL_OPTIONS */
