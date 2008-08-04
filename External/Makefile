# ============================================
# $Id: Makefile,v 1.1 2008/08/04 09:49:02 eric Exp $
#
# ============================================


include $(utildir)/PubRule

pages_fdl = $(patsubst %.php,$(pubdir)/$(applib)/%.php,$(wildcard *.php))

$(pubdir)/EXTERNALS :
	mkdir -p $@

$(pubdir)/$(applib)/%.php: %.php $(pubdir)/EXTERNALS
	cd $(pubdir)/$(style)/EXTERNALS; \
	ln -sf ../$(appname)/$< .

publish: $(pubdir)/EXTERNALS $(pages_fdl) 