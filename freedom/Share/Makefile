# ============================================
# $Id: Makefile,v 1.9 2007/08/16 16:35:59 eric Exp $
# ============================================

SUBDIR= htmlarea fckeditor jscalendar mcal jsXMLParser tsearch2 mozillaplugin
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
