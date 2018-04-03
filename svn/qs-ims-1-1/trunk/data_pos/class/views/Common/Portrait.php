<?php
class Qss_View_Common_Portrait extends Qss_View_Abstract
{

	public function __doExecute ($code='',$date='')
	{
		$this->html->ini = $this->loadHeaderIniFile();
	}
		
	public function getUrl()
	{
		$arr = explode('?', $_SERVER['REQUEST_URI']);
		return $arr[0];
	}
	
	public function loadHeaderIniFile()
	{
		$retval        = array();
		$url           = self::getUrl();
		$urlToFileName = $url?trim(str_replace(array('/','\\'), array('-', '-'), (string)$url), '-'):'';
		$pathToIniFile = $urlToFileName?
			QSS_DATA_DIR.'/'
			.'template/'
			.$urlToFileName.'.ini':'';
		if($pathToIniFile && file_exists($pathToIniFile))
		{
			$ini_array = parse_ini_file($pathToIniFile);
			
			if(count($ini_array))
			{
				$retval = $ini_array;
			}
		}
		return $retval;
	}	
}
?>