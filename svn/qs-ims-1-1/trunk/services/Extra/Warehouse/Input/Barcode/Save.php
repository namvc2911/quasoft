<?php

class Qss_Service_Extra_Warehouse_Input_Barcode_Save extends Qss_Service_Abstract
{
	
	public function __doExecute ($params)
	{		
		// @todo: chua them thuoc tinh tren tung dong
		if($this->validate($params))
		{
			$insert = array();
			$i = 0;
			
			$insert['ONhapKho'][0]['Kho'] = $params['warehouse'];
			$insert['ONhapKho'][0]['MaNCC'] = $params['partner'];
			$insert['ONhapKho'][0]['LoaiChungTu'] = $params['docType'];
			$insert['ONhapKho'][0]['NgayChungTu'] = $params['docDate'];
			$insert['ONhapKho'][0]['SoChungTu'] = $params['docNo'];
			$insert['ONhapKho'][0]['NgayChuyenHang'] = $params['deliveryDate'];
			$insert['ONhapKho'][0]['DonHangThamChieu'] = $params['ref'];
			$insert['ONhapKho'][0]['MoTa'] = $params['des'];
			
			foreach ($params['ItemQty'] as $qty)
			{
				//ThuocTinh
				$insert['ODanhSachNhapKho'][$i]['MaSanPham'] = $params['ItemCode'][$i];
				$insert['ODanhSachNhapKho'][$i]['SoLuong'] = $params['ItemQty'][$i];
                                $insert['ODanhSachNhapKho'][$i]['DonViTinh'] = $params['ItemUOM'][$i];
				$insert['ODanhSachNhapKho'][$i]['DonGia'] = $params['ItemPrice'][$i];
				$insert['ODanhSachNhapKho'][$i]['MoTa'] = $params['ItemDes'][$i];
				$insert['ODanhSachNhapKho'][$i]['DongDonHang'] = $params['ItemRef'][$i];
				$i++;
			}
			
			$service = $this->services->Form->Manual('M402' , 0, $insert, false);
			if($service->isError())
			{
				$this->setError();
				$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
			}
			
			if(!$this->isError())
			{
				$this->setMessage('Cập nhật thành công!');
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
		return $return;
	}
}
?>