# ============================================
# $Id: Makefile,v 1.2 2008/03/28 10:35:25 eric Exp $
#
# ============================================

pages_not_php = stubThesaurusFamilies.php

include $(utildir)/PubRule


pages_fdl = $(patsubst %.php,$(pubdir)/$(applib)/%.php,$(wildcard Method*.php))


$(pubdir)/$(applib):
	mkdir $@

$(pubdir)/$(applib)/%.php: %.php $(pubdir)/$(applib)
	cd $(pubdir)/$(applib); \
	ln -sf ../$(appname)/$< .

publish: $(pubdir)/$(applib) $(pages_fdl) 