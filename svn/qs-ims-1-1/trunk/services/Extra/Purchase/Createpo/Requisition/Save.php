<?php

class Qss_Service_Extra_Purchase_Createpo_Requisition_Save extends Qss_Service_Abstract
{
	
	public function __doExecute ($params)
	{		
		if($this->validate($params))
		{
            $user   = Qss_Register::get('userinfo');
			$model  = new Qss_Model_Extra_Warehouse();
			$extra  = new Qss_Model_Extra_Extra();
			$insert = array();
			$keeper = array(); // Cộng dồn giá trị
			//$older  = array(); // Giá trị cũ
			//$removeOlder = array(); // Loại bỏ giá trị cũ đã cộng
			$ai     = 0; // Chi so mang insert
			$line   = isset($params['itemCode'])?$params['itemCode']:array();
			$lastestLineNo = isset($params['lineNo'])?max($params['lineNo']):0;
			//$order  = $extra->getTable(array('*'), 'ODSDonMuaHang',array('IFID_M401'=>$params['ifid']),
			//						    array(),'NO_LIMIT'
			//							);
										
			//foreach ($order as $item)
			//{
			//	$code = $item->Ref_MaSP.'-'.$item->Ref_ThuocTinh;
			//	$older[$code] = $item->SoLuong;
			//}							
			
			// Cong don neu cung san pham
			
			$insert['ODonMuaHang'][0]['SoDonHang'] = $params['documentNo'];
			$insert['ODonMuaHang'][0]['MaNCC'] = $params['partner'];
			$insert['ODonMuaHang'][0]['NgayDatHang'] = $params['orderDate'];
			$insert['ODonMuaHang'][0]['NgayYCNH'] = $params['requiredDate'];
			
			foreach ($line as $itemCode)
			{
				$code  = $params['refItemCode'][$ai].'-'.$params['refAttributeCode'][$ai];
				$bonus = 0;
				
				if(key_exists($code, $keeper))
				{
					$insert['ODSDonMuaHang'][$keeper[$code]]['SoLuong'] += $params['qty'][$ai];
				}
				else 
				{
					$newLineNo = Qss_Lib_Extra::getLineNo($lastestLineNo);
					$insert['ODSDonMuaHang'][$ai]['MaSP']        = $itemCode;
                                        $insert['ODSDonMuaHang'][$ai]['DonViTinh']   = $params['itemUOM'][$ai];
					$insert['ODSDonMuaHang'][$ai]['SoLuong']     = $params['qty'][$ai];
					$insert['ODSDonMuaHang'][$ai]['DongDonHang'] = $newLineNo;
					$insert['ODSDonMuaHang'][$ai]['ThuocTinh']   = $params['attributeCode'][$ai];
					$insert['ODSDonMuaHang'][$ai]['DonGia']      = $params['price'][$ai]/1000;
					$lastestLineNo = $newLineNo;
					$keeper[$code] = $ai;
				}
				$insert['ODSDonMuaHang'][$ai]['ioidlink']    = $params['stackLine'][$ai];
				$ai++;
			}
			
			//echo '<pre>'; print_r($insert);die;
			
			if(count($insert))
			{
				$service = $this->services->Form->Manual('M401' ,0,$insert,false);
				if($service->isError())
				{
					$this->setError();
					$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
				}
			}
			
			if(!$this->isError())
			{
				$this->setMessage($this->_translate(1));
				// $extra->setStatus($params['ifidArr'], 2);

                if(count($params['ifidArr']))
                {
                    foreach ($params['ifidArr'] as $ifid)
                    {
                        if(!$this->isError())
                        {
                            $form = new Qss_Model_Form();
                            $form->initData($ifid, $user->user_dept_id);
                            $serviceChange = $this->services->Form->Request($form, 2, $user, '');

                            if($serviceChange->isError())
                            {
                                $this->setError();
                                $this->setMessage($serviceChange->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                            }
                        }
                    }
                }

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
		$checkOrderNoDuplicate = $extra->getTable(array(1), 'ODonMuaHang' , array('SoDonHang'=>$params['documentNo']) , array(), 1, 1);
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