<?php

class Qss_Bin_Bash_CreateReturn extends Qss_Lib_Bin 
{
	public function __doExecute()
	{
		$control = new Qss_Controller();
		
		$sql2 = sprintf('
			SELECT *
			FROM ODanhSachXuatKho AS dsxk
			WHERE dsxk.IFID_M506 = %1$d
		', $this->_params->IFID_M506);
		
		$dataSQL2 = $this->_db->fetchAll($sql2);
		
		$sql3 = sprintf('select *
				FROM OLoaiNhapKho
				WHERE Loai = \'TRALAI\'');
		
		$dataSQL3 = $this->_db->fetchOne($sql3);
		
		$insert = array();
		
		// Main Obj
		$insert['ONhapKho'][0]['LoaiNhapKho']    = (int) ($dataSQL3?$dataSQL3->IOID:0);
		$insert['ONhapKho'][0]['NgayChuyenHang'] = date('d-m-Y');
		$insert['ONhapKho'][0]['NgayChungTu']    = date('d-m-Y');
		$insert['ONhapKho'][0]['Kho']            = $this->_params->Kho;
		$insert['ONhapKho'][0]['PhieuXuatKho']   = (int)$this->_params->IOID;

		// Sub Obj
		$i = 0;
		foreach($dataSQL2 as $dat)
		{
			$insert['ODanhSachNhapKho'][$i]['MaSanPham'] = $dat->MaSP;
			$insert['ODanhSachNhapKho'][$i]['DonViTinh'] = $dat->DonViTinh;
			$insert['ODanhSachNhapKho'][$i]['SoLuong']   = $dat->SoLuong;
			$i++;
		}
		
		if(count((array)$insert))
		{
			$service = $this->services->Form->Manual('M402' ,0,$insert,false);
			if($service->isError())
			{
				$this->setError();
				$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
			}
			else
			{
				$service->setRedirect('/user/form/edit?ifid='.$service->getData().'&deptid=1');
			}
		}
		else
		{
			$this->setError();
			$this->setMessage('Danh sách xuất kho còn trống!');
		}
	}
}
?>
