# ============================================
# $Id: Makefile,v 1.3 2005/06/02 04:13:32 marc Exp $
# ============================================

include $(utildir)/PubRule

themes = $(patsubst %.thm,$(pubdir)/$(appname)/Themes/%.thm,$(wildcard *.thm))
fontsz = $(patsubst %.fsz,$(pubdir)/$(appname)/Themes/%.fsz,$(wildcard *.fsz))

$(pubdir)/$(appname)/Themes:
	mkdir -p $@

$(pubdir)/$(appname)/Themes/%.thm: %.thm $(pubdir)/$(appname)/Themes
	cp $< $@

DISTFILES += $(wildcard *.thm) $(wildcard *.fsz)

publish: $(pubdir)/$(appname)/Themes $(themes)

