<?php
class Qss_View_Common_Landscape extends Qss_View_Abstract
{
public function __doExecute ()
	{
		$this->html->ini = $this->loadHeaderIniFile();
	}
	
	public function getUrl()
	{
		return $_SERVER['REQUEST_URI'];
	}
	
	public function loadHeaderIniFile()
	{
		$retval        = array();
		$url           = self::getUrl();
		$urlToFileName = $url?trim(str_replace(array('/','\\'), array('-', '-'), (string)$url), '-'):'';
		$pathToIniFile = $urlToFileName?
			'file://'.QSS_DATA_DIR.'/'
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