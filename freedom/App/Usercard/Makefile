# ============================================
# $Id: Makefile,v 1.4 2004/03/01 09:06:57 eric Exp $
# ============================================

# get the new appname (basename in uppercase)
appname = $(shell basename `pwd` | tr [a-z] [A-Z])

export appname


include $(utildir)/PubRule


DISTFILES += Makefile  $(appname).app $(appname)_init.php.in init.freedom init.sql


$(pubdir)/$(appname)/init.freedom : init.freedom 
	cp init.freedom $(pubdir)/$(appname)

$(pubdir)/EXTERNALS :
	mkdir -p $@

$(pubdir)/EXTERNALS/usercard.php: $(pubdir)/EXTERNALS
	cp $(appname)_external.php $(pubdir)/EXTERNALS/usercard.php

publish: $(pubdir)/$(appname)/init.freedom $(pubdir)/EXTERNALS/usercard.php



