<?php
class Qss_Bin_Trigger_OSanLuong extends Qss_Lib_Trigger
{
	public function onInsert($object)
	{
		parent::init();
		//$this->updateProdutionQty($object);
	}
	
	public function onUpdate($object)
	{
		parent::init();
		//$this->updateProdutionQty($object);
		//$this->updateQty($object);
	}
	
	public function onDelete($object) 
	{
		parent::init();
		//$this->updateProdutionQty($object);
	}
	
	private function updateQty($object)
	{
		// Cong tong so luong phu pham trong truong hop thao do
		$common   = new Qss_Model_Extra_Extra();
        $MaLenhSX = @(string)$object->getFieldByCode('MaLenhSX')->szValue;
        $mSanXuat = Qss_Model_Db::Table('OSanXuat');
        $mSanXuat->where(sprintf(' MaLenhSX = "%1$s"', $MaLenhSX));
        $oSanXuat = $mSanXuat->fetchOne();
//		$ThaoDo   = @(int)$common->getDataset(array('module'=>'OSanXuat'
//					, 'where'=>array('MaLenhSX'=>$MaLenhSX), 'return'=>1))->ThaoDo;
        $ThaoDo   = @(int)$oSanXuat->ThaoDo;

		if($ThaoDo == 1)
		{
			// Neu thao do, set so luong ve disabled
			// Cong don so luong phu pham, hoac set ve 0 neu khong co phu pham
//			$PhuPham = $common->getDataset(array('module'=>'OSanLuong'
//						, 'where'=>array('IFID_M717'=>$object->i_IFID)));
            $mPhuPham = Qss_Model_Db::Table('OSanLuong');
            $mPhuPham->where(sprintf('IFID_M717 = %1$d', $object->i_IFID));

			$SoLuongPhuPham = 0;
			$insert         = array();
			
			foreach($mPhuPham->fetchAll() as $p)
			{
				$SoLuongPhuPham += $p->SoLuong;
			}
			
			
			$insert['OThongKeSanLuong'][0]['SoLuong'] = $SoLuongPhuPham;
			
			$service = $this->services->Form->Manual('M717',$object->i_IFID,$insert,false);
			if($service->isError())
			{
				$this->setError();
				$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
			}
		}
	}
	
