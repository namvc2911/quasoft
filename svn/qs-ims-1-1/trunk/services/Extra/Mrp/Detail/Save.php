<?php
Class Qss_Service_Extra_Mrp_Detail_Save extends  Qss_Service_Abstract
{
	public function __doExecute($params)
	{
		
		if(!$this->validatex()) // Kiểm tra lỗi
		{
			$this->setError();
		}
		else // Không có lỗi
		{
			$purIndex = 0;
			$proIndex = 0;
			$insert = array();
			
			//Ngay MaSP TenSP ThuocTinh  	DonViTinh 	SoLuong KhauTruKho
			// DayChuyen  	Ca  	CongDoan  	DonViThucHien  	Ngay MaSP TenSP DonViTinhThuocTinh ThietKe SoLuong
			
			if(isset($params['purNgay']))
			{
				foreach ($params['purNgay'] as $ngay)
				{
                                    if($params['purSoLuong'][$purIndex] || $params['purKhauTruKho'][$purIndex])
                                    {
					$insert['OKeHoachMuaHang'][$purIndex]['Ngay'] = $params['purNgay'][$purIndex];
					$insert['OKeHoachMuaHang'][$purIndex]['MaSP'] = $params['purMaSP'][$purIndex];
                                        $insert['OKeHoachMuaHang'][$purIndex]['DonViTinh'] = $params['purDonViTinh'][$purIndex];
					$insert['OKeHoachMuaHang'][$purIndex]['ThuocTinh'] = $params['purThuocTinh'][$purIndex];
					$insert['OKeHoachMuaHang'][$purIndex]['SoLuong'] = $params['purSoLuong'][$purIndex];
					$insert['OKeHoachMuaHang'][$purIndex]['KhauTruKho'] = $params['purKhauTruKho'][$purIndex];
                                    }
					$purIndex++;
				}
			}
			
			if(isset($params['proDayChuyen']))
			{
				foreach ($params['proDayChuyen'] as $dayChuyen)
				{
					$insert['OKeHoachSanXuat'][$proIndex]['DayChuyen'] = $params['proDayChuyen'][$proIndex];
//					$insert['OKeHoachSanXuat'][$proIndex]['Ca'] = $params['proCa'][$proIndex];
//					$insert['OKeHoachSanXuat'][$proIndex]['CongDoan'] = $params['proCongDoan'][$proIndex];
//					$insert['OKeHoachSanXuat'][$proIndex]['DonViThucHien'] = $params['proDonViThucHien'][$proIndex];
					$insert['OKeHoachSanXuat'][$proIndex]['TuNgay'] = $params['proStart'][$proIndex];
                                        $insert['OKeHoachSanXuat'][$proIndex]['DenNgay'] = $params['proEnd'][$proIndex];
                                        $insert['OKeHoachSanXuat'][$proIndex]['ThoiGian'] = $params['proTime'][$proIndex];
					$insert['OKeHoachSanXuat'][$proIndex]['MaSP'] = $params['proMaSP'][$proIndex];
                                        $insert['OKeHoachSanXuat'][$proIndex]['DonViTinh'] = $params['proDonViTinh'][$proIndex];
					$insert['OKeHoachSanXuat'][$proIndex]['ThietKe'] = $params['proThietKe'][$proIndex];
					$insert['OKeHoachSanXuat'][$proIndex]['SoLuong'] = $params['proSoLuong'][$proIndex];
					$proIndex++;
				}
			}
			
			//echo '<pre>'; print_r($insert['OKeHoachSanXuat']); die;
			if(count($insert))
			{
				$service = $this->services->Form->Manual($params['module'] , $params['ifid'], $insert, false);
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
			

		}// Kết thúc kiểm tra lỗi
	}
	
	private function validatex()
	{
		return true;
	}
}