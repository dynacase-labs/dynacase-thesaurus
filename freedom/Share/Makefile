# ============================================
# $Id: Makefile,v 1.4 2005/07/21 15:52:46 eric Exp $
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
	cp -f php2V2 $(pubdir)/Api



publish:$(pubdir)/htmlarea $(pubdir)/jscalendar $(pubdir)/php2V2
