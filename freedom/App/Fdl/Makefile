# ============================================
# $Id: Makefile,v 1.2 2002/03/08 14:47:28 eric Exp $
# ============================================

# get the new appname (basename in uppercase)
appname = $(shell basename `pwd` | tr [a-z] [A-Z])

export appname


include $(utildir)/PubRule


DISTFILES = Makefile  $(appname).app $(appname)_init.php.in $(appname)_init.sh

$(pubdir)/$(appname)/$(appname)_init.sh : $(appname)_init.sh
	cp $< $@

publish: $(pubdir)/$(appname)/$(appname)_init.sh
