# ============================================
# $Id: Makefile,v 1.1 2008/08/04 09:49:02 eric Exp $
#
# ============================================


include $(utildir)/PubRule

pages_fdl = $(patsubst %.php,$(pubdir)/$(applib)/%.php,$(wildcard Method*.php))
pages_fdl += $(patsubst %.php,$(pubdir)/$(applib)/%.php,$(wildcard Class*.php))

$(pubdir)/$(applib):
	mkdir $@

$(pubdir)/$(applib)/%.php: %.php $(pubdir)/$(applib)
	cd $(pubdir)/$(applib); \
	ln -sf ../$(appname)/$< .

publish: $(pubdir)/$(applib) $(pages_fdl) 