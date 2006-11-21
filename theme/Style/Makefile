# ============================================
# $Id: Makefile,v 1.2 2006/11/21 18:01:05 eric Exp $
# ============================================


export targetdir
export utildir

#SUBDIR= $(wildcard [^C^V^S]*)
SUBDIR= `find .  -mindepth 1 -maxdepth 1 -type d -not -name CVS  -printf "%f "` # ?

include $(utildir)/PubRule


DISTFILES += $(SUBDIR) Makefile
