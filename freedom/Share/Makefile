# ============================================
# $Id: Makefile,v 1.3 2004/08/27 14:12:02 eric Exp $
# ============================================

SUBDIR= htmlarea jscalendar
appname=$(applib)

include $(utildir)/PubRule

DISTFILES += $(SUBDIR) Makefile


$(pubdir)/htmlarea :
	cp -r htmlarea $(pubdir)

$(pubdir)/jscalendar :
	cp -r jscalendar $(pubdir)



publish:$(pubdir)/htmlarea $(pubdir)/jscalendar 
