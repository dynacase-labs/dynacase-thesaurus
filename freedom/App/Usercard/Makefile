# ============================================
# $Id: Makefile,v 1.3 2002/09/02 16:40:00 eric Exp $
# ============================================

# get the new appname (basename in uppercase)
appname = $(shell basename `pwd` | tr [a-z] [A-Z])

export appname


include $(utildir)/PubRule


DISTFILES += Makefile  $(appname).app $(appname)_init.php.in init.freedom 


$(pubdir)/$(appname)/init.freedom : init.freedom 
	cp init.freedom $(pubdir)/$(appname)

$(pubdir)/EXTERNALS :
	mkdir -p $@

$(pubdir)/EXTERNALS/usercard.php: $(pubdir)/EXTERNALS
	cp $(appname)_external.php $(pubdir)/EXTERNALS/usercard.php

publish: $(pubdir)/$(appname)/init.freedom $(pubdir)/EXTERNALS/usercard.php



