<?php
class Qss_Bin_Bash_CreatePOFromQuote extends Qss_Lib_Bin
{
    public function __doExecute()
    {
        $ifid    = $this->_params->IFID_M406;
        $import  = new Qss_Model_Import_Form('M401',false, false);
        $mCommon = new Qss_Model_Extra_Extra();
        $data    = array();
        $insert  = array();
        $main    = $mCommon->getTableFetchOne('OBaoGiaMuaHang', array('IFID'=>$ifid));
        $i       = 0;

        if($main)
        {
            $insert["ODonMuaHang"][0]["SoDonHang"]           = 'FROM_'.$main->SoChungTu;
            $insert["ODonMuaHang"][0]["SoKeHoach"]           = ''; // $main->SoKeHoach;
            $insert["ODonMuaHang"][0]["MaNCC"]               = (int)$main->Ref_MaNCC;
            $insert["ODonMuaHang"][0]["TenNCC"]              = (int)$main->Ref_MaNCC;
            $insert["ODonMuaHang"][0]["MaKeHoachCU"]         = '';// $main->MaKeHoachCU;
            $insert["ODonMuaHang"][0]["NVMuaHang"]           = '';//$main->NVMuaHang;
            $insert["ODonMuaHang"][0]["NgayDatHang"]         = date('Y-m-d'); //$main->NgayDatHang;
            $insert["ODonMuaHang"][0]["NgayYCNH"]            = date('Y-m-d'); //$main->NgayYCNH;
            $insert["ODonMuaHang"][0]["LoaiTien"]            = ''; //$main->LoaiTien;
            $insert["ODonMuaHang"][0]["GiaTriDonHang"]       = ''; //$main->GiaTriDonHang;
            $insert["ODonMuaHang"][0]["Thue"]                = ''; //$main->Thue;
            $insert["ODonMuaHang"][0]["TongTien"]            = ''; //$main->TongTien;
            $insert["ODonMuaHang"][0]["PhatSinhTang"]        = ''; //$main->PhatSinhTang;
            $insert["ODonMuaHang"][0]["ChungChi"]            = ''; //$main->ChungChi;
            $insert["ODonMuaHang"][0]["GiamTru"]             = ''; //$main->GiamTru;
            $insert["ODonMuaHang"][0]["ChiPhiVanChuyen"]     = ''; //$main->ChiPhiVanChuyen;
            $insert["ODonMuaHang"][0]["TongTienDH"]          = ''; //$main->TongTienDH;
            $insert["ODonMuaHang"][0]["DiaDiemGiaoHang"]     = ''; //$main->DiaDiemGiaoHang;
            $insert["ODonMuaHang"][0]["ThoiGianGiaoHang"]    = ''; //$main->ThoiGianGiaoHang;
            $insert["ODonMuaHang"][0]["HinhThucThanhToan"]   = ''; //$main->HinhThucThanhToan;
            $insert["ODonMuaHang"][0]["ThoiGianBaoHanh"]     = ''; //$main->ThoiGianBaoHanh;
            $insert["ODonMuaHang"][0]["GhiChu"]              = ''; //$main->GhiChu;

            foreach ($data as $item)
            {
                $insert["ODSDonMuaHang"][$i]["SoYeuCau"]            = $item->SoYeuCau;
                $insert["ODSDonMuaHang"][$i]["DongDonHang"]         = $item->DongDonHang;
                $insert["ODSDonMuaHang"][$i]["MaSP"]                = $item->MaSP;
                $insert["ODSDonMuaHang"][$i]["TenSanPham"]          = $item->TenSanPham;
                $insert["ODSDonMuaHang"][$i]["DonViTinh"]           = $item->DonViTinh;
                $insert["ODSDonMuaHang"][$i]["ThuocTinh"]           = $item->ThuocTinh;
                $insert["ODSDonMuaHang"][$i]["DacTinhKyThuat"]      = $item->DacTinhKyThuat;
                $insert["ODSDonMuaHang"][$i]["NhomThue"]            = $item->NhomThue;
                $insert["ODSDonMuaHang"][$i]["DonGia"]              = $item->DonGia;
                $insert["ODSDonMuaHang"][$i]["SoLuong"]             = $item->SoLuong;
                $insert["ODSDonMuaHang"][$i]["ThanhTien"]           = $item->ThanhTien;
                $insert["ODSDonMuaHang"][$i]["NgayGiaoHang"]        = $item->NgayGiaoHang;
            }

            $import->setData($insert);
            $import->generateSQL();
            $error = $import->countFormError() + $import->countObjectError();

            if($error)
            {
                $this->setError();
                $this->setMessage('Không tạo được đơn hàng!');
            }
        }
        else
        {
            $this->setError();
            $this->setMessage('Không tồn tại báo giá!');
        }


        if(!$this->isError())
        {
            $this->setMessage('Đã tạo báo giá thành công!');
        }
    }
}