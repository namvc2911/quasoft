<?php
class Qss_Bin_Bash_M321_Users extends Qss_Lib_Bin
{
	public function __doExecute()
	{
		$ctime = date('Y-m-d h:i:s');
		$fp = fsockopen("localhost", QSS_SOCKET_PORT, $errno, $errstr, 30);
		$arr = array('command'=>3,'uid'=>$this->_user->user_id,'machineid'=>$this->_params->MaMay,'ip'=>$this->_params->IP,'port'=>$this->_params->Port,'data'=>array());
		$string = Qss_Json::encode($arr);
		fwrite($fp, $string);
		$retval = fgets($fp, 1024 * 16);
		if($retval != '0')
		{
			$data = Qss_Json::decode($retval);
			//var_dump($data);die;
			$import = new Qss_Model_Import_Form('M321');
			$arrUpdate = array('OMayChamCong'=>array(0=>array('ifid'=>$this->_params->IFID_M321,'LanKiemTraCuoi'=>$ctime)));
			$arrUpdate['OUserMayChamCong'] = array();
			foreach ($data as $item)
			{
				$arrUpdate['OUserMayChamCong'][] = array('MaChamCong'=>$item['MaChamCong']
													,'TenChamCong'=>$item['TenChamCong']
													,'SoTheChamCong'=>$item['SoTheChamCong']
													,'FingerIndex'=>$item['FingerIndex']
													,'TmpData'=>$item['TmpData']
													,'Privilege'=>$item['Privilege']
													,'Enabled'=>$item['Enabled']
													,'Flag'=>$item['Flag']
													,'MatKhau'=>$item['MatKhau']);	
			}
			$import->setData($arrUpdate);
			$import->generateSQL();
		}
		else
		{
			$this->setError();
			$this->setMessage('Cannot download logs!');
		}
		fclose($fp);
	}
}
?>
