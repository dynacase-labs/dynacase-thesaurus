#
# Makefile fragment for src
#

svdir      :=   $(dir)
dir        :=   $(dir)/src

dirs       +=   $(dir) 
srcs       +=   $(wildcard $(dir)/*.c)
inc        +=   -I$(dir)
libs       +=   -lpam -ldl
subdirs    :=   $(dir)/backends

ifneq ($(strip $(subdirs)),)
-include $(foreach sdir,$(subdirs),$(sdir)/dir.mk)
endif

dir        :=   $(svdir)