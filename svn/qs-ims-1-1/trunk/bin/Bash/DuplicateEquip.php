<?php
/**
 * @author Thinh Tuan
 * @date 20/10/2014
 * @module Nhan doi thiet bi
 */
class Qss_Bin_Bash_DuplicateEquip extends Qss_Lib_Bin
{
	public function __doExecute()
	{
		$common   = new Qss_Model_Extra_Extra();
		$linkTemp = '';
		$comLinkTemp = ''; 
		$insert   = array();
		
		$line      = $common->getTable(array('*'), 'ODanhSachThietBi', array('IFID_M705'=>$this->_params->IFID_M705), array(), 1, 1);
		$component = $common->getTable(array('*'), 'OCauTrucThietBi', array('IFID_M705'=>$this->_params->IFID_M705), array('lft'));
		$sparepart = $common->getTable(array('*'), 'ODanhSachPhuTung', array('IFID_M705'=>$this->_params->IFID_M705));

		$dacTinh   = $common->getTable(array('*'), 'ODacTinhThietBi', array('IFID_M705'=>$this->_params->IFID_M705));
		//$hieuChuan = $common->getTable(array('*'), 'OChuKyHieuChuan', array('IFID_M705'=>$this->_params->IFID_M705));
		$baoHiem   = $common->getTable(array('*'), 'OBaoHiemThietBi', array('IFID_M705'=>$this->_params->IFID_M705));

        // $technote  = $common->getTable(array('*'), 'OThongSoKyThuatTB', array('IFID_M705'=>$this->_params->IFID_M705));
        if(Qss_Lib_System::objectInForm('M705', 'ODanhSachDiemDo'))
        {
        	$diemDo    = $common->getTable(array('*'), 'ODanhSachDiemDo', array('IFID_M705'=>$this->_params->IFID_M705));
        }
        else
        {
        	$diemDo = array();
        }

		$copyImage = '';

		if($line && $line->Anh)
		{
			// Link anh goc
			$image  = $line?"{$line->Anh}":'';
			$link   = $image?QSS_DATA_DIR.'/documents/'.$image:'';

			if(file_exists($link))
			{
				// id cua anh cuoi cung
				$copyImageID = uniqid();
				$copyImage = $copyImageID.'.'.$line->Anh;
				$copyLink  = QSS_DATA_DIR.'/tmp/'.$copyImage;

				// copy anh 
				$linkTemp = copy($link, $copyLink);
			}
		}
		
		$insert['ODanhSachThietBi'][0]['MaThietBi']        = $line->MaThietBi.'_'.uniqid().'_copy';
		$insert['ODanhSachThietBi'][0]['LoaiThietBi']      = (int)$line->Ref_LoaiThietBi;
		$insert['ODanhSachThietBi'][0]['TenThietBi']       = $line->TenThietBi;
		$insert['ODanhSachThietBi'][0]['NhomThietBi']      = (int)$line->Ref_NhomThietBi;
		$insert['ODanhSachThietBi'][0]['NamSanXuat']       = $line->NamSanXuat;
		$insert['ODanhSachThietBi'][0]['Serial']           = $line->Serial;
		$insert['ODanhSachThietBi'][0]['Model']            = $line->Model;
		$insert['ODanhSachThietBi'][0]['XuatXu']           = $line->XuatXu;
		$insert['ODanhSachThietBi'][0]['TrucThuoc']        = (int)$line->Ref_TrucThuoc;
		$insert['ODanhSachThietBi'][0]['DacTinhKT']        = $line->DacTinhKT;
		$insert['ODanhSachThietBi'][0]['NgayNgung']        = $line->NgayNgung;
		$insert['ODanhSachThietBi'][0]['TrangThai']        = $line->TrangThai;
		$insert['ODanhSachThietBi'][0]['ClassHuHong']      = $line->ClassHuHong;

		$insert['ODanhSachThietBi'][0]['MaTaiSan']         = $line->MaTaiSan?$line->MaTaiSan.'_'.uniqid().'_copy':'';
		$insert['ODanhSachThietBi'][0]['NgayMua']          = $line->NgayMua;
		$insert['ODanhSachThietBi'][0]['HangBaoHanh']      = $line->HangBaoHanh;
		$insert['ODanhSachThietBi'][0]['HanBaoHanh']       = $line->HanBaoHanh;
		$insert['ODanhSachThietBi'][0]['NgayDuaVaoSuDung'] = $line->NgayDuaVaoSuDung;
		$insert['ODanhSachThietBi'][0]['NguyenGia']        = Qss_Lib_Util::formatMoney($line->NguyenGia);
		$insert['ODanhSachThietBi'][0]['GiaTriThanhLy']    = $line->GiaTriThanhLy;
		$insert['ODanhSachThietBi'][0]['TinhKhauHao']      = $line->TinhKhauHao;
		$insert['ODanhSachThietBi'][0]['ChiSo']            = $line->ChiSo;
		$insert['ODanhSachThietBi'][0]['GiaTri']           = $line->GiaTri;
		$insert['ODanhSachThietBi'][0]['TrungTamChiPhi']   = $line->TrungTamChiPhi;

		$insert['ODanhSachThietBi'][0]['DayChuyen']        = $line->DayChuyen;
		$insert['ODanhSachThietBi'][0]['LichLamViec']      = $line->LichLamViec;
		$insert['ODanhSachThietBi'][0]['MaKhuVuc']         = $line->MaKhuVuc;
		$insert['ODanhSachThietBi'][0]['QuanLy']           = $line->QuanLy;
		$insert['ODanhSachThietBi'][0]['DuAn']             = $line->DuAn;
		$insert['ODanhSachThietBi'][0]['Anh']              = $copyImage;
		
		$comIndex = 0;
		foreach($component as $com)
		{
			$comLinkTemp = '';
			$copyImage   = '';

			if($com->Anh)
			{
				// Link anh goc
				$image  = $com?"{$com->Anh}":'';
				$link   = $image?QSS_DATA_DIR.'/documents/'.$image:'';

				if(file_exists($link))
				{
					// id cua anh cuoi cung
					$comCopyImageID = uniqid();
					$copyImage      = $comCopyImageID.'.'.$com->Anh;
					$copyLink       = QSS_DATA_DIR.'/tmp/'.$copyImage;

					// copy anh
					$comLinkTemp = copy($link, $copyLink);
				}
			}

            $insert['OCauTrucThietBi'][$comIndex]['ViTri']        = @$com->ViTri;
            $insert['OCauTrucThietBi'][$comIndex]['BoPhan']       = @$com->BoPhan;
            $insert['OCauTrucThietBi'][$comIndex]['PhuTung']      = @(int)$com->PhuTung;
            $insert['OCauTrucThietBi'][$comIndex]['MaSP']         = @(int)$com->Ref_MaSP;
            $insert['OCauTrucThietBi'][$comIndex]['TenSP']        = @(int)$com->Ref_MaSP;
            $insert['OCauTrucThietBi'][$comIndex]['Serial']       = @(int)$com->Ref_Serial;
            $insert['OCauTrucThietBi'][$comIndex]['DonViTinh']    = @(int)$com->Ref_DonViTinh;
            $insert['OCauTrucThietBi'][$comIndex]['TrucThuoc']    = @$com->TrucThuoc;
            $insert['OCauTrucThietBi'][$comIndex]['Anh']          = @$copyImage;
            $insert['OCauTrucThietBi'][$comIndex]['ClassHuHong']  = @(int)$com->Ref_ClassHuHong;
            $insert['OCauTrucThietBi'][$comIndex]['SoLuongChuan'] = @$com->SoLuongChuan;
            $insert['OCauTrucThietBi'][$comIndex]['SoLuongHC']    = @$com->SoLuongHC;
            $insert['OCauTrucThietBi'][$comIndex]['SoLuongHC']    = @$com->SoNgayCanhBao;
            $insert['OCauTrucThietBi'][$comIndex]['MoTa']         = @$com->MoTa;
			$comIndex++;
		}
		
		$sparepartIndex = 0;
		foreach($sparepart as $spare)
		{
			$insert['ODanhSachPhuTung'][$sparepartIndex]['ViTri']         = @$spare->ViTri;
            $insert['ODanhSachPhuTung'][$sparepartIndex]['BoPhan']        = @$spare->BoPhan;
			$insert['ODanhSachPhuTung'][$sparepartIndex]['MaSP']          = @(int)$spare->Ref_MaSP;
            $insert['ODanhSachPhuTung'][$sparepartIndex]['TenSP']         = @(int)$spare->Ref_MaSP;
			$insert['ODanhSachPhuTung'][$sparepartIndex]['DonViTinh']     = @(int)$spare->Ref_DonViTinh;
			$sparepartIndex++;
		}

        $techIndex = 0;
        foreach($diemDo as $te)
        {
            $insert['ODanhSachDiemDo'][$techIndex]['Ma']          = @$te->Ma;
            $insert['ODanhSachDiemDo'][$techIndex]['MaThietBi']   = @(int)$te->Ref_MaThietBi;
            $insert['ODanhSachDiemDo'][$techIndex]['BoPhan']      = @$te->BoPhan;
            $insert['ODanhSachDiemDo'][$techIndex]['ChiSo']       = @(int)$te->Ref_ChiSo;
            $insert['ODanhSachDiemDo'][$techIndex]['GioiHanDuoi'] = @$te->GioiHanDuoi;
            $insert['ODanhSachDiemDo'][$techIndex]['GioiHanTren'] = @$te->GioiHanTren;
            $insert['ODanhSachDiemDo'][$techIndex]['ThuCong']     = @$te->ThuCong;
            $insert['ODanhSachDiemDo'][$techIndex]['Ky']          = @(int)$te->Ref_Ky;
            $insert['ODanhSachDiemDo'][$techIndex]['Thu']         = @(int)$te->Ref_Thu;
            $insert['ODanhSachDiemDo'][$techIndex]['Ngay']        = @(int)$te->Ref_Ngay;
            $insert['ODanhSachDiemDo'][$techIndex]['Thang']       = @(int)$te->Ref_Thang;
            $insert['ODanhSachDiemDo'][$techIndex]['LapLai']      = @$te->LapLai?$te->LapLai:1;
            $insert['ODanhSachDiemDo'][$techIndex]['GhiChu']      = @$te->GhiChu;
            $techIndex++;
        }

		$techIndex = 0;
		foreach($dacTinh as $te)
		{
			$insert['ODacTinhThietBi'][$techIndex]['DonViTinh'] = @(int)$te->Ref_DonViTinh;
			$insert['ODacTinhThietBi'][$techIndex]['GiaTri']    = @$te->GiaTri;
			$insert['ODacTinhThietBi'][$techIndex]['Ten']       = @(int)$te->Ref_Ten;
			$techIndex++;
		}

		/*$techIndex = 0;
		foreach($hieuChuan as $te)
		{
			$insert['OChuKyHieuChuan'][$techIndex]['ViTri']       = @$te->ViTri;
            $insert['OChuKyHieuChuan'][$techIndex]['BoPhan']      = @$te->BoPhan;
			$insert['OChuKyHieuChuan'][$techIndex]['Ten']         = @$te->Ten;
			$insert['OChuKyHieuChuan'][$techIndex]['Loai']        = @(int)$te->Ref_Loai;
			$insert['OChuKyHieuChuan'][$techIndex]['ChuKy']       = @(int)$te->Ref_ChuKy;
			$insert['OChuKyHieuChuan'][$techIndex]['NoiNgoai']    = @$te->NoiNgoai;
            $insert['OChuKyHieuChuan'][$techIndex]['CapChinhXac'] = @$te->CapChinhXac;
            $insert['OChuKyHieuChuan'][$techIndex]['AnToan']      = @$te->AnToan;
			$insert['OChuKyHieuChuan'][$techIndex]['GhiChu']      = @$te->GhiChu;
			$techIndex++;
		}*/

		$techIndex = 0;
		foreach($baoHiem as $te)
		{
			$insert['OBaoHiemThietBi'][$techIndex]['NgayDongBaoHiem'] = @$te->NgayDongBaoHiem;
			$insert['OBaoHiemThietBi'][$techIndex]['NgayHetHan']      = @$te->NgayHetHan;
			$insert['OBaoHiemThietBi'][$techIndex]['DonViBaoHiem']    = @(int)$te->Ref_DonViBaoHiem;
			$insert['OBaoHiemThietBi'][$techIndex]['SoTienDong']      = @$te->SoTienDong;
			$insert['OBaoHiemThietBi'][$techIndex]['NoiDung']         = @$te->NoiDung;
			$techIndex++;
		}
		
		$service = $this->services->Form->Manual('M705' ,0,$insert,false);
		if($service->isError())
		{
			$this->setError();
			$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
		}
		
		if(file_exists($linkTemp)) unlink($linkTemp);
		if(file_exists($comLinkTemp)) unlink($comLinkTemp);
		
		if(!$this->isError())
		{
			//$this->setMessage('Nhân đôi bản ghi thành công!');
			$service->setRedirect('/user/form/edit?ifid='.$service->getData().'&deptid=1');
		}
		
	}
	
}