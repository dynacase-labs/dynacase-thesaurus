# ============================================
# $Id: Makefile,v 1.5 2004/08/09 08:07:06 eric Exp $
# ============================================

# get the new appname (basename in uppercase)
appname = $(shell basename `pwd` | tr [a-z] [A-Z])

export appname


include $(utildir)/PubRule


DISTFILES += Makefile  $(appname).app $(appname)_init.php.in init.freedom init.sql userinit.csv


$(pubdir)/$(appname)/init.freedom : init.freedom 
	cp $< $@
$(pubdir)/$(appname)/userinit.csv : userinit.csv
	cp $< $@

$(pubdir)/EXTERNALS :
	mkdir -p $@

$(pubdir)/EXTERNALS/usercard.php: $(pubdir)/EXTERNALS
	cp $(appname)_external.php $(pubdir)/EXTERNALS/usercard.php

publish: $(pubdir)/$(appname)/init.freedom $(pubdir)/EXTERNALS/usercard.php $(pubdir)/$(appname)/userinit.csv



