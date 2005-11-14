# ============================================
# $Id: Makefile,v 1.6 2005/11/14 11:15:48 marc Exp $
# ============================================

SUBDIR= htmlarea jscalendar mcal jsXMLParser
appname=$(applib)

include $(utildir)/PubRule

DISTFILES += $(SUBDIR) Makefile php2V2


$(pubdir)/htmlarea :
	cp -r htmlarea $(pubdir)

$(pubdir)/jscalendar :
	cp -r jscalendar $(pubdir)

$(pubdir)/php2V2 :
	cp -f php2V2 $(pubdir)/API



publish:$(pubdir)/htmlarea $(pubdir)/jscalendar $(pubdir)/php2V2
