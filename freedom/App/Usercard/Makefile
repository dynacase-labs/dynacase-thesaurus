# ============================================
# $Id: Makefile,v 1.7 2006/01/24 15:25:39 eric Exp $
# ============================================

# get the new appname (basename in uppercase)
appname = $(shell basename `pwd` | tr [a-z] [A-Z])

rootprefix=$(RPM_BUILD_ROOT)
export appname


include $(utildir)/PubRule


DISTFILES += Makefile  $(appname).app $(appname)_init.php.in init.freedom init.sql userinit.csv personnldap.csv slapd_anakeen.conf


$(pubdir)/$(appname)/init.freedom : init.freedom 
	cp $< $@
$(pubdir)/$(appname)/%.csv : %.csv
	cp $< $@

$(rootprefix)/etc/openldap:
	mkdir -p $@

$(rootprefix)/etc/openldap/slapd_anakeen.conf:slapd_anakeen.conf $(rootprefix)/etc/openldap
	cp slapd_anakeen.conf $@

$(pubdir)/EXTERNALS :
	mkdir -p $@

$(pubdir)/EXTERNALS/usercard.php: $(pubdir)/EXTERNALS
	cp $(appname)_external.php $(pubdir)/EXTERNALS/usercard.php

publish: $(pubdir)/$(appname)/init.freedom $(pubdir)/EXTERNALS/usercard.php $(pubdir)/$(appname)/userinit.csv $(pubdir)/$(appname)/personnldap.csv $(rootprefix)/etc/openldap/slapd_anakeen.conf
