<?php
Class Qss_Service_Extra_Warehouse_Movement_Stockstatus_Save extends  Qss_Service_Abstract
{
	public function __doExecute($params)
	{
		if($this->validate($params))
		{
			$this->updateStockStatus($params);
			if(!$this->isError())
			{
				$this->setMessage('Cập nhật thành công.');
			}
		}
		else 
		{
			$this->setError();
		}
	}
	private function validate($params)
	{
		$return    = true;
		$model     = new Qss_Model_Extra_Warehouse();
		//$serialLot = unserialize(Qss_Lib_Extra::formatUnSerialize($params['serialLot']));
		$serialLot = unserialize($params['serialLot']);
		$i         = 0;
		$qty       = isset($params['qty'])?$params['qty']:array();
		$addQty    = array_sum($qty);
		$oldQty    = 0;
		$needQty   = $serialLot->itemQty;
		$updateQty = $addQty + $oldQty;
		
		if(!count($qty))
		{
			$this->setMessage('Không có giá trị để cập nhật.');
			$return = false;
		}
		elseif($updateQty > $needQty)
		{
			$this->setMessage('Tổng số lượng cập nhật lớn hơn số lượng sản phẩm chuyển kho.');
			$return = false;
		}
		elseif($updateQty  <= 0)
		{
			$this->setMessage('Tổng số lượng cập nhật nhỏ hơn bằng không.');
			$return = false;
		}
		

		
		foreach ($qty as $value) {
			if(!is_numeric($value) || $value < 0)
			{
				$this->setMessage('Số lượng trên dòng '.$params['ids'][$i].' không phải là số hoặc không lớn hơn không.');
				$return = false;
			}
			elseif($value > $params['lineQty'][$i])
			{
				$this->setMessage('Số lượng trên dòng '.$params['ids'][$i].' lớn hơn số lượng hiện có.');
				$return = false;
			}
			
			if($params['hasBin'] && $params['check'][$i] && isset($params['toBin'][$i]) && !$params['toBin'][$i])
			{
				$this->setMessage('Trên dòng '.$params['ids'][$i].' thiếu bin.');
				$return = false;
			}
			$i++;
		}
		
		/* 
		if($params['fromWarehouse'] == $params['toWarehouse'])
		{
			if(isset($params['zone']))
			{
				$index = 0;
				foreach ($params['zone'] as $from)
				{
					if($from = $params['toZone'][$index])
					{
						if(isset($params['bin'][$index]) && $params['bin'][$index] )
						{
							if($params['bin'][$index] == $params['toBin'][$index])
							{
								$this->setMessage('Bạn không thể chuyển đến cùng một bin trong cùng một zone trên một kho.');
								$return = false;
							}
						}
						else
						{
							$this->setMessage('Bạn không thể chuyển đến cùng một zone trong cùng một kho.');
							$return = false;
						}
					}
					$index++;
				}
			}
			else 
			{
				$this->setMessage('Bạn không thể thực hiện việc chuyển cùng kho khi kho này không quản lý theo zone.');
				$return = false;
			}
		}
		*/

		
		return $return;
	}
	
	private function updateStockStatus($params)
	{
		$model     = new Qss_Model_Extra_Warehouse();
		//$serialLot = unserialize(Qss_Lib_Extra::formatUnSerialize($params['serialLot']));
		$serialLot = unserialize($params['serialLot']);
		$i         = 0;
		$data      = array();
		$update    = array();
		$delete    = array();
		
		foreach ($params['qty'] as $value)
		{
			if( $params['check'][$i] && (($params['existsIOID'][$i] || $params['qty'][$i] > 0)))
			{

                                $data[$i]['MaSanPham']    = $serialLot->itemCode;
                                $data[$i]['DonViTinh']    = $serialLot->itemUOM;
                                $data[$i]['SoLo']         = isset($params['lot'][$i])?$params['lot'][$i]:'';
                                $data[$i]['SoSerial']     = isset($params['serial'][$i])?$params['serial'][$i]:'';
                                $data[$i]['NgayNhan']     = isset($params['receiveDate'][$i])?
                                                                $params['receiveDate'][$i]:'';
                                $data[$i]['NgaySX']       = isset($params['productDate'][$i])?
                                                                $params['productDate'][$i]:'';
                                $data[$i]['NgayHan']      = isset($params['expiryDate'][$i])?
                                                                $params['expiryDate'][$i]:'';
                                $data[$i]['MaThuocTinh']  = isset($params['attributes'])?$params['attributes']:'';
                                $data[$i]['SoLuong']      = $params['qty'][$i];
                                $data[$i]['KhoLayHang']   = $serialLot->warehouse;
                                $data[$i]['TuZone']       = isset($params['zone'][$i])?$params['zone'][$i]:'';
                                $data[$i]['TuBin']        = isset($params['bin'][$i])?$params['bin'][$i]:'';
                                $data[$i]['KhoNhanHang']  = $serialLot->toWarehouse;
                                $data[$i]['DenZone']      = isset($params['toZone'][$i])?$params['toZone'][$i]:'';
                                $data[$i]['DenBin']       = isset($params['toBin'][$i])?$params['toBin'][$i]:'';
                                if($params['existsIOID'][$i])
                                {
                                        $data[$i]['ioid']     = $params['existsIOID'][$i];
                                }
                                else
                                {
                                        $data[$i]['ioidlink'] = $params['ioid'];
                                }				
			}
			
			if($params['existsIOID'][$i] && !$params['check'][$i]) 
			{
				$delete[] = $params['existsIOID'][$i];
			}
			$i++;
		}
		if(count($data))
		{
			$update = array('OTrangThaiLuuTruCK'=>$data);
			//echo '<pre>'; print_r($update); die;
			
			$service = $this->services->Form->Manual($params['module'] ,$params['ifid'],$update,false);
			if($service->isError())
			{
				$this->setError();
				$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
			}
			
			
		}
		
		if(count($delete))
		{
			// Tiến hành xóa IOIDLink
			$form = new Qss_Model_Form();
			/*
			$fromIOID = $params['ioid'];
			foreach ($delete as $val) {
				$form->deleteIOIDLink($fromIOID, $val);
			}
			*/
			$delete =  array('OTrangThaiLuuTruCK'=>$delete);
			$removeService =  $this->services->Form->Remove($params['module'], $params['ifid'],$delete );
			if($removeService->isError())
			{
				$this->setError();
				$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
			}	
		}
		
	}
}