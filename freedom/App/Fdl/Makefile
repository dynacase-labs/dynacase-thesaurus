# ============================================
# $Id: Makefile,v 1.5 2002/04/23 09:58:35 eric Exp $
# ============================================

# get the new appname (basename in uppercase)
appname = $(shell basename `pwd` | tr [a-z] [A-Z])

export appname


include $(utildir)/PubRule


DISTFILES = Makefile  $(appname).app $(appname)_init.php.in $(appname)_init.sh init.freedom \
	$(appname)_external.php fdl.sql

$(pubdir)/$(appname)/$(appname)_init.sh : $(appname)_init.sh
	cp $< $@

$(pubdir)/$(appname)/init.freedom : init.freedom 
	cp init.freedom $(pubdir)/$(appname)

$(pubdir)/EXTERNALS :
	mkdir -p $@

$(pubdir)/EXTERNALS/fdl.php: $(pubdir)/EXTERNALS
	cp $(appname)_external.php $(pubdir)/EXTERNALS/fdl.php

publish: $(pubdir)/$(appname)/$(appname)_init.sh $(pubdir)/$(appname)/init.freedom $(pubdir)/EXTERNALS/fdl.php 
