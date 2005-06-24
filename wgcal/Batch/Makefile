# ============================================
# $Id: Makefile,v 1.2 2005/06/24 14:44:35 marc Exp $
# ============================================
include $(utildir)/PubRule
#DISTFILES=  $(wildcard *.php) Makefile wgcal.cron
DISTFILES=  $(wildcard *.php) Makefile

/etc/cron.d/wgcal.cron : wgcal.cron
	cp $< $@
	chmod +x $@

#publish: /etc/cron.d/wgcal.cron $(publish)
publish: $(publish)
