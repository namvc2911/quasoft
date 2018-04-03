<?php

Class Qss_Service_Extra_Mrp_Requirement_Save extends  Qss_Lib_Mrp_Service
{
	public function __doExecute($params)
	{
		if($this->validate($params))
		{
			$this->save($params);
			if(!$this->isError())
			{
				$this->setMessage('Cập nhật thành công');
			}
		}
		else
		{
			$this->setError();
		}
	}
	
	private function save($params)
	{
		$insert = array();
                $common = new Qss_Model_Extra_Extra();
                
		$i = 0;
		
		// @todo: cap nhat tham chieu vao tham chieu cua doi tuong chinh
		foreach ($params['ItemCode'] as $itemCode)
		{
			$insert['OChiTietYeuCau'][$i]['MaSP'] = $itemCode;
                        $insert['OChiTietYeuCau'][$i]['DonViTinh'] = $params['ItemUOM'][$i];
			$insert['OChiTietYeuCau'][$i]['ThuocTinh'] = $params['Attribute'][$i];
			$insert['OChiTietYeuCau'][$i]['SoLuong'] = $params['ItemQty'][$i];
			$insert['OChiTietYeuCau'][$i]['NgayBatDau'] = $params['BeginDate'][$i]; 
			$insert['OChiTietYeuCau'][$i]['NgayKetThuc'] = $params['EndDate'][$i];
			$insert['OChiTietYeuCau'][$i]['ThamChieu'] = $params['Ref'][$i];
			$insert['OChiTietYeuCau'][$i]['ioid'] = $params['ToIOID'][$i];
			$insert['OChiTietYeuCau'][$i]['ioidlink'] = $params['IOID'][$i];
			$i++;
		}
		
		$service = $this->services->Form->Manual('M764' , $params['ifid'], $insert, false);
		if($service->isError())
		{
			$this->setError();
			$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
		}
                else
                {
                      // get detail lines to save ref for main line
                    $this->_updateReferenceForRequirement($params);
                }
	}
	
	private function validate($params)
	{
		$retval = true;
		
		
		// Err1: khong co dong nao
		if(!isset($params['ItemCode']))
		{
			$this->setError();
			$this->setMessage('Bạn phải chọn ít nhất một dòng!');
			$retval = false;
		}
		// Err2: ngay bat dau lon hon ngay ket thuc 
		return $retval;
	}
}