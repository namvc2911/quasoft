<?php
class Qss_Bin_Bash_CreateOutputBySO extends Qss_Lib_Bin
{
    public function __doExecute()
    {
        $ifid    = $this->_params->IFID_M505;
        $import  = new Qss_Model_Import_Form('M402',false, false);
        $mCommon = new Qss_Model_Extra_Extra();
        $mInout  = new Qss_Model_Warehouse_Main();
        $data    = $mCommon->getTableFetchAll('ODSDonBanHang', array('IFID_M505'=>$ifid));
        $insert  = array();
        $main    = $mCommon->getTableFetchOne('ODonBanHang', array('IFID_M505'=>$ifid));
        $kho     = $mCommon->getTableFetchOne('ODanhSachKho', array('IFNULL(VatTu, 0)'=>1));
        $i       = 0;

        if($main)
        {
            $insert["OXuatKho"][0]["Kho"]                 = @$kho->IOID;
            $insert["OXuatKho"][0]["MaKH"]                = @$main->Ref_MaKhachHang;
            $insert["OXuatKho"][0]["TenKhachHang"]        = @$main->Ref_MaKhachHang;
            $insert["OXuatKho"][0]["LoaiXuatKho"]         = '';//$main->LoaiXuatKho;
            $insert["OXuatKho"][0]["NgayChungTu"]         = date('Y-m-d');//$main->NgayChungTu;
            $insert["OXuatKho"][0]["SoChungTu"]           = $mInout->getOutputNoByConfig();
            $insert["OXuatKho"][0]["NgayChuyenHang"]      = date('Y-m-d');
            $insert["OXuatKho"][0]["KhoChuyenDen"]        = '';//$main->KhoChuyenDen;
            $insert["OXuatKho"][0]["DonViThucHien"]       = '';//$main->DonViThucHien;
            $insert["OXuatKho"][0]["NguoiGiao"]           = '';//$main->NguoiGiao;
            $insert["OXuatKho"][0]["NguoiNhan"]           = '';//$main->NguoiNhan;
            $insert["OXuatKho"][0]["DuAn"]                = '';//$main->DuAn;
            $insert["OXuatKho"][0]["MoTa"]                = '';//$main->MoTa;
            $insert["OXuatKho"][0]["SoDonHang"]           = $main->IOID;
            $insert["OXuatKho"][0]["PhieuBaoTri"]         = '';//$main->PhieuBaoTri;

            foreach ($data as $item)
            {
                $insert["ODanhSachXuatKho"][$i]["MaSP"]                = $item->MaSP;
                $insert["ODanhSachXuatKho"][$i]["TenSP"]               = $item->TenSP;
                $insert["ODanhSachXuatKho"][$i]["DonViTinh"]           = $item->DonViTinh;
                $insert["ODanhSachXuatKho"][$i]["ThuocTinh"]           = '';//$item->ThuocTinh;
                $insert["ODanhSachXuatKho"][$i]["DacTinhKyThuat"]      = '';//$item->DacTinhKyThuat;
                $insert["ODanhSachXuatKho"][$i]["SoLuong"]             = $item->SoLuong;
                $insert["ODanhSachXuatKho"][$i]["TonKho"]              = '';///$item->TonKho;
                $insert["ODanhSachXuatKho"][$i]["DonGia"]              = $item->DonGia;
                $insert["ODanhSachXuatKho"][$i]["LayGia"]              = '';//$item->LayGia;
                $insert["ODanhSachXuatKho"][$i]["ThanhTien"]           = $item->ThanhTien;
                $insert["ODanhSachXuatKho"][$i]["TrangThaiLuuTru"]     = '';//$item->TrangThaiLuuTru;
                $insert["ODanhSachXuatKho"][$i]["MoTa"]                = '';$item->MoTa;
                $i++;
            }

            $import->setData($insert);
            $import->generateSQL();
            $error = $import->countFormError() + $import->countObjectError();

            if($error)
            {
                $this->setError();
                $this->setMessage('Không tạo được phiếu xuất kho!');
            }
        }
        else
        {
            $this->setError();
            $this->setMessage('Không tồn tại đơn bán hàng!');
        }


        if(!$this->isError())
        {
            $this->setMessage('Đã tạo phiếu xuất kho!');
        }
    }
}