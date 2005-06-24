# ============================================
# $Id: Makefile,v 1.1 2005/06/24 14:40:49 marc Exp $
# ============================================
include $(utildir)/PubRule
DISTFILES=  $(wildcard *.php) Makefile wgcal.cron

/etc/cron.d/wgcal.cron : wgcal.cron
	cp $< $@
	chmod +x $@

publish: /etc/cron.d/wgcal.cron $(publish)
