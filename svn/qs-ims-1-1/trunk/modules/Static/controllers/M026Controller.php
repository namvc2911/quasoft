<?php
/**
 *
 * @author HuyBD
 *
 */
class Static_M026Controller extends Qss_Lib_Controller
{

	/**
	 *
	 * @return unknown_type
	 */
	public function init ()
	{
		parent::init();
	}

	/**
	 *
	 * @return void
	 */
	public function indexAction ()
	{
		$this->html->data = $this->getFiles();
	}

	/**
	 *
	 * @author HuyBD
	 * @return void
	 */
	public function backupAction ()
	{
		$file = $this->params->requests->getParam('file','');
		$model = new Qss_Model_System_Backup();
		if($file)
		{
			$model->backup($file);
		}
		$arr = array('error'=>false);
		echo Qss_Json::encode($arr);
	}

	/**
	 * Edit page
	 *
	 * @author HuyBD
	 * @return void
	 */
	public function restoreAction ()
	{
		$file = $this->params->requests->getParam('file','');
		$model = new Qss_Model_System_Backup();
		if($file)
		{
			$model->restore($file);
		}
		$arr = array('error'=>false);
		echo Qss_Json::encode($arr);
	}
	private function getFiles()
	{
		$filterArr = array();
		$existsArr = array();
		$folder = QSS_ROOT_DIR . '/backup/';
		if(file_exists($folder))
		{
			$arrFile = scandir($folder);
			foreach($arrFile as $filename)
			{
				$file_parts = pathinfo($filename);
				if($file_parts['extension'] == 'sql')
				{
					$filterArr[] = $file_parts['basename'];
				}
			}
		}
		return $filterArr;
	}

}
?>