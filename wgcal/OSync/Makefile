# ============================================
# $Id: Makefile,v 1.5 2005/06/18 04:30:47 marc Exp $
# ============================================
appname=osync
include $(utildir)/PubRule
DISTFILES=  $(wildcard *.php) Makefile synchroniser.zip

$(pubdir)/$(appname)/synchroniser.zip : synchroniser.zip
	cp $< $@

publish: $(pubdir)/$(appname)/synchroniser.zip
