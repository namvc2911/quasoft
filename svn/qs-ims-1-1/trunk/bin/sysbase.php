<?php
$domain = @$argv[1];
defined('QSS_ROOT_DIR') || define('QSS_ROOT_DIR', dirname(dirname(__FILE__)));
defined('QSS_BASE_URL') || define('QSS_BASE_URL', 'http://'.$domain);
defined('QSS_PUBLIC_DIR') || define('QSS_PUBLIC_DIR', dirname(__FILE__));
defined('QSS_DATA_BASE') || define('QSS_DATA_BASE', QSS_ROOT_DIR . '/data');
//Set library when PHP needed, make it find in this location
set_include_path(implode(PATH_SEPARATOR, array(QSS_ROOT_DIR,/* */
get_include_path())));
require_once 'configs/Application.php';
require_once 'Qss/Application.php';
//set_time_limit(1);
$application = new Qss_Application(QSS_ROOT_DIR . '/configs/', QSS_CONFIG_FILE);
if ( isset($application->options->database) )
{
	Qss_Db::factory((array) $application->options->database);
}
/*Load php setting */
if ( isset($application->options->php) )
{
	foreach ($application->options->php as $key => $val)
	{
		ini_set($key, $val);
	}
}
?>