#!/bin/bash -
#
. /etc/freedom.conf
#
cd $wpub
[ ! -d $wpub/virtual ] && {
    wchoose -b
    logger "running freedom API wgcal_cronalert for default configuration";
    $wpub/wsh.php --api=wgcal_cronalert > /dev/null 2>&1
    exit
}

vd=`ls $wpub/virtual`
for vdn in $vd; do
  wchoose $vdn
  logger "running freedom API wgcal_cronalert for $vdn configuration ($dbpsql)";
  $wpub/wsh.php --api=wgcal_cronalert > /dev/null 2>&1
done
