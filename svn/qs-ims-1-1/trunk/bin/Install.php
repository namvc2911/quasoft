<?php
include "sysbase.php";
$dir = @$argv[2];
//change htpp config file
$file = file('c:/xampp/apache/conf/httpd.conf');
$lines = array_map('read_file_line', $file);
$lines[246] = sprintf('DocumentRoot "%1$s/public_html"', $dir);
$lines[247] = sprintf('<Directory "%1$s/public_html">', $dir);
$lines = array_values($lines);
//To save it again, implode the array with newline:
$content = implode(PHP_EOL, $lines);
file_put_contents('c:/xampp/apache/conf/httpd.conf', $content);

//change php.ini
$file = file('c:/xampp/php/php.ini');
$lines = array_map('read_file_line', $file);
$lines[371] = sprintf('max_execution_time=300');
$lines[381] = sprintf('max_input_time=600');
$lines[388] = sprintf('max_input_vars = 10000');
$lines[392] = sprintf('memory_limit=1280M');
$lines[819] = sprintf('upload_max_filesize=100M');
$lines[916] = sprintf('extension=php_soap.dll');
$lines[922] = sprintf('extension=php_xsl.dll');
$lines[2033] = sprintf('openssl.cafile=%1$s/cacert.pem',$dir);

$lines = array_values($lines);
//To save it again, implode the array with newline:
$content = implode(PHP_EOL, $lines);
file_put_contents('c:/xampp/php/php.ini', $content);

//change my.ini
$file = file('c:/xampp/mysql/bin/my.ini');
$lines = array_map('read_file_line', $file);
$lines[34] = sprintf('key_buffer_size = 160M');
$lines[35] = sprintf('max_allowed_packet = 100M');
$lines[36] = sprintf('sort_buffer_size = 10M');
$lines[37] = sprintf('net_buffer_length = 100K');
$lines[38] = sprintf('read_buffer_size = 10M');
$lines[39] = sprintf('read_rnd_buffer_size = 10M');
$lines[40] = sprintf('myisam_sort_buffer_size = 100M');

$lines[145] = sprintf('innodb_log_file_size = 50M');
$lines[146] = sprintf('innodb_log_buffer_size = 80M');
$lines[148] = sprintf('innodb_lock_wait_timeout = 500');

$lines[159] = sprintf('max_allowed_packet = 160M');

$lines = array_values($lines);
//To save it again, implode the array with newline:
$content = implode(PHP_EOL, $lines);
file_put_contents('c:/xampp/mysql/bin/my.ini', $content);

//change backup
$file = file($dir . '/bin/backup.bat');
$lines = array_map('read_file_line', $file);
$lines[0] = sprintf('set folder=%1$s\backup',$dir);

$lines = array_values($lines);
//To save it again, implode the array with newline:
$content = implode(PHP_EOL, $lines);
file_put_contents($dir . '/bin/backup.bat', $content);

$db    = Qss_Db::getAdapter('mysql');
$db->open();
$sql = sprintf('DROP DATABASE IF EXISTS cmms');
$db->execute($sql);
$sql = sprintf('CREATE DATABASE cmms CHARACTER SET utf8 COLLATE utf8_general_ci');
$db->execute($sql);

$db    = Qss_Db::getAdapter('main');
$db->open();
// Temporary variable, used to store current query
$templine = '';
$filename = $dir.'\database\install.sql';
$lines = file($filename);
// Loop through each line
foreach ($lines as $line)
{
	// Skip it if it's a comment
	if (substr($line, 0, 2) == '--' || $line == '')
	continue;

	// Add this line to the current segment
	$templine .= $line;
	// If it has a semicolon at the end, it's the end of the query
	if (substr(trim($line), -1, 1) == ';')
	{
		// Perform the query
		$db->execute($templine);
		// Reset temp variable to empty
		$templine = '';
	}
}
function read_file_line($value)
{
	return rtrim($value, PHP_EOL);
}