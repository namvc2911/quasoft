#!/bin/bash
/**

*/
MUSER="root"
MPASS="quasoft.vn"
MHOST="localhost"
MYSQL="mysql"
MYSQLDUMP="mysqldump"
BAK="/home/quasoft/backup/mysql"
GZIP="gzip"
### FTP SERVER Login info ###
#FTPU="vuhung@vvk.quasoft.vn"
#FTPP="abc123"
#FTPS="vvk.quasoft.vn"
NOW=$(date +"%d-%m-%Y")

[ ! -d $BAK ] && mkdir -p $BAK || /bin/rm -f $BAK/*

DBS="$($MYSQL -u $MUSER -h $MHOST -p$MPASS -Bse 'show databases')"
for db in $DBS
do
if ["$db" = "ttco" && "$db" = "pos" && "$db" = "vit"]; then
 FILE=$BAK/$db.$NOW-$(date +"%T").gz
 $MYSQLDUMP -u $MUSER -h $MHOST -p$MPASS --skip-lock-tables $db | $GZIP -9 > $FILE
fi
done

#lftp -u $FTPU,$FTPP -e "mkdir /mysql/$NOW;cd /mysql/$NOW; mput $BAK/*; quit" $FTPS