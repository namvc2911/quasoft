<?php
class Qss_View_Common_FullLandscape extends Qss_View_Abstract
{

	public function __doExecute ()
	{
        $user             = Qss_Register::get('userinfo');
		$this->html->ini  = $this->loadHeaderIniFile();
		$this->html->user = $user;

		$dept = Qss_Model_Db::Table('qsdepartments');
		$dept->where(sprintf(' DepartmentID = %1$d', $user->user_dept_id));
        $objDept = $dept->fetchOne();

		$this->html->CongTy = $objDept?$objDept->Name:'';
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