<?php
class Qss_Bin_Bash_CreateSOFromQuote extends Qss_Lib_Bin
{
    public function __doExecute()
    {
        $ifid    = $this->_params->IFID_M509;
        $mOrder  = new Qss_Model_Sale_Order();
        $import  = new Qss_Model_Import_Form('M505',false, false);
        $mCommon = new Qss_Model_Extra_Extra();
        $data    = $mCommon->getTableFetchAll('OVatTuPBT', array('IFID_M509'=>$ifid));
        $insert  = array();
        $main    = $mCommon->getTableFetchOne('OBaoGiaBanHang', array('IFID_M509'=>$ifid));
        $i       = 0;

        if($main)
        {
            $insert["ODonBanHang"][0]["SoDonHang"]           = $mOrder->getDocNo('DBH.SDM.{Y}/{m}/{d}_');
            $insert["ODonBanHang"][0]["MaKhachHang"]         = (int)$main->Ref_MaKH;
            $insert["ODonBanHang"][0]["TenKH"]               = (int)$main->Ref_MaKH;
            $insert["ODonBanHang"][0]["NVBanHang"]           = "";
            $insert["ODonBanHang"][0]["NgayDatHang"]         = date('Y-m-d');
            $insert["ODonBanHang"][0]["NgayYCNH"]            = date('Y-m-d');
            $insert["ODonBanHang"][0]["LoaiTien"]            = $main->LoaiTien;
            $insert["ODonBanHang"][0]["GiaTri"]              = "";
            $insert["ODonBanHang"][0]["Thue"]                = "";
            $insert["ODonBanHang"][0]["TongTien"]            = "";
            $insert["ODonBanHang"][0]["CPVanChuyen"]         = "";
            $insert["ODonBanHang"][0]["GiamTru"]             = "";
            $insert["ODonBanHang"][0]["TongTienDonHang"]     = "";
            $insert["ODonBanHang"][0]["SoBaoGia"]            = $main->IOID;
            $insert["ODonBanHang"][0]["GhiChu"]              = "";

            foreach ($data as $item)
            {
                $insert["ODSDonBanHang"][$i]["DongDonHang"]         = "";
                $insert["ODSDonBanHang"][$i]["MaSP"]                = $item->MaSP;
                $insert["ODSDonBanHang"][$i]["TenSP"]               = $item->TenSP;
                $insert["ODSDonBanHang"][$i]["DonViTinh"]           = $item->DonViTinh;
                $insert["ODSDonBanHang"][$i]["DonGia"]              = $item->DonGia;
                $insert["ODSDonBanHang"][$i]["SoLuong"]             = $item->SoLuong;
                $insert["ODSDonBanHang"][$i]["ThanhTien"]           = $item->ThanhTien;
                $i++;
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
            $this->setMessage('Đã tạo đơn hàng thành công!');
        }
    }
}