# ============================================
# $Id: Makefile,v 1.5 2005/09/09 06:56:13 eric Exp $
# ============================================

SUBDIR= htmlarea jscalendar
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
