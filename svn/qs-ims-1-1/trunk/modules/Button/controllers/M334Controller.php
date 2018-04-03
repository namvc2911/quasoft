<?php
class Button_M334Controller extends Qss_Lib_Controller
{

	public function init()
	{
		$this->i_SecurityLevel = 15;
		parent::init();
		$this->headScript($this->params->requests->getBasePath() . '/js/common.js');
		$this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/popup.php';
	}

	/**
	 * Button: Nút tạo bàn giao
	 */
	public function uploadAction()
	{
		$this->html->data = Qss_Model_Db::Table('OMayChamCong')->fetchAll();
	}

	public function downloadAction()
	{
		$this->html->data = Qss_Model_Db::Table('OMayChamCong')->fetchAll();
	}

	public function uploadSaveAction()
	{
		$id = $this->params->requests->getParam('ioid',0);
		$table = Qss_Model_Db::Table('OMayChamCong');
		$table->where(sprintf('IOID = %1$d',$id));
		$dataSQL = $table->fetchOne();
		$service = Qss_Lib_Factory_Service::createInstance();
		$tb = Qss_Model_Db::Table('OTheChamCong');
		$tb->select('*');
		$thechamcong = $tb->fetchAll();
		if($dataSQL && count($thechamcong))
		{
			$fp = fsockopen("localhost", QSS_SOCKET_PORT, $errno, $errstr, 30);
			$arr = array('command'=>2
						,'uid'=>$this->_user->user_id
						,'machineid'=>$dataSQL->MaMay
						,'ip'=>$dataSQL->IP
						,'port'=>$dataSQL->Port,
						'data'=>$thechamcong);
			$string = Qss_Json::encode($arr);
			fwrite($fp, $string);
			$retval = fgets($fp, 1024 * 16);
			if(!$retval)
			{
				$service->setError();
				$service->setMessage('Cannot upload users!');
			}
			fclose($fp);
		}
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	public function downloadSaveAction()
	{
		$id = $this->params->requests->getParam('ioid',0);
		$table = Qss_Model_Db::Table('OMayChamCong');
		$table->where(sprintf('IOID = %1$d',$id));
		$dataSQL = $table->fetchOne();
		$service = Qss_Lib_Factory_Service::createInstance();
		if($dataSQL)
		{
			$fp = fsockopen("localhost", QSS_SOCKET_PORT, $errno, $errstr, 30);
			$arr = array('command'=>3
						,'uid'=>$this->_user->user_id
						,'machineid'=>$dataSQL->MaMay
						,'ip'=>$dataSQL->IP
						,'port'=>$dataSQL->Port,
						'data'=>array());
			$string = Qss_Json::encode($arr);
			fwrite($fp, $string);
			$retval = fgets($fp, 1024 * 16);
			if($retval)
			{
				$data = Qss_Json::decode($retval);
				$import = new Qss_Model_Import_Form('M334');
				foreach ($data as $item)
				{
					$arrUpdate = array('OTheChamCong'=>array());
					$arrUpdate['OTheChamCong'][] = array('MaChamCong'=>$item['MaChamCong']
					,'TenChamCong'=>$item['TenChamCong']
					,'SoTheChamCong'=>$item['SoTheChamCong']
					,'FingerIndex'=>$item['FingerIndex']
					,'TmpData'=>$item['TmpData']
					,'Privilege'=>$item['Privilege']
					,'Enabled'=>$item['Enabled']
					,'Flag'=>$item['Flag']
					,'MatKhau'=>$item['MatKhau']);
					$import->setData($arrUpdate);
				}
				$import->generateSQL();
			}
			else
			{
				$service->setError();
				$service->setMessage('Cannot download logs!');
			}
			fclose($fp);
		}
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}


}