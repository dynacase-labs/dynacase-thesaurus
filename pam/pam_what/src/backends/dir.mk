#
# Makefile fragment for backends
#

svdir      :=   $(dir)
dir        :=   $(dir)/backends

dirs       +=   $(dir)
srcs       +=   $(wildcard $(dir)/*.c)
libs       +=   -lpq
inc        +=   

subdirs    :=


ifneq ($(strip $(subdirs)),)
-include $(foreach sdir,$(subdirs),$(sdir)/dir.mk)
endif


dir        :=   $(svdir)