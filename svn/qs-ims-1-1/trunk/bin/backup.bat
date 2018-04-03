set folder=E:\Backup

set dbname=hoaphat

set mysqlpath=c:\wamp\bin\mysql\mysql5.1.36\bin

echo Starting Backup of Mysql Database on server 

For /f "tokens=2-4 delims=/ " %%a in ('date /t') do (set dt=%%c-%%a-%%b)

For /f "tokens=1-4 delims=:." %%a in ('echo %time%') do (set tm=%%a%%b%%c%%d)

set bkupfilename=%1 %dt% %tm%.sql

echo Backing up to file: %bkupfilename%
%mysqlpath%\mysqldump  --lock-tables=false -u root %dbname% > %folder%\"%dbname%%bkupfilename%"

echo on

echo delete old backup

forfiles /p %folder% /s /m *.* /d -3 /c "cmd /c del @file : date >= 3days"

echo Backup Complete!