# ============================================
# $Id: Makefile,v 1.1 2002/02/13 14:31:58 eric Exp $
# ============================================

# get the new appname (basename in uppercase)
appname = $(shell basename `pwd` | tr [a-z] [A-Z])

export appname


include $(utildir)/PubRule


DISTFILES = Makefile  $(appname).app $(appname)_init.php.in init.freedom 


$(pubdir)/$(appname)/init.freedom : init.freedom 
	cp init.freedom $(pubdir)/$(appname)

publish: $(pubdir)/$(appname)/init.freedom
