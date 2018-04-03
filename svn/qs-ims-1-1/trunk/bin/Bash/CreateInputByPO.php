<?php
class Qss_Bin_Bash_CreateInputByPO extends Qss_Lib_Bin
{
    public function __doExecute()
    {
        $ifid    = $this->_params->IFID_M401;
        $import  = new Qss_Model_Import_Form('M402',false, false);
        $mCommon = new Qss_Model_Extra_Extra();
        $data    = array();
        $insert  = array();
        $main    = $mCommon->getTableFetchOne('ODonMuaHang', array('IFID'=>$ifid));
        $i       = 0;

        if($main)
        {
            $insert["ONhapKho"][0]["Kho"]                 = @$main->Kho;
            $insert["ONhapKho"][0]["MaNCC"]               = (int)$main->Ref_MaNCC;;
            $insert["ONhapKho"][0]["TenNCC"]              = (int)$main->Ref_MaNCC;;
            $insert["ONhapKho"][0]["LoaiNhapKho"]         = @$main->LoaiNhapKho;
            $insert["ONhapKho"][0]["NgayChungTu"]         = @$main->NgayChungTu;
            $insert["ONhapKho"][0]["SoChungTu"]           = 'FROM_'.$main->SoDonHang;;
            $insert["ONhapKho"][0]["NgayChuyenHang"]      = @$main->NgayChuyenHang;
            $insert["ONhapKho"][0]["DonViThucHien"]       = @$main->DonViThucHien;
            $insert["ONhapKho"][0]["NguoiGiao"]           = @$main->NguoiGiao;
            $insert["ONhapKho"][0]["NguoiNhan"]           = @$main->NguoiNhan;
            $insert["ONhapKho"][0]["DuAn"]                = @$main->DuAn;
            $insert["ONhapKho"][0]["SoDonHang"]           = @$main->SoDonHang;
            $insert["ONhapKho"][0]["PhieuXuatKho"]        = @$main->PhieuXuatKho;
            $insert["ONhapKho"][0]["MoTa"]                = @$main->MoTa;

            foreach ($data as $item)
            {
                $insert["ODanhSachNhapKho"][0]["MaSanPham"]           = $main->MaSanPham;
                $insert["ODanhSachNhapKho"][0]["TenSanPham"]          = $main->TenSanPham;
                $insert["ODanhSachNhapKho"][0]["DonViTinh"]           = $main->DonViTinh;
                $insert["ODanhSachNhapKho"][0]["ThuocTinh"]           = $main->ThuocTinh;
                $insert["ODanhSachNhapKho"][0]["DacTinhKyThuat"]      = $main->DacTinhKyThuat;
                $insert["ODanhSachNhapKho"][0]["SoLuong"]             = $main->SoLuong;
                $insert["ODanhSachNhapKho"][0]["DonGia"]              = $main->DonGia;
                $insert["ODanhSachNhapKho"][0]["ThanhTien"]           = $main->ThanhTien;
                $insert["ODanhSachNhapKho"][0]["TrangThaiLuuTru"]     = $main->TrangThaiLuuTru;
                $insert["ODanhSachNhapKho"][0]["SoYeuCau"]            = $main->SoYeuCau;
                $insert["ODanhSachNhapKho"][0]["MoTa"]                = $main->MoTa;
                $insert["ODanhSachNhapKho"][0]["SoLuongMat"]          = $main->SoLuongMat;
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
            $this->setMessage('Không tồn tại đơn mua hàng!');
        }


        if(!$this->isError())
        {
            $this->setMessage('Đã tạo phiếu nhập kho!');
        }
    }
}