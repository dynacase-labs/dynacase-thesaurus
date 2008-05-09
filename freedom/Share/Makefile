# ============================================
# $Id: Makefile,v 1.10 2008/05/09 10:00:58 jerome Exp $
# ============================================

SUBDIR= htmlarea fckeditor jscalendar mcal jsXMLParser tsearch2 mozillaplugin
appname=$(applib)

include $(utildir)/PubRule

DISTFILES += $(SUBDIR) Makefile php2V2


$(pubdir)/htmlarea :
	cp -r htmlarea $(pubdir)
	mkdir -p $(pubdir)/htmlarea/htmlarea
	ln -s ../images $(pubdir)/htmlarea/htmlarea/images
	ln -s ../popups $(pubdir)/htmlarea/htmlarea/popups

$(pubdir)/jscalendar :
	cp -r jscalendar $(pubdir)

$(pubdir)/php2V2 :
	cp -f php2V2 $(pubdir)/API



publish:$(pubdir)/htmlarea $(pubdir)/jscalendar $(pubdir)/php2V2
