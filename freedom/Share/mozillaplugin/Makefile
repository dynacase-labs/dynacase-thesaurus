# ============================================
# $Id: Makefile,v 1.2 2004/11/29 08:58:57 marc Exp $
# ============================================

appname=$(applib)

DISTFILES += Makefile

plugin=$(pubdir)/moz-searchplugin

$(plugin):
	[ ! -d $(plugin) ] && mkdir -p $(plugin)

$(plugin)/freedom.src : $(plugin) freedom.src
	cp freedom.src $(plugin)/moz-searchplugin

$(plugin)/moz-searchplugin/freedom.gif :  $(plugin) freedom.gif
	cp freedom.gif $(plugin)

$(pubdir)/freedomsearch.php :
	cp freedomsearch.php $(pubdir)

publish: $(plugin) $(plugin)/freedom.src $(plugin)/freedom.gif $(pubdir)/freedomsearch.php
