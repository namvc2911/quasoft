<?php
class Qss_Bin_Trigger_ODanhSachXuatKho extends Qss_Lib_Trigger
{
	private $amountField = 'ThanhTien';



	public function onInsert($object)
	{
		parent::init();
		//$this->dateNotify($object);
	}

	public function onUpdate($object)
	{
		parent::init();
		//$this->dateNotify($object);
	}

	public function onUpdated($object)
	{
		parent::init();
		$this->deleteStockStatus($object);
	}

	public function onDeleted($object)
	{
		parent::init();
		$this->deleteStockStatus($object);
	}

//	private function dateNotify(Qss_Model_Object $object)
//	{
//		$maintain     = new Qss_Model_Extra_Maintenance();
//		$refEq        = (int)$object->getFieldByCode('MaThietBi')->intRefIOID;
//		$refItem      = (int)$object->getFieldByCode('MaSP')->intRefIOID;
//		$deliveryDate = $this->_params->NgayChungTu;
//
//		// Thực hiện kiểm tra canh bao khi co mã thiết bị
//		if($refEq)
//		{
//			// Lay thong tin phu tung (Thong tin don gian ko viet sql)
//			$info = $this->getItemInfo($refItem, $refEq);
//
////			//@test
////			if(!$info)
////			{
////				$info = new stdClass();
////				$info->SoNgayCanhBao = '3';
////			}
//
//			// Neu co phu tung nay thi moi thuc hien kiem tra canh bao
//			if($info && @(double)$info->SoNgayCanhBao)
//			{
//				// Tinh toan thoi gian xuat phu tung lan gan day nhat
//				$last = $maintain->getLastestOutputDateOfItemForEquip($refItem, $refEq, $object->i_IFID);
//
////				//@test
////				if(!$last)
////				{
////					$last = new stdClass();
////					$last->Last = '2014-10-21';
////				}
////				//@test
////				if($last && !$last->Last)
////				{
////					$last->Last = '2014-10-21';
////				}
//
//				// Neu co ngay xuat kho truoc do moi tien hanh kiem tra tiep
//				if($last && @(string)$last->Last && @(string)$last->Last != '0000-00-00')
//				{
//					$deliveryDate = (!$deliveryDate || $deliveryDate=='0000-00-00')?date('Y-m-d'):$deliveryDate;
//					$divDate      = Qss_Lib_Date::divDate($last->Last, $deliveryDate);
//
//					if($divDate <= $info->SoNgayCanhBao)
//					{
//						$this->setMessage('Phụ tùng này mới được xuất cho thiết bị '.$divDate.' ngày trước trong '
//							. ' phiếu xuất kho <a href="#1" onclick="openModule(\'M506\',\'/user/form/edit?ifid='.$last->IFID
//							.'&deptid=1\')">'.$last->DocNo.'</a>!');
//						//$this->setError();
//					}
//				}
//			}
//		}
//
//	}
	
//	private function getItemInfo($refItem, $refEq)
//	{
//		$common   = new Qss_Model_Extra_Extra();
//		$info     = $common->getDataset(array(
//			'select'=>'pt.*',
//			'module'=>'ODanhSachThietBi',
//			'where'=>array(
//				'cm.IOID'=>$refEq,
//				'sp.IOID'=>$refItem
//			),
//			'join'=>array(
//				array(
//					'type'=>1,
//					'table'=>'ODanhSachPhuTung',
//					'alias'=>'pt',
//					'condition'=>array(
//						array(
//							'alias1'=>'cm',
//							'col1'=>'IFID_M705',
//							'alias2'=>'pt',
//							'col2'=>'IFID_M705'
//						)
//					)
//				),
//				array(
//					'type'=>1,
//					'table'=>'OSanPham',
//					'alias'=>'sp',
//					'condition'=>array(
//						array(
//							'alias1'=>'pt',
//							'col1'=>'Ref_MaSP',
//							'alias2'=>'sp',
//							'col2'=>'IOID'
//						)
//					)
//				)
//			),
//			'return'=>1
//		));
//		return $info;
//	}
	
	
	
	private function deleteStockStatus($object)
	{
		$params     = array();
		$mInv       = new Qss_Model_Inventory_Inventory();
		$IOIDArray  = $mInv->getStockStatusOfOutput($object->i_IFID);

		foreach ($IOIDArray as $val)
		{
			if($val->OutputLineIOID == 0)
			{
				$params['OThuocTinhChiTiet'][] = $val->IOID;
			}
		}

		//echo '<pre>'; print_r($params); die;

		if(isset($params['OThuocTinhChiTiet']) && count($params['OThuocTinhChiTiet']))
		{
			// Tiến hành xóa các dòng trong bảng lot & serial tương ứng với IOID của params truyền vào
			$service = $this->services->Form->Remove('M506',$object->i_IFID,$params);
			if($service->isError())
			{
				$this->setError();
				$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
			}
		}
	}
	
}
?>