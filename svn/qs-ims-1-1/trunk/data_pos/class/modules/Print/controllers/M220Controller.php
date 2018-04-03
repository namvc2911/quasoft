<?php
class Print_M220Controller extends Qss_Lib_Controller
{
    public function init()
    {
        $this->i_SecurityLevel = 15;
        parent::init();
        $this->headScript($this->params->requests->getBasePath() . '/js/common.js');
        $this->headScript($this->params->requests->getBasePath() . '/js/ajaxfileupload.js');
        $this->headScript($this->params->requests->getBasePath() . '/js/tabs.js');

        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/wide.php';
    }

    public function denghithanhtoanAction()
    {
        header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-disposition: attachment; filename=\"Đề nghị thanh toán.xlsx\"");

        $mRequest       = new Qss_Model_Purchase_Request();
        $mCommon        = new Qss_Model_Extra_Extra();
        $main           = new stdClass();
        $ifid           = $this->params->requests->getParam('ifid', 0);
        $file           = new Qss_Model_Excel(QSS_DATA_DIR.'/template/M220/DeNghiThanhToan.xlsx');
        $pay            = $mCommon->getTableFetchOne('OThanhToan', array('IFID_M220'=>$ifid));
        $refOrder       = $pay?$pay->Ref_SoDonHang:0;
        $order          = $mCommon->getTableFetchOne('ODonMuaHang', array('IOID'=>$refOrder));
        $requests       = $mRequest->getRequestsByOrder($refOrder);

        $ngayHoaDon     = '';
        $ngayDonHang    = '';
        $CacPhieuYeuCau = array();
        $CacDuAn        = array();
        $tienMat        = ($pay && $pay->Ref_PhuongThucTT == 2)?'V':'X';
        $chuyenKhoan    = ($pay && $pay->Ref_PhuongThucTT == 1)?'V':'X';;

        if($pay && $pay->NgayHoaDon)
        {
            $ngayHoaDon = "Ngày ".date('d', strtotime($pay->NgayHoaDon))
                ." tháng ".date('m', strtotime($pay->NgayHoaDon))
                ." năm ".date('Y', strtotime($pay->NgayHoaDon));
        }

        // echo '<pre>'; print_r($order->NgayDatHang); die;

        if($order && $order->NgayDatHang)
        {
            $ngayDonHang = "Ngày ".date('d', strtotime($order->NgayDatHang))
                ." tháng ".date('m', strtotime($order->NgayDatHang))
                ." năm ".date('Y', strtotime($order->NgayDatHang));
        }

        foreach($requests as $item)
        {
            if($item->SoPhieu && !in_array($item->SoPhieu, $CacPhieuYeuCau))
            {
                $CacPhieuYeuCau[] = $item->SoPhieu;
            }

            if($item->DuAn && !in_array($item->DuAn, $CacDuAn))
            {
                $CacDuAn[] = $item->SoPhieu;
            }
        }

        $main->SoDeNghiThanhToan         = $pay?$pay->SoDeNghiThanhToan:'';
        $main->user_name                 = $this->_user->user_desc;
        $main->SoNgayThanhToan           = ($pay && $pay->SoNgayThanhToan)?$pay->SoNgayThanhToan:0;
        $main->TenDoiTac                 = $pay?$pay->TenNCC:'';
        $main->SoHoaDon                  = $pay?$pay->SoHoaDon:'';
        $main->NgayHoaDon                = $ngayHoaDon; // Dạng ngày x tháng y năm z
        $main->SoDonHang                 = $pay?$pay->SoDonHang:'';
        $main->NgayDonHang               = $ngayDonHang;
        $main->CacPhieuYeuCau            = implode(', ', $CacPhieuYeuCau);
        $main->CacDuAn                   = implode(', ', $CacDuAn);
        $main->SoTienBangChu             = $pay?Qss_Lib_Util::VndText($pay->SoTienDaTT/1000).' ':'';; // bao gom đơn vị tiền tệ
        $main->TongSoTienDeNghiThanhToan = $pay?Qss_Lib_Util::formatMoney($pay->SoTienDaTT).' '.$pay->LoaiTien:'';; // bao gom đơn vị tiền tệ
        $main->TienMat                   = $tienMat; // x hoặc v
        $main->ChuyenKhoan               = $chuyenKhoan; // x hoặc v
        $main->SoGiaiTrinhThanhToan      = $pay?$pay->SoGiaiTrinhThanhToan:'';
        $main->PhuTrachDonVi             = 'Phạm Thế Phong';

