#
# Mike Glover
# mpg4@duluoz.net
#
# Makefile for pam_sql
#
#


#
# BEGIN setting variables
#


# local configuration options
include ./local.mk


# keep track of our current location
dir        :=   $(srcdir)


# these get built by the included dir.mk's
dirs       :=   .
srcs       := 
libs       :=
inc        :=
auto       :=   $(wildcard $(dir)/*.in)
objs        =   $(srcs:.c=.o)


# look for a dir.mk in these subdirectories
subdirs    :=   src


#
# END   setting variables
# BEGIN including subfiles
#

# include the fragment from each directory
-include $(foreach sdir,$(subdirs),$(dir)/$(sdir)/dir.mk)



#
# END including subfiles
# BEGIN rules
#

# clear out the suffix list and rewrite
.SUFFIXES:
.SUFFIXES: .c .o
.INTERMEDIATE: .d
.PHONY: all clean distclean dist install uninstall


# compile the program (and the objects)
all: $(prog)
$(prog): $(objs) 
	$(CC) -shared -Xlinker -x -o $(prog) $(objs) $(libs)


men        :=   $(wildcard $(srcdir)/man/*.?)
install: $(prog)
	$(NORMAL_INSTALL)
	$(INSTALL_PROGRAM) $(srcdir)/$(prog) $(DESTDIR)/$(bindir)/$(prog)
	$(foreach man,$(men),$(INSTALL_DATA) $(man) $(mandir)/$(notdir $(man)))

uninstall:
	$(NORMAL_UNINSTALL)
	rm -f $(bindir)/$(prog)
	rm -f $(foreach man,$(notdir $(men)), $(mandir)/$(man))


distdir    :=   $(package)-$(version)
distold: distclean
	mkdir ./$(distdir)
	cp -R $(srcdir)/* ./$(distdir)
	rm -rf ./$(distdir)/$(distdir)
	tar -zcvf ./$(distdir).tar.gz $(distdir)
	rm -rf ./$(distdir)
dist: distclean
	mkdir /tmp/$(distdir)
	cp -R $(srcdir)/* /tmp/$(distdir)
	rm -rf ./$(distdir)/$(distdir)
	cd /tmp ; \
	tar -zcvf ./$(distdir).tar.gz $(distdir)
	rm -rf /tmp/$(distdir)
	mv /tmp/$(distdir).tar.gz .


cfixes     :=   ~ .o 
clean: 
	rm -f $(foreach sfix,$(cfixes),$(addsuffix /*$(sfix),$(dirs)))
	rm -f $(prog) core


dcfixes    :=   .d  
distclean: clean
	rm -f $(foreach sfix,$(dcfixes),$(addsuffix /*$(sfix),$(dirs)))
	rm -f $(filter-out %/configure,$(auto:.in=))
	rm -f $(srcdir)/config.*
	rm -rf ./$(distdir) ./$(distdir).tar.gz


# include object dependencies
-include $(objs:.o=.d)


#
# END   rules
# BEGIN pattern rules
#


rpm : $(package).spec dist
	@if [ -f ~/.rpmmacros ]; then \
	  rpmdir=`cat ~/.rpmmacros | grep "%_topdir" | awk '{print $$2}'` ; \
	else \
	  echo "%_topdir $$HOME/rpmbuild" > ~/.rpmmacros ; \
	  rpmdir=$$HOME/rpmbuild ; \
	fi ; \
	[ ! -d $$rpmdir/SOURCES ] && mkdir $$rpmdir/SOURCES ; \
	[ ! -d $$rpmdir/SPECS ] && mkdir $$rpmdir/SPECS ; \
	echo "Copying $(distdir).tar.gz in $$rpmdir/SOURCES" ; \
	cp -f $(distdir).tar.gz $$rpmdir/SOURCES ; \
	echo "Copying $(package).spec in $$rpmdir/SPECS" ; \
	cp -f $(package).spec $$rpmdir/SPECS ; \
	cd $$rpmdir/SPECS ; \
	rpmbuild -ba $(package).spec
	@echo "RPM done."



# create dependencies automatically from .c files
%.d: %.c
	$(srcdir)/tools/depend.sh $(CPPFLAGS) $< > $@
