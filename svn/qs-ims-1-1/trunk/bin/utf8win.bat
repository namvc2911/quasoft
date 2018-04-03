set mysqlpath=c:\xampp\mysql\bin
set dbname=%2
%mysqlpath%\mysqldump -u root --lock-tables=false %dbname% > D:\latin.sql
"C:\xampp\php\php.exe" "LatinToUnicode.php"
%mysqlpath%\mysql -u root -e "drop database %dbname%"
%mysqlpath%\mysql -u root -e "create database %dbname% CHARACTER SET utf8 COLLATE utf8_general_ci"
%mysqlpath%\mysql -u root %dbname% < D:\utf8.sql