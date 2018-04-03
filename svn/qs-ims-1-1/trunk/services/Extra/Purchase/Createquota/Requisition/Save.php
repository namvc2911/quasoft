<?php

class Qss_Service_Extra_Purchase_Createquota_Requisition_Save extends Qss_Service_Abstract
{
	
	public function __doExecute ($params)
	{		
		if($this->validate($params))
		{
			$model  = new Qss_Model_Extra_Warehouse();
			$extra  = new Qss_Model_Extra_Extra();
			$insert = array();
			$keeper = array(); // Cộng dồn giá trị
			//$older  = array(); // Giá trị cũ
			//$removeOlder = array(); // Loại bỏ giá trị cũ đã cộng
			$ai     = 0; // Chi so mang insert
			$line   = isset($params['itemCode'])?$params['itemCode']:array();
			$lastestLineNo = isset($params['lineNo'])?max($params['lineNo']):0;
			//$order  = $extra->getTable(array('*'), 'ODSDonMuaHang', 
			//							array('IFID_M401'=>$params['ifid']), array(),
			//						   'NO_LIMIT');
										
			//foreach ($order as $item)
			//{
			//	$code = $item->Ref_MaSP.'-'.$item->Ref_ThuocTinh;
			//	$older[$code] = $item->SoLuong;
			//}							
			
			// Cong don neu cung san pham
			
			$insert['OBaoGiaMuaHang'][0]['SoChungTu']  = $params['documentNo'];
			$insert['OBaoGiaMuaHang'][0]['MaNCC']      = $params['partner'];
			$insert['OBaoGiaMuaHang'][0]['NgayYeuCau'] = $params['orderDate'];
			$insert['OBaoGiaMuaHang'][0]['NgayBaoGia'] = $params['requiredDate'];
			
			foreach ($line as $itemCode)
			{
				$code  = $params['refItemCode'][$ai].'-'.$params['refAttributeCode'][$ai];
				$bonus = 0;
				
				if(key_exists($code, $keeper))
				{
					$insert['ODSBGMuaHang'][$keeper[$code]]['SoLuong'] += $params['qty'][$ai];
				}
				else 
				{
					$newLineNo = Qss_Lib_Extra::getLineNo($lastestLineNo);
					$insert['ODSBGMuaHang'][$ai]['MaSP']        = $itemCode;
                                        $insert['ODSBGMuaHang'][$ai]['DonViTinh']   = $params['itemUOM'][$ai];
					$insert['ODSBGMuaHang'][$ai]['SoLuong']     = $params['qty'][$ai];
					$insert['ODSBGMuaHang'][$ai]['DongDonHang'] = $newLineNo;
					$insert['ODSBGMuaHang'][$ai]['ThuocTinh']   = $params['attributeCode'][$ai];
					$insert['ODSBGMuaHang'][$ai]['NhomThue']    = $params['tax'][$ai];
					$insert['ODSBGMuaHang'][$ai]['DonGia']      = $params['price'][$ai]/1000;
					$lastestLineNo = $newLineNo;
					$keeper[$code] = $ai;
				}
				$insert['ODSBGMuaHang'][$ai]['ioidlink']    = $params['stackLine'][$ai];
				$ai++;
			}
			
			//echo '<pre>'; print_r($insert);die;
			
			if(count($insert))
			{
				$service = $this->services->Form->Manual('M406' ,0,$insert,false);
				if($service->isError())
				{
					$this->setError();
					$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
				}
			}
			
			if(!$this->isError())
			{
				$this->setMessage($this->_translate(1));
			}
		}
		else 
		{
			$this->setError();
		}
	}

	private function validate($params)
	{
		$return = true;
		$extra  = new Qss_Model_Extra_Extra();
		// So don hang da ton tai
		$checkOrderNoDuplicate = $extra->getTable(array(1), 'OBaoGiaMuaHang', array('SoChungTu'=>$params['documentNo']), array(), 1, 1);
		if($checkOrderNoDuplicate)
		{
			$this->setMessage($this->_translate(2));
		}
		
		/*
		// ngay can co nho hon ngay nhan hang, warning
		$line = isset($params['requiredReceiveDate'])?$params['requiredReceiveDate']:array();
		$ai   = 0;
		
		if($params['requiredDate'])
		{
			foreach ($line as $date)
			{
				if(Qss_Lib_Date::compareTwoDate($date, $params['requiredDate']) == -1)
				{
					$date = Qss_Lib_Date::mysqltodisplay($date);
					$this->setMessage("{$this->_translate(3)} \"{$params['itemCode'][$ai]}\" {$this->_translate(4)} {$params['qty'][$ai]} {$params['itemUOM'][$ai]} {$this->_translate(5)} {$date}");
				}
				$ai++;
			}
		}
		*/
		return $return;
	}
}
?>