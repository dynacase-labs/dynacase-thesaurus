# ============================================
# $Id: Makefile,v 1.2 2008/03/28 09:55:38 eric Exp $
# $Log: Makefile,v $
# Revision 1.2  2008/03/28 09:55:38  eric
# rename RPM packages
#
# Revision 1.1  2004/11/22 17:10:46  eric
# first event
#
# Revision 1.1  2004/05/04 12:13:47  eric
# first
#
# Revision 1.1  2001/10/03 08:55:33  eric
# liste des utilisateurs nomade
#
# Revision 1.1  2001/06/13 14:41:44  eric
# contact address book
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