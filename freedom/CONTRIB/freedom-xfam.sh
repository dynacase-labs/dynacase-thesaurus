#!/bin/bash 
# $Id: freedom-xfam.sh,v 1.1 2008/05/06 13:11:06 marc Exp $
if [ "$pgservice_core" == "" ]; then
    #load environement variable for freedom
  . /etc/freedom.conf
   wchoose -b
fi

if [ "$#" -ne 1 ] ; then
        echo "select name,title from docfam;" | PGSERVICE=$pgservice_freedom psql -E
        echo "Indiquez en paramètre à cette commande le numero de famille à supprimer (cf liste ci-dessus)"
   exit
fi

idFamille=$1
restitle=`echo "select title from docfam where name='"$idFamille"'" | PGSERVICE=$pgservice_freedom psql -E -A -x | awk -F"|" '{ print $2 }'`
resid=`echo "select id from docfam where name='"$idFamille"'" | PGSERVICE=$pgservice_freedom psql -E -A -x | awk -F"|" '{ print $2}'`
echo    ""
echo    "   *** Suppression de la famille '"$restitle"' ($idFamille), id=[$resid]."
read -p "   *** Confirmez-vous cette suppression o/[n] : " -n 1
[ "$REPLY" = "o" ] && {
 echo ""
 echo "drop table doc$resid;" | PGSERVICE=$pgservice_freedom psql -e
 echo "delete from docattr where docid=$resid;" | PGSERVICE=$pgservice_freedom psql -e
 echo "delete from doc where id=$resid;" | PGSERVICE=$pgservice_freedom psql -e
}
exit
