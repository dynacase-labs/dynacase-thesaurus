# ============================================
# $Id: Makefile,v 1.1 2005/02/17 17:26:02 marc Exp $
# ============================================

include $(utildir)/PubRule
themes = $(patsubst %.thm,$(pubdir)/$(appname)/Themes/%.thm,$(wildcard *.thm))

$(pubdir)/$(appname)/Themes:
	mkdir -p $@

$(pubdir)/$(appname)/Themes/%.thm: %.thm $(pubdir)/$(appname)/Themes
	cp $< $@

publish: $(pubdir)/$(appname)/Themes $(themes)