	// @Remove
	// @todo: viet ham cap nhat lai so luong hoan thanh vao lenh san xuats
	private function updateProdutionQty(Qss_Model_Object $object)
	{
		$model = new Qss_Model_Extra_Production();		
		// @todo: Chua xet thong qua don vi tinh
		$oldRefItem = 0;
		$oldRefAttr = 0;
		$oldRefOperation = 0;
		$oldQty = 0;
		
		foreach ($this->_params->OSanLuong as $item)
		{
			if($item->IOID == $object->i_IOID)
			{
				$oldRefItem = $item->Ref_MaSP;
				$oldRefAttr = (int)@$item->Ref_ThuocTinh;
				$oldRefOperation = $item->Ref_CongDoan;
				$oldQty = $item->SoLuong;
				//$oldUOM = $item->Ref_DonViTinh;
			}
		}
		
		$newRefItem = $object->getFieldByCode('MaSP')->intRefIOID;
		$newRefAttr = $object->getFieldByCode('ThuocTinh')->intRefIOID;
		$newRefOperation = $object->getFieldByCode('CongDoan')->intRefIOID;
		$newQty = $object->getFieldByCode('SoLuong')->getValue();
		// $newUOM = $object->getFieldByCode('Ref_DonViTinh')->getValue();

		if($oldRefItem != $newRefItem || $oldRefAttr != $newRefAttr || $oldRefOperation != $newRefOperation )
		{
			// Thay doi so luong can tru bot so luong cu, cap nhat lai so luong cua sp moi
			$productionLineUpdate = $model->getMOLineUpdate($oldRefItem, $oldRefAttr, $oldRefOperation, $this->_params->IFID_M717);
			
			if($productionLineUpdate)
			{
				//echo $newQty.'-'.$oldQty.'-'.$productionLineUpdate->SoLuongThucTe;
				$insertPOLine['OChiTietSanXuat'][0]['SoLuongThucTe'] = 0;//$newQty - $oldQty + (int)@$productionLineUpdate->SoLuongThucTe;
				$insertPOLine['OChiTietSanXuat'][0]['ioid'] = $productionLineUpdate->IOID;
				//echo '<pre>'; print_r($insertPOLine); die;
				$service = $this->services->Form->Manual('M710',$productionLineUpdate->IFID_M710,$insertPOLine,false);
				if($service->isError())
				{
					$this->setError();
					$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
				}
			}
			
			$productionLineUpdate = $model->getMOLineUpdate($newRefItem, $newRefAttr, $newRefOperation, $this->_params->IFID_M717);
			
			if($productionLineUpdate)
			{		
				//echo $newQty.'-'.$oldQty.'-'.$productionLineUpdate->SoLuongThucTe;
				$insertPOLine['OChiTietSanXuat'][0]['SoLuongThucTe'] = $newQty - $oldQty + (int)@$productionLineUpdate->SoLuongThucTe;
				$insertPOLine['OChiTietSanXuat'][0]['ioid'] = $productionLineUpdate->IOID;
				//echo '<pre>'; print_r($insertPOLine); die;
				$service = $this->services->Form->Manual('M710',$productionLineUpdate->IFID_M710,$insertPOLine,false);
				if($service->isError())
				{
					$this->setError();
					$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
				}
			}
			
			$prLineUpdate = $model->getPRLineUpdate($oldRefItem, $oldRefAttr, $oldRefOperation, $this->_params->IFID_M717);
			
			if($prLineUpdate)
			{
				$insertPRLine['OYeuCauSanXuat'][0]['SoLuongThucTe'] = 0;//$newQty - $oldQty + (int)@$prLineUpdate->SoLuongThucTe;
				$insertPRLine['OYeuCauSanXuat'][0]['ioid'] = $prLineUpdate->IOID;
				//echo '<pre>'; print_r($insertPRLine); die;
				$service2 = $this->services->Form->Manual('M764',$prLineUpdate->IFID_M764,$insertPRLine,false);
				if($service2->isError())
				{
					$this->setError();
					$this->setMessage($service2->getMessage(Qss_Service_Abstract::TYPE_TEXT));
				}
			}
			
			$prLineUpdate = $model->getPRLineUpdate($newRefItem, $newRefAttr, $newRefOperation, $this->_params->IFID_M717);
			
			if($prLineUpdate)
			{
				$insertPRLine['OYeuCauSanXuat'][0]['SoLuongThucTe'] = $newQty - $oldQty + (int)@$prLineUpdate->SoLuongThucTe;
				$insertPRLine['OYeuCauSanXuat'][0]['ioid'] = $prLineUpdate->IOID;
				//echo '<pre>'; print_r($insertPRLine); die;
				$service2 = $this->services->Form->Manual('M764',$prLineUpdate->IFID_M764,$insertPRLine,false);
				if($service2->isError())
				{
					$this->setError();
					$this->setMessage($service2->getMessage(Qss_Service_Abstract::TYPE_TEXT));
				}
			}
			
		}
		elseif($newQty != $oldQty)
		{
			// Cap nhat lai so luong, lay so luong moi - so luong cu + hien co
			$productionLineUpdate = $model->getMOLineUpdate($oldRefItem, $oldRefAttr, $oldRefOperation, $this->_params->IFID_M717);
			
			if($productionLineUpdate)
			{
				//echo $newQty.'-'.$oldQty.'-'.$productionLineUpdate->SoLuongThucTe;
				$insertPOLine['OChiTietSanXuat'][0]['SoLuongThucTe'] = $newQty - $oldQty + (int)@$productionLineUpdate->SoLuongThucTe;
				$insertPOLine['OChiTietSanXuat'][0]['ioid'] = $productionLineUpdate->IOID;
				//echo '<pre>'; print_r($insertPOLine); die;
				$service = $this->services->Form->Manual('M710',$productionLineUpdate->IFID_M710,$insertPOLine,false);
				if($service->isError())
				{
					$this->setError();
					$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
				}
			}
			
			$prLineUpdate = $model->getPRLineUpdate($oldRefItem, $oldRefAttr, $oldRefOperation, $this->_params->IFID_M717);
			
			if($prLineUpdate)
			{
				$insertPRLine['OYeuCauSanXuat'][0]['SoLuongThucTe'] = $newQty - $oldQty + (int)@$prLineUpdate->SoLuongThucTe;
				$insertPRLine['OYeuCauSanXuat'][0]['ioid'] = $prLineUpdate->IOID;
				//echo '<pre>'; print_r($insertPRLine); die;
				$service2 = $this->services->Form->Manual('M764',$prLineUpdate->IFID_M764,$insertPRLine,false);
				if($service2->isError())
				{
					$this->setError();
					$this->setMessage($service2->getMessage(Qss_Service_Abstract::TYPE_TEXT));
				}
			}
		}
	}
}