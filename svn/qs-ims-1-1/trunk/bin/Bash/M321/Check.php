<?php
class Qss_Bin_Bash_M321_Check extends Qss_Lib_Bin
{
	public function __doExecute()
	{
		$tb = Qss_Model_Db::Table('OMayChamCong');
		$tb->select('IFID_M321,MaMay,IP,Port');
		$maychammcong = $tb->fetchAll();
		
		if(count($maychammcong))
		{
			$import = new Qss_Model_Import_Form('M321');
			$fp = fsockopen("localhost", QSS_SOCKET_PORT, $errno, $errstr, 30);
			foreach ($maychammcong as $item)
			{
				
				//$this->setError();
				$arr = array('command'=>1,'uid'=>$this->_user->user_id,'machineid'=>$item->MaMay,'ip'=>$item->IP,'port'=>$item->Port);
				$string = Qss_Json::encode($arr);
				fwrite($fp, $string);
				$retval = fgets($fp, 1024);
				if($retval)
				{
					$update = array('OMayChamCong'=>array(0=>array(
															'ifid'=>$item->IFID_M321
															,'TinhTrang'=>1
															,'LanKiemTraCuoi'=>date('Y-m-d H:i:s')))); 
					
				}
				else
				{
					$update = array('OMayChamCong'=>array(0=>array(
															'ifid'=>$item->IFID_M321
															,'TinhTrang'=>2
															,'LanKiemTraCuoi'=>date('Y-m-d H:i:s')))); 
					
				}
				$import->setData($update);
			}
			$import->generateSQL();
			fclose($fp);
		}
	}
}
?>
