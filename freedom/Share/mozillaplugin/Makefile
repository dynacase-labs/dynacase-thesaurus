# ============================================
# $Id: Makefile,v 1.4 2007/08/16 16:57:01 eric Exp $
# ============================================

appname=$(applib)

include $(utildir)/PubRule
DISTFILES += Makefile freedom.xml freedom-os.xml 

plugin=$(pubdir)/moz-searchplugin

$(plugin):
	[ ! -d $(plugin) ] && mkdir -p $(plugin)

$(plugin)/freedom.src : $(plugin) freedom.src
	cp freedom.src $(plugin)

$(plugin)/freedom.gif :  $(plugin) freedom.gif
	cp freedom.gif $(plugin)

$(plugin)/freedom.xml : $(plugin) freedom.xml
	cp freedom.xml $(plugin)

$(plugin)/freedom-os.xml : $(plugin) freedom-os.xml
	cp freedom-os.xml $(plugin)



$(pubdir)/freedomsearch.php :
	cp freedomsearch.php $(pubdir)

publish: $(plugin) $(plugin)/freedom.src $(plugin)/freedom.gif $(plugin)/freedom.xml $(plugin)/freedom-os.xml  $(pubdir)/freedomsearch.php 
