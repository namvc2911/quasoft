<?php
class Qss_Bin_Bash_CreateSaleQuoteFormWorkorder extends Qss_Lib_Bin
{
    public function __doExecute()
    {
        $ifid    = $this->_params->IFID_M759;
        $mQuote  = new Qss_Model_Sale_Quote();
        $import  = new Qss_Model_Import_Form('M509',false, false);
        $mCommon = new Qss_Model_Extra_Extra();
        $insert  = array();
        $main    = $mCommon->getTableFetchOne('OPhieuBaoTri', array('IFID_M759'=>$ifid));
        $data    = $mCommon->getTableFetchAll('OVatTuPBT', array('IFID_M759'=>$ifid));
        $i       = 0;

        if($main)
        {
            $insert["OBaoGiaBanHang"][0]["SoChungTu"]           = $mQuote->getDocNo('BGB.SDM.{Y}/{m}/{d}_');
            $insert["OBaoGiaBanHang"][0]["MaKH"]                = @$main->Ref_BenKhachHang;
            $insert["OBaoGiaBanHang"][0]["TenKH"]               = @$main->Ref_BenKhachHang;
            $insert["OBaoGiaBanHang"][0]["NVBanHang"]           = '';//$main->NVBanHang;
            $insert["OBaoGiaBanHang"][0]["NgayYeuCau"]          = '';//$main->NgayYeuCau;
            $insert["OBaoGiaBanHang"][0]["NgayBaoGia"]          = date('Y-m-d');//$main->NgayBaoGia;
            $insert["OBaoGiaBanHang"][0]["LoaiTien"]            = Qss_Lib_Extra::getDefaultCurrency();//$main->LoaiTien;
            $insert["OBaoGiaBanHang"][0]["GiaTriDonHang"]       = '';//$main->GiaTriDonHang;
            $insert["OBaoGiaBanHang"][0]["Thue"]                = '';//$main->Thue;
            $insert["OBaoGiaBanHang"][0]["TongTien"]            = '';//$main->TongTien;
            $insert["OBaoGiaBanHang"][0]["ChiPhiVanChuyen"]     = '';//$main->ChiPhiVanChuyen;
            $insert["OBaoGiaBanHang"][0]["GiamTru"]             = '';//$main->GiamTru;
            $insert["OBaoGiaBanHang"][0]["TongTienDonHang"]     = '';//$main->TongTienDonHang;
            $insert["OBaoGiaBanHang"][0]["PhieuBaoTri"]         = $main->IOID;//$main->TongTienDonHang;
            $insert["OBaoGiaBanHang"][0]["GhiChu"]              = 'Created from '. $main->SoChungTu;//$main->GhiChu;


            foreach ($data as $item)
            {
                $insert["ODSBGBanHang"][$i]["MaSP"]                = $item->MaVatTu;
                $insert["ODSBGBanHang"][$i]["TenSP"]               = $item->TenVatTu;
                $insert["ODSBGBanHang"][$i]["DonViTinh"]           = $item->DonViTinh;
                $insert["ODSBGBanHang"][$i]["DonGia"]              = @$item->DonGia;
                $insert["ODSBGBanHang"][$i]["SoLuong"]             = $item->SoLuong;
                $insert["ODSBGBanHang"][$i]["ThanhTien"]           = @$item->ThanhTien;
                $i++;
            }

            $import->setData($insert);
            $import->generateSQL();
            $error = $import->countFormError() + $import->countObjectError();

            if($error)
            {
                $this->setError();
                $this->setMessage('Không tạo được báo giá!');
            }
        }
        else
        {
            $this->setError();
            $this->setMessage('Không có phiếu tham chiếu!');
        }


        if(!$this->isError())
        {
            $this->setMessage('Đã tạo báo giá thành công!');
        }
    }
}