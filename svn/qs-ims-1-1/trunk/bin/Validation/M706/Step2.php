<?php

class Qss_Bin_Validation_M706_Step2 extends Qss_Lib_Warehouse_WValidation
{
	/**
	 * @one: Kiểm tra xem lịch có trùng thời gian với lịch khác hay không?
	 */
	public function onNext()
	{
		parent::init();
//		$wCalModel   = new Qss_Model_Extra_WCalendar();
//		$start       = $this->_params->NgayBatDau;
//		$end         = $this->_params->NgayKetThuc;
//		$startMic    = strtotime($start);
//		$endMic      = $end?strtotime($end):0;
//		$apply       = true;

        // @todo: Kiem tra xem thiet bi co the dieu dogn duoc khong?
        /*
		$wCals       = $wCalModel->getWCalOfEquipByTimeByEqCal(
			array($this->_params->Ref_MaThietBi)
			, Qss_Lib_Date::displaytomysql($start)
			, Qss_Lib_Date::displaytomysql($end));
		
		if(count($wCals))
		{
			$this->setError();
			$this->setMessage($this->_translate(1));
		}
        */
	}
	
	/**
	 * @one: Cập nhật lại lịch làm việc và khu vực vào danh sách thiết bị
	 * @todo: Kiem tra xem dieu dong thiet bi co dieu dong truoc do ko moi cap nhat lai vao thiet bi
	 * vd: ngay 15: dieu dong da ap dung roi, tao them ngay 10 thi ko cap nhat thiet bi
	 */
	public function next()
	{
		parent::init();
//		$common = new Qss_Model_Extra_Extra();
//        // !$this->checkUpdateEquipInfoError() &&
//		if(($this->_params->MaKhuVuc || $this->_params->LichLamViec || $this->_params->DuAn))
//		{
//            foreach($this->_params->ODanhSachDieuDongThietBi AS $item)
//            {
//
//				$thietBi = $common->getTableFetchOne('ODanhSachThietBi', array('IOID'=>$item->Ref_MaThietBi));
//
//				if($thietBi)
//				{
//					$insert = array();
//					$insert['ODanhSachThietBi'][0]['MaKhuVuc']     = (int)$this->_params->Ref_MaKhuVuc;
//					$insert['ODanhSachThietBi'][0]['LichLamViec']  = (int)$this->_params->Ref_LichLamViec;
//					$insert['ODanhSachThietBi'][0]['DuAn']         = (int)$this->_params->Ref_DuAn;
//					$insert['ODanhSachThietBi'][0]['ioid']         = (int)$thietBi->IOID;
//
//
//					$service = $this->services->Form->Manual('M705', $thietBi->IFID_M705, $insert, false);
//					if ($service->isError())
//					{
//						$this->setError();
//						$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
//					}
//				}
//
//            }
//		}
	}
	
	public function onAlert()
	{
//	    parent::init();
//	    $end   = $this->_params->NgayKetThuc;
//	    $today = date('Y-m-d');
//
//	    if($end && Qss_Lib_Date::compareTwoDate($end, $today) != -1) // >=
//	    {
//	        $this->setMessage('Thiết bị '.$this->_params->MaThietBi.' đã quá hạn điều động bạn cần chuyển tình trạng kết thúc điều động cho thiết bị này.');
//	        $this->setError();
//	    }
	}


	// @todo: Ham thieu dieu kien theo thiet bi, ham phai duoc kiem tra truoc khi tien hang next
	private function checkUpdateEquipInfoError()
	{
//	    $commonModel = new Qss_Model_Extra_Extra();
//
//	    $over = $commonModel->getTable(
//	        array('count(1) AS Total')
//	        , 'OLichThietBi'
//	        , sprintf(' ifnull(NgayBatDau, \'\') <> \'\' AND NgayBatDau >= \'%1$s\' AND IFID_M706 <> %2$d'
//               , Qss_Lib_Date::displaytomysql($this->_params->NgayBatDau)
//	           , $this->_params->IFID_M706)
//	        , array()
//	        , 'NO_LIMIT'
//	        , 1
//        );
//// 	    echo $over; die;
//	    return (($over?$over->Total:0) > 0)?TRUE:FALSE;
	}

}
