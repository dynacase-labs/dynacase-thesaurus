# ============================================
# $Id: Makefile,v 1.7 2006/12/22 14:38:45 eric Exp $
# ============================================

SUBDIR= htmlarea fckeditor jscalendar mcal jsXMLParser
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
