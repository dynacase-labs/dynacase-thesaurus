# ============================================
# $Id: Makefile,v 1.1 2006/10/13 14:49:09 marc Exp $
# ============================================

include $(utildir)/PubRule

wsdl = $(patsubst %.wsdl,$(pubdir)/$(appname)/Wsdl/%.wsdl,$(wildcard *.wsdl))

$(pubdir)/$(appname)/Wsdl:
	mkdir -p $@

$(pubdir)/$(appname)/Wsdl/%.wsdl: %.wsdl $(pubdir)/$(appname)/Wsdl
	cp $< $@

DISTFILES += $(wildcard *.wsdl) 

publish: $(pubdir)/$(appname)/Wsdl $(wsdl)

