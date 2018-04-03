<?php

class Qss_Bin_Process_ProductCost extends Qss_Lib_Bin
{

        public function __doExecute()
        {
                $arrProducts = array();
                $arrQuantity = array();
                $code = $this->_params->Code;
                //echo $code;die;	
                //Lay gia thanh nguyen vat lieu
                $sql = sprintf('select ct.*,
				sum(case when btt.SoLuong is not null or btt.SoLuong !=0 then nvl.SoLuong*btt.SoLuong else nvl.SoLuong*uom.HeSoQuyDoi end * (select GiaThanh from OGiaoDichKho where ct.MaSP = MaSanPham and ct.Ref_ThuocTinh = Ref_ThuocTinh order by IOID limit 1)) as GiaThanh, 
				sum(case when btt.SoLuong is not null or btt.SoLuong !=0 then nvl.SoLuong*btt.SoLuong else nvl.SoLuong*uom.HeSoQuyDoi end * sp.GiaMua) as GiaVon
				from OPhieuGiaoViec as ct
				left join ONVLDauVao as nvl on nvl.IFID_M712= ct.IFID_M712
				left join OSanPham as sp on sp.IOID = nvl.Ref_MaSP
				left join ODonViTinhSP as uom on uom.IFID_M113 = sp.IFID_M113 and ct.Ref_DonViTinh=uom.IOID
				left join OBangThuocTinh as btt on btt.IOID = ifnull(ct.Ref_ThuocTinh,0)
				inner join OChiTietYeuCau as ctyc on ctyc.MaSP = ct.MaSP and ifnull(ctyc.ThuocTinh,0) = ifnull(ct.ThuocTinh,0) and ct.Ref_DonViTinh = ctyc.Ref_DonViTinh
				inner join OYeuCauSanXuat as yc on yc.IFID_M764 = ctyc.IFID_M764
				where yc.SoYeuCau = %1$s and ct.Ngay between yc.TuNgay and ifnull(yc.DenNgay,\'9999-01-01\')
				group by ct.Ref_MaSP,ct.Ref_ThuocTinh,ct.Ref_CongDoan', $this->_db->quote($code));
                
                $dataSQL = $this->_db->fetchAll($sql);
                foreach ($dataSQL as $item)
                {
                        $arrProducts[$item->MaSP][$item->DonViTinh][$item->ThuocTinh][$item->CongDoan]['NVL'] = ($item->GiaThanh ? $item->GiaThanh : $item->GiaVon) / 1000;
                        $arrQuantity[$item->MaSP][$item->DonViTinh][$item->ThuocTinh] = $item->SoLuong;
                }
                //Tinh nhan cong
                $sql = sprintf('select ct.*,
				sum(nc.SoLuong * nl.ChiPhi * nc.HeSo * case when dvt.Ma = \'H\' then time_to_sec(timediff(ct.GioKT,ct.GioBD))/3600 else ct.SoLuongThucHien end) as ChiPhi
				from OPhieuGiaoViec as ct
				left join OTKCPNhanCong as nc on nc.IFID_M712 = ct.IFID_M712 
				left join ONhomLuong as nl on nl.IOID = nc.Ref_NhomLuong
				left join ODVTChiPhi as dvt on dvt.IOID = nl.Ref_DVTChiPhi
				inner join OChiTietYeuCau as ctyc on ctyc.MaSP = ct.MaSP and ifnull(ctyc.ThuocTinh,0) = ifnull(ct.ThuocTinh,0) and ct.Ref_DonViTinh = ctyc.Ref_DonViTinh
				inner join OYeuCauSanXuat as yc on yc.IFID_M764 = ctyc.IFID_M764
				where yc.SoYeuCau = %1$s and ct.Ngay between yc.TuNgay and ifnull(yc.DenNgay,\'9999-01-01\')
				group by ct.Ref_MaSP,ct.Ref_ThuocTinh,ct.Ref_CongDoan', $this->_db->quote($code));
                $dataSQL = $this->_db->fetchAll($sql);
                foreach ($dataSQL as $item)
                {
                        $arrProducts[$item->MaSP][$item->DonViTinh][$item->ThuocTinh][$item->CongDoan]['NC'] = ($item->ChiPhi) / 1000;
                        $arrQuantity[$item->MaSP][$item->DonViTinh][$item->ThuocTinh] = $item->SoLuong;
                }
                //Tinh may moc
                $sql = sprintf('select ct.*,
				sum(tb.ChiPhi * case when dvt.Ma = \'H\' then time_to_sec(timediff(ct.GioKT,ct.GioBD))/3600 else ct.SoLuongThucHien end) as ChiPhi
				from OPhieuGiaoViec as ct
				left join OTKCPMayMoc as mm on mm.IFID_M712 = ct.IFID_M712
				left join ODanhSachThietBi as tb on tb.IOID = mm.Ref_MaThietBi 
				left join ODVTChiPhi as dvt on dvt.IOID = tb.Ref_DVTChiPhi
				inner join OChiTietYeuCau as ctyc on ctyc.MaSP = ct.MaSP and ifnull(ctyc.ThuocTinh,0) = ifnull(ct.ThuocTinh,0) and ct.Ref_DonViTinh = ctyc.Ref_DonViTinh
				inner join OYeuCauSanXuat as yc on yc.IFID_M764 = ctyc.IFID_M764
				where yc.SoYeuCau = %1$s and ct.Ngay between yc.TuNgay and ifnull(yc.DenNgay,\'9999-01-01\')
				group by ct.Ref_MaSP,ct.Ref_ThuocTinh,ct.Ref_CongDoan', $this->_db->quote($code));
                $dataSQL = $this->_db->fetchAll($sql);
                foreach ($dataSQL as $item)
                {
                        $arrProducts[$item->MaSP][$item->DonViTinh][$item->ThuocTinh][$item->CongDoan]['MM'] = ($item->ChiPhi) / 1000;
                        $arrQuantity[$item->MaSP][$item->DonViTinh][$item->ThuocTinh] = $item->SoLuong;
                }
                //Chi phi gian tiep
                $sql = sprintf('select ct.*,
				sum(tg.ChiPhi * case when dvt.Ma = \'H\' then time_to_sec(timediff(ct.GioKT,ct.GioBD))/3600 else ct.SoLuongThucHien end) as ChiPhi
				from OPhieuGiaoViec as ct
				left join OTKCPGianTiep as gt on gt.IFID_M712 = ct.IFID_M712
				left join OChiPhiKhac as tg on tg.IOID = gt.Ref_TenChiPhi 
				left join ODVTChiPhi as dvt on dvt.IOID = tg.Ref_DVTChiPhi
				inner join OChiTietYeuCau as ctyc on ctyc.MaSP = ct.MaSP and ifnull(ctyc.ThuocTinh,0) = ifnull(ct.ThuocTinh,0) and ct.Ref_DonViTinh = ctyc.Ref_DonViTinh
				inner join OYeuCauSanXuat as yc on yc.IFID_M764 = ctyc.IFID_M764
				where yc.SoYeuCau = %1$s and ct.Ngay between yc.TuNgay and ifnull(yc.DenNgay,\'9999-01-01\')
				group by ct.Ref_MaSP,ct.Ref_ThuocTinh,ct.Ref_CongDoan', $this->_db->quote($code));
                $dataSQL = $this->_db->fetchAll($sql);
                foreach ($dataSQL as $item)
                {
                        $arrProducts[$item->MaSP][$item->DonViTinh][$item->ThuocTinh][$item->CongDoan]['GT'] = ($item->ChiPhi) / 1000;
                        $arrQuantity[$item->MaSP][$item->DonViTinh][$item->ThuocTinh] = $item->SoLuong;
                }
                $arrLines = array();
                foreach ($arrProducts as $masp => $val1)
                {
                        foreach ($val1 as $donvitinh => $val2)
                        {
                                foreach ($val2 as $thuoctinh => $val3)
                                {
                                        foreach ($val3 as $congdoan => $val4)
                                        {
                                                $arrLines[] = array('MaSanPham' => $masp,
                                                    'ThuocTinh' => $thuoctinh,
                                                    'DonViTinh' => $donvitinh,
                                                    'CPGianTiep' => (int) @$val4['GT'],
                                                    'CongDoan' => $congdoan,
                                                    'SoLuong' => $arrQuantity[$masp][$donvitinh][$thuoctinh],
                                                    'CPNVL' => (int) @$val4['NVL'],
                                                    'CPNhanCong' => (int) @$val4['NC'],
                                                    'CPMayMoc' => (int) @$val4['MM'],
                                                    'GiaThanh' => (int) @$val4['GT'] + (int) @$val4['NVL'] + (int) @$val4['NC'] + (int) @$val4['MM'],
                                                    'GiaThanhDonVi' => ((int) @$val4['GT'] + (int) @$val4['NVL'] + (int) @$val4['NC'] + (int) @$val4['MM']) / $arrQuantity[$masp][$donvitinh][$thuoctinh]
                                                );
                                        }
                                }
                        }
                }
                $service = $this->services->Form->Manual('M713', $this->_form->i_IFID, array('OGiaThanhSanXuat' => $arrLines), false);
                if ($service->isError())
                {
                        $this->setError();
                        $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                }
        }
}

?>