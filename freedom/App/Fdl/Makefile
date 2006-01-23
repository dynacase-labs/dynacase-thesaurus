# ============================================
# $Id: Makefile,v 1.8 2006/01/23 17:07:52 eric Exp $
# ============================================

# get the new appname (basename in uppercase)
appname = $(shell basename `pwd` | tr [a-z] [A-Z])

export appname


include $(utildir)/PubRule


DISTFILES += Makefile  $(appname).app $(appname)_init.php.in $(appname)_init.sh init.freedom freeaction.csv \
	 fdl.sql

$(pubdir)/$(appname)/$(appname)_init.sh : $(appname)_init.sh
	cp $< $@

$(pubdir)/$(appname)/init.freedom : init.freedom 
	cp init.freedom $(pubdir)/$(appname)

$(pubdir)/$(appname)/freeaction.csv : freeaction.csv 
	cp freeaction.csv $(pubdir)/$(appname)

$(pubdir)/EXTERNALS :
	mkdir -p $@

$(pubdir)/FDLGEN :
	mkdir -p $@

$(pubdir)/EXTERNALS/fdl.php: $(pubdir)/EXTERNALS $(pubdir)/FDLGEN
	cp $(appname)_external.php $(pubdir)/EXTERNALS/fdl.php


publish: $(pubdir)/$(appname)/$(appname)_init.sh $(pubdir)/$(appname)/init.freedom $(pubdir)/$(appname)/freeaction.csv $(pubdir)/EXTERNALS/fdl.php 


