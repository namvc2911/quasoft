#!/bin/bash
/**

*/
MUSER="root"
MPASS="quasoft.vn"
MHOST="localhost"
MYSQL="mysql"
MYSQLDUMP="mysqldump"
BAK="/home/quasoft/backup/mysql"

dbname=$2

$MYSQLDUMP -u $MUSER -h $MHOST -p$MPASS --skip-lock-tables $dbname > latin.sql

php ./LatinToUnicode.php

$MYSQL -u $MUSER -h $MHOST -p$MPASS -e "drop database $dbname"
$MYSQL -u $MUSER -h $MHOST -p$MPASS -e "create database $dbname CHARACTER SET utf8 COLLATE utf8_general_ci
$MYSQLDUMP -u $MUSER -h $MHOST -p$MPASS $dbname < utf8.sql