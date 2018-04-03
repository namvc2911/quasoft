<?php
class Qss_Bin_Validation_M603_Step2 extends Qss_Lib_Warehouse_WValidation
{
	public function onNext()
	{
		parent::init();
		$danhSach       = $this->_params->ODanhSachCK;
		$deliveryDate   = $this->_params->NgayChuyenHang;
                $stockStatus    = $this->_params->OTrangThaiLuuTruCK;
		$model 		= new Qss_Model_Extra_Warehouse();
		$product        = new Qss_Model_Extra_Products();
		
		if(count((array)$danhSach) == 0) {
			$this->setError();
			$this->setMessage("{$this->_translate(1)}");
		}
		
		
		// Kiểm tra ngày nhận lại
		if(!$deliveryDate || $deliveryDate == '0000-00-00') 
		{
			$this->setError();
			$this->setMessage("{$this->_translate(2)}");
		}
                
                
                // * Stock status update successfully?
		// Total qty in order (only items has zone, bin, lot or serial)
                $totalStockStatus = 0;
		$total = $model->getTotalReceiveHasAttr($this->_params->IFID_M603, 'M603');
		// Total qty in stock status
		foreach ($stockStatus as $val)
		{
			$totalStockStatus += $val->SoLuong;
		}

		if($total && $total != $totalStockStatus)
		{
			$this->setError();
			$this->setMessage($this->_translate(3));
		}
		
		// Kiểm tra xem có thuộc tính bắt buộc hay ko ?
		foreach ($danhSach as $value) 
		{
			$checkRequires = $product->checkAttributeRequires($value->MaSP);
			if(!$value->ThuocTinh && $checkRequires)
			{
				$this->setError();
				$this->setMessage("{$this->_translate(5)} {$value->MaSP} {$this->_translate(5)}");
			}
		}
		
		// Kiem tra suc chua cua  bin
		$capacity  = $model->checkCapacity('M603', $this->_params->IFID_M603);

		$keepValue = array();
		foreach ($capacity as $val)
		{
			$newQty = 0;
			
			if(!$val->Serial && !$val->Lot)
			{
				foreach ($capacity as $valin)
				{
                                    //$val->ZoneCode && ($val->ZoneCode == $valin->ZoneCode) && 
					if((!$val->BinCode || ($val->BinCode == $valin->BinCode) ) )
					{
						$newQty += $valin->NewQty + $valin->HasQty;
					}
				}
			}
			else
			{
				$newQty = $val->NewQty + $val->HasQty;;
			}
			
			//$key = $val->ZoneCode.'.'.$val->BinCode;
                        $key = $val->BinCode;
                        
			if($val->BinCode && !in_array($key, $keepValue))
			{
				switch ($val->Condition)
				{
					case 1: // Bin co san pham
						if($val->BinCapacity && ($newQty > $val->BinCapacity) )
						{
							$this->setError();
							$this->setMessage("{$val->BinCode} {$this->_translate(18)} " );
						}
					break;
					
					case 2: // Bin co don vi tinh
						if($val->BinCapacity && ($newQty > $val->BinCapacity) )
						{
							$this->setError();
							$this->setMessage("{$val->BinCode} {$this->_translate(18)}");
						}
					break;
					
                                        /*
					case 3: // Zone co san pham
						if($val->ZoneCapacity && ($newQty > $val->ZoneCapacity) )
						{
							$this->setError();
							$this->setMessage("{$val->ZoneCode} {$this->_translate(18)}");
						}
					break;
					
					case 4: // Zone co don vi tinh
						if($val->ZoneCapacity && ($newQty > $val->ZoneCapacity) )
						{
							$this->setError();
							$this->setMessage("{$val->ZoneCode} {$this->_translate(18)}");
						}
					break;		
                                         * 
                                         */										
				}
			}
			
                        /*
			if($val->ZoneCode)
			{
				$keepValue[] = $val->ZoneCode.'.'.$val->BinCode;
			}
                         * 
                         */
                        if($val->BinCode)
			{
				$keepValue[] = $val->BinCode;
			}
		}
		//$this->checkWarehouse();
	}
	
	/**
	 * create incoming shipments
	 */
	public function next()
	{
		parent::init();
		$field = array('MaSP', 'ThuocTinh', 'SoLuong', 'NgayYeuCau', '', '', '', 'KhoLH', 'SoChungTu','DonViTinh') ;
		$insertFieldLabel = array('Partner'=>'MaDoiTac', 'Date'=>'Ngay', 
								  'Item'=>'MaSP', 'Attr'=>'ThuocTinh', 
								  'Qty'=>'SoLuong', 'Price'=>'DonGia',
								  'Module'=>'Module','Warehouse'=>'Kho', 'Des'=>'MoTa'
                                                                    , 'UOM'=>'DonViTinh');
		$this->insertIncomingOutgoing($field, $insertFieldLabel, 'OHangDoiXuat', 'ODanhSachCK', 'OChuyenKho' , 
									  'M603', 'M611', '', false);
		$field = array('MaSP', 'ThuocTinh', 'SoLuong', 'NgayChuyenHang', '', '', '', 'KhoCD', 'SoChungTu', 'DonViTinh') ;
		$this->insertIncomingOutgoing($field, $insertFieldLabel, 'OHangDoiNhap', 'ODanhSachCK', 'OChuyenKho' , 
									  'M603', 'M610', '', false);
	}
}