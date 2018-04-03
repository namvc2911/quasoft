<?php
Class Qss_Service_Extra_Warehouse_Shipment_Stockstatus_Save extends  Qss_Service_Abstract
{
	public function __doExecute($params)
	{
		if($this->validate($params))
		{
			$this->updateAttr($params);
			$this->setMessage('Cập nhật thành công !');
		}
		else
		{
			$this->setError();
		}
	}
	
	private function validate($params)
	{
		$model 	   = new Qss_Model_Extra_Products();
		//$serialLot = unserialize(Qss_Lib_Extra::formatUnSerialize($params['serialLot']));
		$serialLot = unserialize($params['serialLot']);
		$return    = true;
		$qtyLine   = isset($params['qtyLine'])?$params['qtyLine']:array();
		$qty 	   = isset($params['qty'])?$params['qty']:array();
                $obj       = (isset($params['obj']) && $params['obj'])?$params['obj']:'';
		$exists    = $model->getOldStockStatus($params['module'], $params['ioid'], $params['ifid'], $obj);
		//$existQty 		= count((array)$exists);
		$existQty  = 0;
                
		foreach ($exists as $value) 
                {
                    $existQty += $value->SoLuong;
		}
                
		$qtyHas  = array();
		$qtyDiv  = array();
		
		for($i=0;$i<count($qtyLine);$i++)
		{
			// Kiểm tra có phải là số hợp lệ
			if(!is_numeric($qty[$i]) || $qty[$i]<0 )
			{
				$this->setMessage('Số lượng lấy ra trên dòng '.$params['stt'][$i].' không phải là số dương lớn hơn bằng 0!');
				$return = false;
			}
			
			// Kiểm tra có lớn hơn số lượng của dòng
			if($qtyLine[$i] < $qty[$i])
			{
				$this->setMessage('Số lượng lấy ra trên dòng '.$params['stt'][$i].' lớn hơn số lượng hiện có trên dòng!');
				$return = false;
			}
			
			if($params['check'][$i])
			{
				$qtyHas[] = $qty[$i] - $params['qtyKeeper'][$i];
			}
			
			if ($params['existsIOID'][$i] && !$params['check'][$i])
			{
				$qtyDiv[] = $params['qtyKeeper'][$i];
			}

		}

		/* @todo: Tinh sum nay bi sai roi */
		$sum  = array_sum($qtyHas) + $existQty - array_sum($qtyDiv);
		//echo array_sum($qtyHas) .'-'. $existQty .'-'.  array_sum($qtyDiv); die;
		if($sum < $serialLot->itemQty)
		{
			$this->setMessage('Tổng số lượng lấy ra nhỏ hơn số lượng sản phẩm !');
		}
		elseif ($sum > $serialLot->itemQty)
		{
			$this->setMessage('Tổng số lượng lấy ra lớn hơn số lượng sản phẩm !');
			$return = false;
		}
		
		return $return;
	}
	
	private function updateAttr($params)
	{
		// Trong hoa phat doi tuong vattu dung chung giua pbt m759 va ke hoach m724
		//$vattuObject = (_QSS_DATA_FOLDER_ == 'data_hoaphat')?'OVatTu':'OVatTuPBT';
		$qty        = isset($params['qty'])?$params['qty']:array();
		$model      = new Qss_Model_Extra_Products();
		//$serialLot  = unserialize(Qss_Lib_Extra::formatUnSerialize($params['serialLot']));
		$serialLot  = unserialize($params['serialLot']);
		$data       = array();
		$delete     = array();
                $stockObj   = 'OThuocTinhChiTiet';
                
                // Neu so ban ghi cu lon hon so ban ghi moi can xoa ban ghi cu di moi cap nhat duoc
                switch ($params['obj'])
		{
			case 'OSanLuong': // Output statistics
				$stockObj = 'OTrangThaiLuuTruTP';
			break;
			case 'ONVLDauVao': // Material consumption
				$stockObj = 'OTrangThaiLuuTruNVL';
			break;
			case 'OSanPhamLoi':// Defect statistics
				$stockObj = 'OTrangThaiLuuTruPL';
			break;
			case 'OCongCuCauThanh'://toolset statistics
				$stockObj = 'OTrangThaiLuuTruCCDC';
			break;
                        case 'OPheLieu':
                                $stockObj = 'OTrangThaiLuuTruPLBT';
                        break;
                        case 'OVatTuPBT':
                                $stockObj = 'OTrangThaiLuuTruVT';
                        break;
		}
		
		for($i=0;$i<count($qty);$i++)
		{
			if(!$params['existsIOID'][$i] || $params['check'][$i])
			{
				if(isset($IOIDExists->IOID) || (isset($params['qty'][$i]) && $params['qty'][$i]>0) )
				{
					$data[$i]['MaSanPham']   = $serialLot->itemCode;
                                        $data[$i]['DonViTinh']   = $serialLot->itemUOM;
					$data[$i]['Kho'] 	 = isset($params['warehouse'][$i])?$params['warehouse'][$i]:$serialLot->warehouse;
					$data[$i]['SoLo'] 	 = isset($params['lot'][$i])?$params['lot'][$i]:'';
					$data[$i]['SoSerial']    = isset($params['serial'][$i])?$params['serial'][$i]:'';
                                        $data[$i]['NgayNhan']    = isset($params['receiveDate'][$i])?$params['receiveDate'][$i]:'';
                                        $data[$i]['NgaySX']      = isset($params['productDate'][$i])?$params['productDate'][$i]:'';
                                        $data[$i]['NgayHan']     = isset($params['expiryDate'][$i])?$params['expiryDate'][$i]:'';
					$data[$i]['MaThuocTinh'] = isset($params['attr'][$i])?$params['attr'][$i]:'';
					$data[$i]['Zone']        = isset($params['zone'][$i])?$params['zone'][$i]:'';
					$data[$i]['Bin']         = isset($params['bin'][$i])?$params['bin'][$i]:'';
					$data[$i]['SoLuong'] 	 = $params['qty'][$i];
					if($params['existsIOID'][$i])
					{
						$data[$i]['ioid']  	 = $params['existsIOID'][$i];
					}
					else 
					{
						if($params['ioid'])
						{
							$data[$i]['ioidlink'] = $params['ioid'];
						}
						else 
						{
							$data[$i]['ioidlink'] = $model->getIOIDfromIFID($params['module'], $params['ifid']);
						}
					}
					
				}
				
				if(isset($params['qty'][$i]) && $params['qty'][$i] == 0 && $params['existsIOID'][$i])
				{
					$delete[] = $params['existsIOID'][$i];
				}
			}
			elseif($params['existsIOID'][$i] && !$params['check'][$i]) 
			{
				$delete[] = $params['existsIOID'][$i];
			}
		}
                
                

		if(count($data))
		{
			///------------------------- Cập nhật giá trị --------------------------------
			$data = array($stockObj=>$data);
			$service = $this->services->Form->Manual($params['module'], $params['ifid'], $data,false);			
			if($service->isError())
			{
				$this->setError();
				$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
			}
			
					
		}
		
		if(count($delete))
		{
			/*
			// Tiến hành xóa IOIDLink
			$form = new Qss_Model_Form();
			$fromIOID = $params['ioid']?$params['ioid']:$model->getIOIDfromIFID($params['module'], $params['ifid']);
			foreach ($delete as $val) {
				$form->deleteIOIDLink($fromIOID, $val);
			}
			*/
			
			$delete =  array($stockObj=>$delete);
			$removeService =  $this->services->Form->Remove($params['module'], $params['ifid'],$delete );
			if($removeService->isError())
			{
				$this->setError();
				$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
			}	
		}
		
		
	}
}