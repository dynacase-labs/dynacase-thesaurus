# ============================================
# $Id: Makefile,v 1.3 2002/03/14 14:56:54 eric Exp $
# ============================================

# get the new appname (basename in uppercase)
appname = $(shell basename `pwd` | tr [a-z] [A-Z])

export appname


include $(utildir)/PubRule


DISTFILES = Makefile  $(appname).app $(appname)_init.php.in $(appname)_init.sh init.freedom 

$(pubdir)/$(appname)/$(appname)_init.sh : $(appname)_init.sh
	cp $< $@

$(pubdir)/$(appname)/init.freedom : init.freedom 
	cp init.freedom $(pubdir)/$(appname)

publish: $(pubdir)/$(appname)/$(appname)_init.sh $(pubdir)/$(appname)/init.freedom
