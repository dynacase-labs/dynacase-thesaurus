# ============================================
# $Id: Makefile,v 1.2 2008/05/29 05:54:07 marc Exp $
# ============================================

# get the new appname (basename in uppercase)
appname = $(shell basename `pwd` | tr [a-z] [A-Z])
export appname

include $(utildir)/PubRule

DISTFILES += Makefile  \
	freedom-xfam.sh  freedom-cleanattr.sh

$(pubdir)/$(appname) : 
	mkdir $(pubdir)/$(appname)

$(pubdir)/$(appname)/freedom-cleanattr.sh : freedom-cleanattr.sh
	/bin/cp $< $@
	chmod +x $(pubdir)/$(appname)/freedom-cleanattr.sh

$(pubdir)/$(appname)/freedom-xfam.sh : freedom-xfam.sh
	/bin/cp $< $@
	chmod +x $(pubdir)/$(appname)/freedom-xfam.sh

publish: $(pubdir)/$(appname)  \
	$(pubdir)/$(appname)/freedom-xfam.sh \
	$(pubdir)/$(appname)/freedom-cleanattr.sh
