<?php

class Qss_Service_Extra_Warehouse_Output_Barcode_Save extends Qss_Service_Abstract
{
	
	public function __doExecute ($params)
	{		
		// @todo: chua them thuoc tinh tren tung dong
		if($this->validate($params))
		{
			$insert = array();
			$i = 0;
			
			$insert['OXuatKho'][0]['Kho'] = $params['warehouse'];
			$insert['OXuatKho'][0]['MaKH'] = $params['partner'];
			$insert['OXuatKho'][0]['LoaiChungTu'] = $params['docType'];
			$insert['OXuatKho'][0]['NgayChungTu'] = $params['docDate'];
			$insert['OXuatKho'][0]['SoChungTu'] = $params['docNo'];
			$insert['OXuatKho'][0]['NgayChuyenHang'] = $params['deliveryDate'];
			$insert['OXuatKho'][0]['DonHangThamChieu'] = $params['ref'];
			$insert['OXuatKho'][0]['MoTa'] = $params['des'];
			
			foreach ($params['ItemQty'] as $qty)
			{
				//ThuocTinh
				$insert['ODanhSachXuatKho'][$i]['MaSP'] = $params['ItemCode'][$i];
                                $insert['ODanhSachXuatKho'][$i]['DonViTinh'] = $params['ItemUOM'][$i];
				$insert['ODanhSachXuatKho'][$i]['SoLuong'] = $params['ItemQty'][$i];
				$insert['ODanhSachXuatKho'][$i]['DonGia'] = $params['ItemPrice'][$i];
				$insert['ODanhSachXuatKho'][$i]['MoTa'] = $params['ItemDes'][$i];
				$insert['ODanhSachXuatKho'][$i]['DongDonHang'] = $params['ItemRef'][$i];
				$i++;
			}
			
			$service = $this->services->Form->Manual('M506' , 0, $insert, false);
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