# ============================================
# $Id: Makefile,v 1.2 2005/03/08 22:40:03 marc Exp $
# ============================================

include $(utildir)/PubRule

themes = $(patsubst %.thm,$(pubdir)/$(appname)/Themes/%.thm,$(wildcard *.thm))

$(pubdir)/$(appname)/Themes:
	mkdir -p $@

$(pubdir)/$(appname)/Themes/%.thm: %.thm $(pubdir)/$(appname)/Themes
	cp $< $@

DISTFILES += $(wildcard *.thm)

publish: $(pubdir)/$(appname)/Themes $(themes)

