# ============================================
# $Id: Makefile,v 1.1 2002/03/01 09:36:42 eric Exp $
# ============================================


export doc = usercard
export pubdoc = $(pubdir)/USERCARD/doc


SUBDIR= images

include $(utildir)/doc.mk

$(pubdoc)/helpid.php: usercard.sgml
	$(utildir)/create_index.sh usercard > $@

publish: $(pubdoc)/helpid.php

DISTFILES= images Makefile $(wildcard *.sgml) 
