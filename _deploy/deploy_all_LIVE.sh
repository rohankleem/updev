#!/bin/bash
#ref: https://www.andrewcbancroft.com/blog/musings/make-bash-script-executable/
rsync --rsh='ssh' -av --include-from="../_rsync/.rsync_all" ../ buildiod@vda4300.is.cc:domains/unipixelhq.com --delete-after --chmod=Du=rwx,Dgo=rx,Fu=rw,Fog=r
