<?php
class Qss_Model_M321_Main extends Qss_Model_Abstract
{
	public function updateTimesheet($maychamcong)
	{
		$ret = true;
		//tải in/out về máy
		$ctime = date('Y-m-d h:i:s');
		if($maychamcong)
		{ 
			$time = Qss_Lib_Date::i_fMysql2Time($maychamcong->LanLayDuLieuCuoi);
			$thechamcong = new StdClass();
			$thechamcong->year = date('Y',$time);
			$thechamcong->month = date('m',$time);
			$thechamcong->day = date('d',$time);
			$thechamcong->hour = date('h',$time);
			$thechamcong->minute = date('i',$time);
			$thechamcong->second = date('s',$time);
			$fp = fsockopen("localhost", QSS_SOCKET_PORT, $errno, $errstr, 30);
			//$this->setError();
			$arr = array('command'=>4,'uid'=>$this->_user->user_id,'machineid'=>$maychamcong->MaMay,'ip'=>$maychamcong->IP,'port'=>$maychamcong->Port,'data'=>array($thechamcong));
			$string = Qss_Json::encode($arr);
			fwrite($fp, $string);
			$retval = fgets($fp, 1024 * 16);
			if($retval != '0')
			{
				$data = Qss_Json::decode($retval);
				if(is_array($data))
				{
					//var_dump($data);die;
					$import = new Qss_Model_Import_Form('M321');
					$arrUpdate = array('OMayChamCong'=>array(0=>array('ifid'=>$maychamcong->IFID_M321,'LanLayDuLieuCuoi'=>$ctime)));
					$arrUpdate['OLichSuChamCong'] = array();
					foreach ($data as $item)
					{
						$arrUpdate['OLichSuChamCong'][] = array('MaChamCong'=>$item['MaChamCong'],'VerifyMode'=>$item['VerifyMode'],'InOutMode'=>$item['InOutMode'],'Ngay'=>$item['Ngay']);	
					}
					$import->setData($arrUpdate);
					$import->generateSQL();
				}
			}
			else
			{
				$ret = false;
			}
			fclose($fp);
		}
		return $ret;
	}
	public function getInOut($startdate,$enddate,$department)
	{
		$sql = sprintf('select OTheChamCong.MaNhanVien,OTheChamCong.Ref_MaNhanVien,OTheChamCong.TenNhanVien,
							DATE_FORMAT(min(Ngay),"%%Y-%%m-%%d") as NgayVao,DATE_FORMAT(max(Ngay),"%%Y-%%m-%%d") as NgayRa,
							DATE_FORMAT(min(Ngay),"%%H:%%i:%%s") as GioVao,DATE_FORMAT(max(Ngay),"%%H:%%i:%%s") as GioRa,
							Ngay,ODanhSachNhanVien.MaPhongBan
							from OLichSuChamCong
							inner join OTheChamCong on OTheChamCong.MaChamCong = OLichSuChamCong.MaChamCong
							inner join ODanhSachNhanVien on ODanhSachNhanVien.IOID = OTheChamCong.Ref_MaNhanVien
							where Ngay between %1$s and %2$s  
							group by OLichSuChamCong.MaChamCong,Date(Ngay)'
				, $this->_o_DB->quote($startdate)
				, $this->_o_DB->quote($enddate));
		return $this->_o_DB->fetchAll($sql);
	}
}