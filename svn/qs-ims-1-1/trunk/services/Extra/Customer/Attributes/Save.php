<?php
Class Qss_Service_Extra_Customer_Attributes_Save extends  Qss_Service_Abstract
{
	public function __doExecute($params)
	{
		if($this->validateData($params))
		{
			$qtyNew    	  = isset($params['qtyNew'])?$params['qtyNew']:array();
			//$serialLot    = unserialize(Qss_Lib_Extra::formatUnSerialize($params['serialLot']));
			$serialLot    = unserialize($params['serialLot']);
			
			if($serialLot->lot || $serialLot->serial)
			{
				if(count((array)$qtyNew))
				{
					if($serialLot->lot)
					{
						$this->updateLot($params);
					}
					$this->updateNewAttributesDetails($params);
				}
				
				$updateQty       = isset($params['qty'])?$params['qty']:array();
				if(count((array)$updateQty))
				{
					$this->updateAttr($params);
				}
			}
			else 
			{
				$this->updateNewAttributesDetails($params);
			}
			$this->setMessage('Cập nhật thành công !');
		}
		else
		{
			$this->setError();
		}
	}
	
	private function updateLot($params)
	{
		$lot      = isset($params['lotNew'])?$params['lotNew']:array();
		$serial	  = isset($params['serialNew'])?$params['serialNew']:array();
		$qty   	  = isset($params['qtyNew'])?$params['qtyNew']:array();
		//$itemCode = unserialize(Qss_Lib_Extra::formatUnSerialize($params['serialLot']))->itemCode;
		$itemCode = unserialize($params['serialLot'])->itemCode;
		
		if($lot)
		{
			$model    = new Qss_Model_Extra_Products();
                        $common   = new Qss_Model_Extra_Extra();
			$lotExist = array();
			
			foreach($common->getTable(array('*'), 'OLo', array('MaSanPham'=>$itemCode), array(), 'NO_LIMIT') as $val)
			{
				$lotExist[] = $val->SoLo;
			}
	
			/**
			 * Neu co serial hoac thuoc tinh thi can phai loai bo cac lo trung lap
			 */
			if($serial)
			{
				$lot = array_unique($lot);
			}
			/**
			 * Chi cap nhat cac lo co gia tri va chua ton tai
			 */
			$i    = 0;
			$data = array();

			foreach($lot as $val)
			{
				if($val && $qty[$i] && !in_array($val, $lotExist))
				{
					$data[] = array(
						'MaSanPham'=>$itemCode,
						'SoLo'=>$val
					);
				}
				$i++;
			}
			
			/**
			 * Neu co du lieu thi cap nhat vao bang lo
			 */
			if(count($data))
			{
				$data = array('OLo'=>$data);
				//echo '<pre>'; print_r($data); die;
				$service = $this->services->Form->Manual('M120',0,$data,false);
				if($service->isError())
				{
					$this->setError();
					$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
				}
			}
		}
	}
	
	/*
	 * Cập nhật vào bảng Lot & Serial với giá trị mới
	 */
	private function updateNewAttributesDetails($params)
	{
		$model 	   		 = new Qss_Model_Extra_Products();
		$IOIDExist 		 = array();
		$dataTmp   		 = array();
		$qty    	     = isset($params['qtyNew'])?$params['qtyNew']:array();
		$lines 		     = count((array)$qty);
		//$serialLot       = unserialize(Qss_Lib_Extra::formatUnSerialize($params['serialLot']));
		$serialLot       = unserialize($params['serialLot']);
		$lot       	     = isset($params['lotNew'])?$params['lotNew']:array();
		$serial 	  	 = isset($params['serialNew'])?$params['serialNew']:array();
		$attributes      = isset($params['attributeNew'])?$params['attributeNew']:'';
		$updateLotAndSeri= $model->getExistsIOIDFromAttrTable($params['ifid'], $serialLot->itemCode, $params['module'], $serialLot->warehouse, $attributes);
		
		foreach ($updateLotAndSeri as $val)
		{
			$IOIDExist[] = $val->IOID;
		}
		$delete = array();
		for($i=0; $i<$lines; $i++)
		{
			if(isset($IOIDExist[$i]) && $IOIDExist[$i] && $qty[$i]<=0)
			{
				$delete[] = $IOIDExist[$i];
			}
			// Lặp qua các dòng của bảng thuộc tính 
			if( isset($IOIDExist[$i]) || $qty[$i])
			{
				// nếu tồn tại ioid hoặc số lượng khác không 
				// Lấy mã sản phẩm chung
				$dataTmp[$i]['MaSanPham'] = $serialLot->itemCode;
				
				// Lấy lô theo dòng
				if($lot)
				{
					// nếu có serial
					$dataTmp[$i]['SoLo'] = $lot[$i];
				}
				
				// Update kho
				if($serialLot->warehouse)
				{
					$dataTmp[$i]['Kho'] = $serialLot->warehouse;
				}
				
				// Lấy serial theo dòng
				if($serial)
				{
					$dataTmp[$i]['SoSerial'] = $serial[$i];
				}
				
				// Lấy thuộc tính theo dòng
				if($attributes)
				{
					$dataTmp[$i]['MaThuocTinh'] = $attributes;
				}
				
				if($params['zoneNew'])
				{
					$dataTmp[$i]['Zone'] = $params['zoneNew'][$i];
				}
				
				if($params['binNew'])
				{
					$dataTmp[$i]['Bin'] = $params['binNew'][$i];
				}
				
				// Lấy số lượng trên mỗi dòng
				$dataTmp[$i]['SoLuong'] = $qty[$i];
				
				if(isset($IOIDExist[$i]))
				{
					$dataTmp[$i]['ioid'] = $IOIDExist[$i];
				}
				else 
				{
					$dataTmp[$i]['ioidlink'] = $params['ioid'];
				}
			}
		}
		
		
		if(count($dataTmp))
		{
			$data = array('OThuocTinhChiTiet'=>$dataTmp);
			//print_r($data); die;
			
			$service = $this->services->Form->Manual($params['module'] ,$params['ifid'],$data,false);
			if($service->isError())
			{
				$this->setError();
				$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
			}
		}
		
		if(count($delete))
		{
			// Tiến hành xóa IOIDLink
			/*
			$form = new Qss_Model_Form();
			$fromIOID = $params['ioid'];
			foreach ($delete as $val) {
				$form->deleteIOIDLink($fromIOID, $val);
			}
			*/
			$delete =  array('OThuocTinhChiTiet'=>$delete);
			$removeService =  $this->services->Form->Remove($params['module'] ,$params['ifid'],$delete );
			if($removeService->isError())
			{
				$this->setError();
				$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
			}	
		}
	}
	
	// Cập nhật với giá trị đã có
	private function updateAttr($params)
	{
		$qty 		= isset($params['qty'])?$params['qty']:array();
		$qtyLine 	= isset($params['qtyLine'])?$params['qtyLine']:array();
		$model 		= new Qss_Model_Extra_Products();
		//$serialLot  = unserialize(Qss_Lib_Extra::formatUnSerialize($params['serialLot']));
		$serialLot  = unserialize($params['serialLot']);
		$data 		= array();
		$delete 	= array();
		
		for($i=0;$i<count($qtyLine);$i++)
		{
			if(!$params['existsIOID'][$i] || $params['checkUpdate'][$i])
			{
				if(isset($qty[$i]) && (isset($IOIDExists->IOID) || (isset($params['qty'][$i]) && $params['qty'][$i]>0) ))
				{
					$data[$i]['MaSanPham']   = $serialLot->itemCode;
					$data[$i]['Kho'] 	     = $serialLot->warehouse;
					$data[$i]['SoLo'] 	     = isset($params['lotUpdate'][$i])?$params['lotUpdate'][$i]:'';
					$data[$i]['SoSerial']    = isset($params['serialUpdate'][$i])?$params['serialUpdate'][$i]:'';
					$data[$i]['MaThuocTinh'] = isset($params['attrUpdate'][$i])?$params['attrUpdate'][$i]:'';
					$data[$i]['Zone']        = isset($params['zoneUpdate'][$i])?$params['zoneUpdate'][$i]:'';
					$data[$i]['Bin'] 		 = isset($params['binUpdate'][$i])?$params['binUpdate'][$i]:'';
					$data[$i]['SoLuong'] 	 = $params['qty'][$i];
					if($params['existsIOID'][$i])
					{
						$data[$i]['ioid']  	 = $params['existsIOID'][$i];
					}
					else 
					{
						$data[$i]['ioidlink']  	 = $params['ioid'];
					}
				}
			}
			elseif($params['existsIOID'][$i] && !$params['checkUpdate'][$i]) 
			{
				$delete[] = $params['existsIOID'][$i];
			}
		}
		//print_r($data); die;

		if(count($data))
		{
			///------------------------- Cập nhật giá trị --------------------------------
			$data = array('OThuocTinhChiTiet'=>$data);
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
			
			$delete =  array('OThuocTinhChiTiet'=>$delete);
			$removeService =  $this->services->Form->Remove($params['module'], $params['ifid'],$delete );
			if($removeService->isError())
			{
				$this->setError();
				$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
			}	
		}
		
		
	}
	
	private function validateData($params)
	{
		$result 		= true;
		$model  		= new Qss_Model_Extra_Products();
		$breakCondition = true; // Ngắt báo lỗi
		//$serialLot      = unserialize(Qss_Lib_Extra::formatUnSerialize($params['serialLot']));
		$serialLot      = unserialize($params['serialLot']);
		$itemUOM        = $serialLot->itemUOM;
		/** 
		 * Kiểm tra dữ liệu
		 */
		$updateQty = 0;
		
		if(isset($params['qty']))
		{
			foreach ($params['qty'] as $val)
			{
				$updateQty += ($val)?$val:0;
			}
		}
		
		$newQty 		= isset($params['newQty'])?$params['newQty']:0;
		$itemQty 		= $serialLot->itemQty;

		if( ($updateQty + $newQty) > $itemQty )
		{
			$this->setMessage('Tổng số lượng cập nhật và đánh mới lớn hơn số lượng sản phẩm nhận lại !');
			$result = false;
		}
		else 
		{
			/**
			 * Giái quyết form thêm mới
			 */
			if($newQty>0)
			{
				$newQtyArray = isset($params['qtyNew'])?$params['qtyNew']:array();
				if(sum($params['qtyNew']) > $itemQty)
				{
					$this->setMessage('Số lượng đánh mới thực tế lớn hơn số lượng sản phẩm !');
					$result = false;
				}

						
				/**
				 * Kiểm tra số lượng sản phẩm !
				 */
				
				/** Kiểm tra số dương trên mỗi dòng */
				for($i = 0; $i < count((array)$params['qtyNew']); $i++)
				{
					if(!is_numeric($params['qtyNew'][$i])
				    	|| ($params['qtyNew'][$i] < 0) 
				   		 )
					{
						$this->setMessage('Dòng '.($i+1).' có số lượng không phải là số dương lớn hơn 0!');
						$result         = false;
						$breakCondition = false;
					}
				}
				
				/** Kiểm tra tổng số lượng lớn hơn hay không ? */
				$total = array_sum($params['qtyNew'])?array_sum($params['qtyNew']):0;
				
				if($total > $itemQty)// Nếu lơn hơn thông báo lỗi
				{
					$this->setMessage('Tổng số lượng được chọn lớn hơn tổng số lượng sản phẩm!');
					$result = false;
					$breakCondition = false;
				}
		
				
				/** Kiểm tra lô */
				if(isset($params['lotNew']))
				{
					$countLot   	= count($params['lotNew']);
					$uniqueLot 		= array_unique($params['lotNew']);
					$countUniqueLot = count($uniqueLot);
					for($j = 0; $j < $countLot; $j++)
					{
						if(!$params['lotNew'][$j])
						{
							$result = false;
							$breakCondition = false;
							$this->setMessage('Có lô để trống tại dòng '.($j+1).', đề nghị kiểm tra lại !');					
						}
					}
		
					if(!isset($params['serialNew']) && $countUniqueLot < $countLot)
					{
						for ($k = 0; $k < $countLot; $k++) 
						{
								if(!isset($uniqueLot[$k]))
								{
									$mistake = array_search($params['lotNew'][$k], $params['lotNew']) + 1;
									$this->setMessage('Có lô trùng lặp tại dòng '.($k+1).', trùng lặp với giá trị "'
												.$params['lotNew'][$k].'" của dòng '.$mistake.' , đề nghị kiểm tra lại !');
								}
						}
						$result = false;
						$breakCondition = false;
					}
				}
				
				/** Kiểm tra serial */
				if(isset($params['serial']))
				{
					$countSerial   	   = count($params['serialNew']);
					$uniqueSerial 	   = array_unique($params['serialNew']);
					$countUniqueSerial = count($uniqueSerial);
					foreach($params['serial'] as $val)
					{
						if(!$val)
						{
							$result = false;
							$breakCondition = false;
							$this->setMessage('Có serial để trống, đề nghị kiểm tra lại !');
						}
					}
					
					if(($countUniqueSerial < $countSerial) )
					{
						for ($k = 0; $k < $countSerial; $k++) {
							if(!isset($uniqueSerial[$k]))
							{
								$mistake = array_search($params['serialNew'][$k], $params['serialNew']) + 1;
								$this->setMessage('Có serial trùng lặp tại dòng '.($k+1).', trùng lặp với giá trị "'
											.$params['serial'][$k].'" của dòng '.$mistake.' , đề nghị kiểm tra lại !');
							}
						}
						$result = false;
						$breakCondition = false;
					}
					
				}
			
		
				
				/**
				 * Kiểm tra lỗi nhưng vẫn cho cập nhật này
				 * 
				 */
				if($breakCondition && $total < $itemQty )	// Nếu nhỏ hơn cảnh báo
				{
					$itemUOM = strtolower($itemUOM);
					$div     = (($itemQty - $total)<$itemQty)?($itemQty - $total):$itemQty;
					$this->setMessage('Tổng số lượng được cập nhật : '.$total.' '.$itemUOM);
					$this->setMessage('Số lượng chưa cập nhật  : '.$div.'/'.$itemQty.' '.$itemUOM);
				} 
			}
			/** */
			
			/**
			 * Giải quyết form cập nhật lại
			 */
			if($updateQty>0)
			{
				$updateQtyArray = isset($params['qty'])?$params['qty']:array();
				if(sum($updateQtyArray) > $itemQty)
				{
					$this->setMessage('Số lượng cập nhật thực tế lớn hơn số lượng sản phẩm !');
					$result = false;
				}
								
				//$serialLot      = unserialize(Qss_Lib_Extra::formatUnSerialize($params['serialLot']));
				$serialLot      = unserialize($params['serialLot']);
				$qtyLine 		= isset($params['qtyLine'])?$params['qtyLine']:array();
				$qty 			= isset($params['qty'])?$params['qty']:arraY();
				$exists 		= $model->existsMovement($params['ifid'], $serialLot->itemCode
													, $serialLot->warehouse, $params['module']);
				$existQty 		= count((array)$exists);
				$qtyHas 		= array();
				$qtyDiv 		= array();
				
				for($i=0;$i<count($qtyLine);$i++)
				{
					// Kiểm tra có phải là số hợp lệ
					if(isset($qty[$i]) && (!is_numeric($qty[$i]) || $qty[$i]<=0 ))
					{
						$this->setMessage('Số lượng lấy ra trên dòng '.$params['sttUpdate'][$i].' không phải là số dương lớn hơn 0!');
						$return = false;
					}
					
					if(isset($qty[$i]) && $serialLot->serial && $qty[$i]>1)
					{
						$this->setMessage('Số lượng lấy ra trên dòng '.$params['sttUpdate'][$i].' phải bằng 1 do một serial chỉ ưng với một sản phẩm!');
						$return = false;
					}
				}
			}
		}
		
		/**Kết thúc kiểm tra dữ liệu */
		return $result;
	}
	

}