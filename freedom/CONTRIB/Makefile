# ============================================
# $Id: Makefile,v 1.1 2008/05/06 13:11:06 marc Exp $
# ============================================

# get the new appname (basename in uppercase)
appname = $(shell basename `pwd` | tr [a-z] [A-Z])
export appname

include $(utildir)/PubRule

DISTFILES += Makefile  \
	freedom-xfam.sh

$(pubdir)/$(appname) : 
	mkdir $(pubdir)/$(appname)

$(pubdir)/$(appname)/freedom-xfam.sh : freedom-xfam.sh
	/bin/cp $< $@
	chmod +x $(pubdir)/$(appname)/freedom-xfam.sh

publish: $(pubdir)/$(appname)  \
	$(pubdir)/$(appname)/freedom-xfam.sh