        $data = array('main'=>$main);
        $file->init($data);

        $file->save();
        die;
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    public function giaitrinhthanhtoanAction()
    {
        header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-disposition: attachment; filename=\"Giải trình thanh toán.xlsx\"");

        $mCommon         = new Qss_Model_Extra_Extra();
        $mRequest        = new Qss_Model_Purchase_Request();
        $main            = new stdClass();
        $ifid            = $this->params->requests->getParam('ifid', 0);
        $file            = new Qss_Model_Excel(QSS_DATA_DIR.'/template/M220/GiaiTrinhThanhToan.xlsx');
        $pay             = $mCommon->getTableFetchOne('OThanhToan', array('IFID_M220'=>$ifid));
        $refOrder        = $pay?$pay->Ref_SoDonHang:0;
        $order           = $mCommon->getTableFetchOne('ODonMuaHang', array('IOID'=>$refOrder));
        $requests       = $mRequest->getRequestsByOrder($refOrder);

        $ngayHoaDon      = '';
        $ngayDonHang     = '';
        $CacPhieuYeuCau  = array();
        $CacDuAn         = array();
        $tienMat         = ($pay && $pay->Ref_PhuongThucTT == 2)?'V':'X';
        $chuyenKhoan     = ($pay && $pay->Ref_PhuongThucTT == 1)?'V':'X';;
        $tongTienChuaVAT = 0;
        $VAT             = 0;
        $tongTienBangChu = $pay?Qss_Lib_Util::VndText($pay->SoTienDaTT/1000):'';

        if($pay && $pay->NgayHoaDon)
        {
            $ngayHoaDon = "Ngày ".date('d', strtotime($pay->NgayHoaDon))
                ." tháng ".date('m', strtotime($pay->NgayHoaDon))
                ." năm ".date('Y', strtotime($pay->NgayHoaDon));
        }

        if($order && $order->NgayDatHang)
        {
            $ngayDonHang = "Ngày ".date('d', strtotime($order->NgayDatHang))
                ." tháng ".date('m', strtotime($order->NgayDatHang))
                ." năm ".date('Y', strtotime($order->NgayDatHang));
        }

        if($pay && $pay->SoTienDaTT)
        {
            $tongTienChuaVAT = round(($pay->SoTienDaTT/110 * 100), 0);
            $VAT             = round(($pay->SoTienDaTT/110 * 10), 0);
        }

        foreach($requests as $item)
        {
            if($item->SoPhieu && !in_array($item->SoPhieu, $CacPhieuYeuCau))
            {
                $CacPhieuYeuCau[] = $item->SoPhieu;
            }

            if($item->DuAn && !in_array($item->DuAn, $CacDuAn))
            {
                $CacDuAn[] = $item->SoPhieu;
            }
        }


        $main->SoGiaiTrinhThanhToan      = $pay?$pay->SoGiaiTrinhThanhToan:'';
        $main->SoDeNghiThanhToan         = $pay?$pay->SoDeNghiThanhToan:'';
        $main->SoDonHang                 = $pay?$pay->SoDonHang:'';
        $main->NgayDonHang               = $ngayDonHang;
        $main->TenDoiTac                 = $pay?$pay->TenNCC:'';
        $main->NgayHoaDon                = $ngayHoaDon; // Dạng ngày x tháng y năm z
        $main->SoHoaDon                  = $pay?$pay->SoHoaDon:'';
        $main->TongTienChuaVAT           = Qss_Lib_Util::formatMoney($tongTienChuaVAT). ' '.$pay->LoaiTien;
        $main->VAT                       = Qss_Lib_Util::formatMoney($VAT). ' '.$pay->LoaiTien;
        $main->TongTien                  = $pay?Qss_Lib_Util::formatMoney($pay->SoTienDaTT).' '.$pay->LoaiTien:'';; // bao gom đơn vị tiền tệ
        $main->CacSoYeuCau               = implode(', ', $CacPhieuYeuCau);
        $main->CacDuAn                   = implode(', ', $CacDuAn);
        $main->TongTienBangChu           = $tongTienBangChu; // VND
        $main->PhuTrachDonVi             = 'Phạm Thế Phong';


        $data = array('main'=>$main);
        $file->init($data);

        $file->save();
        die;
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
}