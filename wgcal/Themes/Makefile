# ============================================
# $Id: Makefile,v 1.4 2005/06/02 05:03:44 marc Exp $
# ============================================

include $(utildir)/PubRule

themes = $(patsubst %.thm,$(pubdir)/$(appname)/Themes/%.thm,$(wildcard *.thm))
fontsz = $(patsubst %.fsz,$(pubdir)/$(appname)/Themes/%.fsz,$(wildcard *.fsz))

$(pubdir)/$(appname)/Themes:
	mkdir -p $@

$(pubdir)/$(appname)/Themes/%.fsz: %.fsz $(pubdir)/$(appname)/Themes
	cp $< $@

$(pubdir)/$(appname)/Themes/%.thm: %.thm $(pubdir)/$(appname)/Themes
	cp $< $@

DISTFILES += $(wildcard *.thm) $(wildcard *.fsz)

publish: $(pubdir)/$(appname)/Themes $(themes) $(fontsz)

