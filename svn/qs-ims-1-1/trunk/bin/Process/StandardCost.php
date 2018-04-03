<?php
class Qss_Bin_Process_StandardCost extends Qss_Lib_Bin
{
	public function __doExecute()
	{
		$arrProducts = array();
		$arrQuantity = array();
		$code = $this->_params->Code;
		//echo $code;die;	
		//Lay gia thanh nguyen vat lieu
		$sql = sprintf('select ct.*,cd.*,
				sum(case when btt.SoLuong is not null or btt.SoLuong !=0 then nvl.SoLuong*btt.SoLuong else nvl.SoLuong*uom.HeSoQuyDoi end * (select GiaThanh from OGiaoDichKho where ct.MaSanPham = MaSanPham and ct.Ref_ThuocTinh = Ref_ThuocTinh order by IOID limit 1)) as GiaThanh, 
				sum(case when btt.SoLuong is not null or btt.SoLuong !=0 then nvl.SoLuong*btt.SoLuong else nvl.SoLuong*uom.HeSoQuyDoi end * sp.GiaMua) as GiaVon
				from OCauThanhSanPham as ct
				inner join OCongDoanBOM as cd on cd.IFID_M114 = ct.IFID_M114
				inner join OThanhPhanSanPham as nvl on nvl.IFID_M114 = ct.IFID_M114 and cd.Ten = nvl.CongDoan
				inner join OSanPham as sp on sp.IOID = nvl.Ref_MaThanhPhan
				inner join ODonViTinhSP as uom on uom.IFID_M113 = sp.IFID_M113 and ct.Ref_DonViTinh=uom.IOID
				left join OBangThuocTinh as btt on btt.IOID = ifnull(ct.Ref_ThuocTinh,0)
				inner join OChiTietYeuCau as ctyc on ctyc.Ref_MaSP = ct.Ref_MaSanPham and ifnull(ctyc.Ref_ThuocTinh,0) = ifnull(ct.Ref_ThuocTinh,0)  and ct.Ref_DonViTinh = ctyc.Ref_DonViTinh
				inner join OYeuCauSanXuat as yc on yc.IFID_M764 = ctyc.IFID_M764
				where yc.SoYeuCau = %1$s
				group by ct.IOID, cd.IOID',$this->_db->quote($code));
		$dataSQL = $this->_db->fetchAll($sql);
		foreach($dataSQL as $item)
		{
			$arrProducts[$item->MaSanPham][$item->DonViTinh][$item->ThuocTinh][$item->Ten]['NVL'] = ($item->GiaThanh?$item->GiaThanh:$item->GiaVon)/1000;
			$arrQuantity[$item->MaSanPham][$item->DonViTinh][$item->ThuocTinh] = $item->SoLuong;
		}
		//Tinh nhan cong
		$sql = sprintf('select ct.*,cd.*,sum(nc.SoLuong * nl.ChiPhi * nc.HeSo * case when dvt.Ma = \'H\' then cd.SoGio else ct.SoLuong end) as ChiPhi
				from OCauThanhSanPham as ct
				inner join OCongDoanBOM as cd on cd.IFID_M114 = ct.IFID_M114
				inner join OChiPhiNhanCong as nc on nc.IFID_M114 = ct.IFID_M114 and cd.Ten = nc.CongDoan
				left join OCongViecBaoTri as cvbt on cvbt.IOID = nc.Ref_CongViec 
				left join ONhomLuong as nl on nl.IOID = cvbt.Ref_NhomLuong
				inner join ODVTChiPhi as dvt on dvt.IOID = nl.Ref_DVTChiPhi
				inner join OChiTietYeuCau as ctyc on ctyc.Ref_MaSP = ct.Ref_MaSanPham and ifnull(ctyc.Ref_ThuocTinh,0) = ifnull(ct.Ref_ThuocTinh,0) and ct.Ref_DonViTinh = ctyc.Ref_DonViTinh
				inner join OYeuCauSanXuat as yc on yc.IFID_M764 = ctyc.IFID_M764
				where yc.SoYeuCau = %1$s
				group by ct.IOID, cd.IOID',$this->_db->quote($code));
		$dataSQL = $this->_db->fetchAll($sql);
		foreach($dataSQL as $item)
		{
			$arrProducts[$item->MaSanPham][$item->DonViTinh][$item->ThuocTinh][$item->Ten]['NC'] = ($item->ChiPhi)/1000;
			$arrQuantity[$item->MaSanPham][$item->DonViTinh][$item->ThuocTinh] = $item->SoLuong;
		}
		//Tinh may moc
		$sql = sprintf('select ct.*,cd.*,sum(tb.ChiPhi * case when dvt.Ma = \'H\' then cd.SoGio else ct.SoLuong end) as ChiPhi
				from OCauThanhSanPham as ct
				inner join OCongDoanBOM as cd on cd.IFID_M114 = ct.IFID_M114
				inner join OChiPhiMayMoc as mm on mm.IFID_M114 = ct.IFID_M114 and cd.Ten = mm.CongDoan
				inner join ODanhSachThietBi as tb on tb.IOID = mm.Ref_MaThietBi 
				inner join ODVTChiPhi as dvt on dvt.IOID = tb.Ref_DVTChiPhi
				inner join OChiTietYeuCau as ctyc on ctyc.Ref_MaSP = ct.Ref_MaSanPham and ifnull(ctyc.Ref_ThuocTinh,0) = ifnull(ct.Ref_ThuocTinh,0)  and ct.Ref_DonViTinh = ctyc.Ref_DonViTinh
				inner join OYeuCauSanXuat as yc on yc.IFID_M764 = ctyc.IFID_M764
				where yc.SoYeuCau = %1$s
				group by ct.IOID, cd.IOID',$this->_db->quote($code));
		$dataSQL = $this->_db->fetchAll($sql);
		foreach($dataSQL as $item)
		{
			$arrProducts[$item->MaSanPham][$item->DonViTinh][$item->ThuocTinh][$item->Ten]['MM'] = ($item->ChiPhi)/1000;
			$arrQuantity[$item->MaSanPham][$item->DonViTinh][$item->ThuocTinh] = $item->SoLuong;
		}
		//Chi phi gian tiep
		$sql = sprintf('select ct.*,cd.*,sum(tg.ChiPhi * case when dvt.Ma = \'H\' then cd.SoGio else ct.SoLuong end) as ChiPhi
				from OCauThanhSanPham as ct
				inner join OCongDoanBOM as cd on cd.IFID_M114 = ct.IFID_M114
				inner join OChiPhiGianTiep as gt on gt.IFID_M114 = ct.IFID_M114 and cd.Ten = gt.CongDoan
				inner join OChiPhiKhac as tg on tg.IOID = gt.Ref_TenChiPhi 
				inner join ODVTChiPhi as dvt on dvt.IOID = tg.Ref_DVTChiPhi
				inner join OChiTietYeuCau as ctyc on ctyc.Ref_MaSP = ct.Ref_MaSanPham and ifnull(ctyc.Ref_ThuocTinh,0) = ifnull(ct.Ref_ThuocTinh,0)  and ct.Ref_DonViTinh = ctyc.Ref_DonViTinh
				inner join OYeuCauSanXuat as yc on yc.IFID_M764 = ctyc.IFID_M764
				where yc.SoYeuCau = %1$s
				group by ct.IOID, cd.IOID',$this->_db->quote($code));
		$dataSQL = $this->_db->fetchAll($sql);
		foreach($dataSQL as $item)
		{
			$arrProducts[$item->MaSanPham][$item->DonViTinh][$item->ThuocTinh][$item->Ten]['GT'] = ($item->ChiPhi)/1000;
			$arrQuantity[$item->MaSanPham][$item->DonViTinh][$item->ThuocTinh] = $item->SoLuong;
		}
		$arrLines = array();
		foreach($arrProducts as $masp=>$val1)
		{
			foreach ($val1 as $donvitinh=>$val2)
			{
				foreach ($val2 as $thuoctinh=>$val3)
				{
					foreach($val3 as $congdoan=>$val4)
					{
						$arrLines[] = array('MaSanPham'=>$masp,
										'ThuocTinh'=>$thuoctinh,
										'DonViTinh'=>$donvitinh,
										'CPGianTiep'=>(int) @$val4['GT'],
										'CongDoan'=>$congdoan,
										'SoLuong'=>$arrQuantity[$masp][$donvitinh][$thuoctinh],
										'CPNVL'=>(int) @$val4['NVL'],
										'CPNhanCong'=>(int) @$val4['NC'],
										'CPMayMoc'=>(int) @$val4['MM'],
										'GiaThanh'=>(int) @$val4['GT']+(int) @$val4['NVL']+(int) @$val4['NC']+(int) @$val4['MM'],
										'GiaThanhDonVi'=>((int) @$val4['GT']+(int) @$val4['NVL']+(int) @$val4['NC']+(int) @$val4['MM'])/$arrQuantity[$masp][$donvitinh][$thuoctinh]
										);
					}
				}
			}
		}
		$service = $this->services->Form->Manual('M761',$this->_form->i_IFID ,array('OGiaThanhSanXuat'=>$arrLines),false);
		if($service->isError())
		{
			$this->setError();
			$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
		}
	}
	
	
}
?>