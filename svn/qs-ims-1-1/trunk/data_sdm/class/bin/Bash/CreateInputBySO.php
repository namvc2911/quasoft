<?php
class Qss_Bin_Bash_CreateInputBySO extends Qss_Lib_Bin
{
    public function __doExecute()
    {
        $ifid    = $this->_params->IFID_M505;
        $import  = new Qss_Model_Import_Form('M402',false, false);
        $mCommon = new Qss_Model_Extra_Extra();
        $data    = $mCommon->getTableFetchAll('ODSDonBanHang', array('IFID_M505'=>$ifid));
        $insert  = array();
        $main    = $mCommon->getTableFetchOne('ODonBanHang', array('IFID_M505'=>$ifid));
        $kho     = $mCommon->getTableFetchOne('ODanhSachKho', array('IFNULL(VatTu, 0)'=>1));
        $i       = 0;

        if($main)
        {
            $insert["ONhapKho"][0]["Kho"]                 = @$kho->IOID;
            $insert["ONhapKho"][0]["MaNCC"]               = "";//(int)$main->Ref_MaNCC;;
            $insert["ONhapKho"][0]["TenNCC"]              = "";//(int)$main->Ref_MaNCC;;
            $insert["ONhapKho"][0]["LoaiNhapKho"]         = "";//@$main->LoaiNhapKho;
            $insert["ONhapKho"][0]["NgayChungTu"]         = date('Y-m-d');
            $insert["ONhapKho"][0]["SoChungTu"]           = 'FROM_'.$main->SoDonHang;;
            $insert["ONhapKho"][0]["NgayChuyenHang"]      = date('Y-m-d');
            $insert["ONhapKho"][0]["DonViThucHien"]       = "";//@$main->DonViThucHien;
            $insert["ONhapKho"][0]["NguoiGiao"]           = "";//@$main->NguoiGiao;
            $insert["ONhapKho"][0]["NguoiNhan"]           = "";//@$main->NguoiNhan;
            $insert["ONhapKho"][0]["DuAn"]                = "";//@$main->DuAn;
            $insert["ONhapKho"][0]["SoDonHang"]           = "";//@$main->SoDonHang;
            $insert["ONhapKho"][0]["PhieuXuatKho"]        = "";//@$main->PhieuXuatKho;
            $insert["ONhapKho"][0]["MoTa"]                = "";//@$main->MoTa;

            foreach ($data as $item)
            {
                $insert["ODanhSachNhapKho"][$i]["MaSanPham"]           = $item->MaSP;//"";//$main->MaSanPham;
                $insert["ODanhSachNhapKho"][$i]["TenSanPham"]          = $item->TenSP;//"";//$main->TenSanPham;
                $insert["ODanhSachNhapKho"][$i]["DonViTinh"]           = $item->DonViTinh;//"";//$main->DonViTinh;
                $insert["ODanhSachNhapKho"][$i]["DacTinhKyThuat"]      = "";//$main->DacTinhKyThuat;
                $insert["ODanhSachNhapKho"][$i]["SoLuong"]             = $item->SoLuong;//$main->SoLuong;
                $insert["ODanhSachNhapKho"][$i]["DonGia"]              = $item->DonGia;//$main->DonGia;
                $insert["ODanhSachNhapKho"][$i]["ThanhTien"]           = $item->ThanhTien;;//$main->ThanhTien;
                $insert["ODanhSachNhapKho"][$i]["TrangThaiLuuTru"]     = "";//$main->TrangThaiLuuTru;
                $insert["ODanhSachNhapKho"][$i]["SoYeuCau"]            = "";//$main->SoYeuCau;
                $insert["ODanhSachNhapKho"][$i]["MoTa"]                = "";//$main->MoTa;
                $insert["ODanhSachNhapKho"][$i]["SoLuongMat"]          = "";//$main->SoLuongMat;
                $i++;
            }

            $import->setData($insert);
            $import->generateSQL();
            $error = $import->countFormError() + $import->countObjectError();

            if($error)
            {
                $this->setError();
                $this->setMessage('Không tạo được phiếu nhập kho!');
            }
        }
        else
        {
            $this->setError();
            $this->setMessage('Không tồn tại đơn bán hàng!');
        }


        if(!$this->isError())
        {
            $this->setMessage('Đã tạo phiếu nhập kho!');
        }
    }
}