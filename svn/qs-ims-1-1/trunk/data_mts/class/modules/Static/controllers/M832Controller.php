<?php
class Static_M832Controller extends Qss_Lib_Controller
{
    public function init()
    {
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
        parent::init();
        $this->headScript($this->params->requests->getBasePath() . '/js/report-list.js'); //lay js
        $this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');
        $this->html->curl = $this->params->requests->getRequestUri(); //lay crurl (giong data) de su dung trong html
    }

    public function indexAction()
    {

    }

    public function showAction()
    {
//        $mMaintain      = new Qss_Model_Extra_Maintain();
//        $start          = $this->params->requests->getParam('start', '');
//        $end            = $this->params->requests->getParam('end', '');
//        $location       = $this->params->requests->getParam('location', '');
//        $group          = $this->params->requests->getParam('group', '');
//        $type           = $this->params->requests->getParam('type', '');
//        $equip          = $this->params->requests->getParam('equip', '');
//
//        $report         = $mMaintain->getKeHoachSuaChuaBaoDuongThuongXuyen(
//            Qss_Lib_Date::displaytomysql($start)
//            , Qss_Lib_Date::displaytomysql($end)
//            , $location
//            , $group
//            , $type
//            , $equip
//        );
//
//        $this->html->month  = date('m', strtotime($start));
//        $this->html->year   = date('Y', strtotime($start));
//        $this->html->report = $report;
        $sum   = array(); // Tinh tong theo loai bao tri, nhom thiet bi, loai thiet bi
        $mPlan = new Qss_Model_Mtsplan();
        $mCom  = new Qss_Model_Extra_Extra();
        $type  = $this->params->requests->getParam('type', '');
        $year  = $this->params->requests->getParam('year', '');
        $start = $this->params->requests->getParam('start', '');
        $end   = $this->params->requests->getParam('end', '');
        $location  = $this->params->requests->getParam('location', '');
        $objLoaiBT = $mCom->getTableFetchAll('OPhanLoaiBaoTri', array(), array('*'), array('lft'));
        $arrLoaiBT = array();
        $subTitle  = '';

        foreach ($objLoaiBT as $item) {
            $arrLoaiBT[$item->lft] = $item;
        }

        switch ($type) {
            case '1':
                $subTitle = 'Quý I năm '.$year;
                break;

            case '2':
                $subTitle = 'Quý II năm '.$year;
                break;

            case '3':
                $subTitle = 'Quý III năm '.$year;
                break;

            case '4':
                $subTitle = 'Quý IV năm '.$year;
                break;

            case '5':
                $subTitle = '6 tháng đầu năm '.$year;
                break;

            case '6':
                $subTitle = '6 tháng cuối năm '.$year;
                break;

            case '7':
                $subTitle = 'Năm '.$year;
                break;
        }

        $this->html->report = ($year)?$mPlan->getGeneralPlans($year, $location, $type):array();

        foreach($this->html->report as $item) {
            $key0 = (int)$item->KhuVucIOID; // Dem theo khu vuc
            $key1 = (int)$item->KhuVucIOID.'-'.(int)$item->LoaiBaoTriLft; // Dem theo loai bao tri
            $key2 = (int)$item->KhuVucIOID.'-'.(int)$item->LoaiBaoTriLft.'-'.(int)$item->NhomThietBiIOID; // Dem theo loai bt va nhom thiet bi
            $key3 = (int)$item->KhuVucIOID.'-'.(int)$item->LoaiBaoTriLft.'-'.(int)$item->NhomThietBiIOID.'-'.(int)$item->LoaiThietBiIOID; // Dem theo loai bt, nhom va loai tb

            if(!isset($sum[$key0])) {
                $sum[$key0]                = array();
                $sum[$key0]['TongSo']      = 0;
                $sum[$key0]['TuLam']       = 0;
                $sum[$key0]['TrongCongTy'] = 0;
                $sum[$key0]['TrongTKV']    = 0;
                $sum[$key0]['NgoaiTKV']    = 0;
            }

            if(!isset($sum[$key1])) {
                $sum[$key1]                = array();
                $sum[$key1]['TongSo']      = 0;
                $sum[$key1]['TuLam']       = 0;
                $sum[$key1]['TrongCongTy'] = 0;
                $sum[$key1]['TrongTKV']    = 0;
                $sum[$key1]['NgoaiTKV']    = 0;
            }

            if(!isset($sum[$key2])) {
                $sum[$key2]                = array();
                $sum[$key2]['TongSo']      = 0;
                $sum[$key2]['TuLam']       = 0;
                $sum[$key2]['TrongCongTy'] = 0;
                $sum[$key2]['TrongTKV']    = 0;
                $sum[$key2]['NgoaiTKV']    = 0;
            }

            if(!isset($sum[$key3])) {
                $sum[$key3]                = array();
                $sum[$key3]['TongSo']      = 0;
                $sum[$key3]['TuLam']       = 0;
                $sum[$key3]['TrongCongTy'] = 0;
                $sum[$key3]['TrongTKV']    = 0;
                $sum[$key3]['NgoaiTKV']    = 0;
            }

            if(!isset($sum['total'])) {
                $sum['total']                = array();
                $sum['total']['TongSo']      = 0;
                $sum['total']['TuLam']       = 0;
                $sum['total']['TrongCongTy'] = 0;
                $sum['total']['TrongTKV']    = 0;
                $sum['total']['NgoaiTKV']    = 0;
            }

            $sum[$key0]['TongSo']      += $item->TongSo?$item->TongSo:0;
            $sum[$key0]['TuLam']       += $item->TuLam?$item->TuLam:0;
            $sum[$key0]['TrongCongTy'] += $item->TrongCongTy?$item->TrongCongTy:0;
            $sum[$key0]['TrongTKV']    += $item->TrongTKV?$item->TrongTKV:0;
            $sum[$key0]['NgoaiTKV']    += $item->NgoaiTKV?$item->NgoaiTKV:0;

            $sum[$key1]['TongSo']      += $item->TongSo?$item->TongSo:0;
            $sum[$key1]['TuLam']       += $item->TuLam?$item->TuLam:0;
            $sum[$key1]['TrongCongTy'] += $item->TrongCongTy?$item->TrongCongTy:0;
            $sum[$key1]['TrongTKV']    += $item->TrongTKV?$item->TrongTKV:0;
            $sum[$key1]['NgoaiTKV']    += $item->NgoaiTKV?$item->NgoaiTKV:0;

            $sum[$key2]['TongSo']      += $item->TongSo?$item->TongSo:0;
            $sum[$key2]['TuLam']       += $item->TuLam?$item->TuLam:0;
            $sum[$key2]['TrongCongTy'] += $item->TrongCongTy?$item->TrongCongTy:0;
            $sum[$key2]['TrongTKV']    += $item->TrongTKV?$item->TrongTKV:0;
            $sum[$key2]['NgoaiTKV']    += $item->NgoaiTKV?$item->NgoaiTKV:0;

            $sum[$key3]['TongSo']      += $item->TongSo?$item->TongSo:0;
            $sum[$key3]['TuLam']       += $item->TuLam?$item->TuLam:0;
            $sum[$key3]['TrongCongTy'] += $item->TrongCongTy?$item->TrongCongTy:0;
            $sum[$key3]['TrongTKV']    += $item->TrongTKV?$item->TrongTKV:0;
            $sum[$key3]['NgoaiTKV']    += $item->NgoaiTKV?$item->NgoaiTKV:0;

            $sum['total']['TongSo']      += $item->TongSo?$item->TongSo:0;
            $sum['total']['TuLam']       += $item->TuLam?$item->TuLam:0;
            $sum['total']['TrongCongTy'] += $item->TrongCongTy?$item->TrongCongTy:0;
            $sum['total']['TrongTKV']    += $item->TrongTKV?$item->TrongTKV:0;
            $sum['total']['NgoaiTKV']    += $item->NgoaiTKV?$item->NgoaiTKV:0;
        }
        $this->html->sum        = $sum;
        $this->html->subTitle   = $subTitle;
        $this->html->loaiBaoTri = $arrLoaiBT;

//        $this->html->ThietBiVanTaiBoTrungDaiTu   = $mPlan->getThietBiTrungDaiTuTheoLoai('VTB');
//        $this->html->ThietBiVanTaiThuyTrungDaiTu = $mPlan->getThietBiTrungDaiTuTheoLoai('VTT');
//        $this->html->ThietBiKhacTrungDaiTu       = $mPlan->getThietBiKhacTrungDaiTu();
//        $this->html->VatKienTrucTrungDaiTu       = $mPlan->getVatKienTrucTrungDaiTu();
//        $this->html->ThietBiVanTaiBoSuaChua      = $mPlan->getThietBiSuaChuaThuongXuyenTheoLoai('VTB');
//        $this->html->ThietBiVanTaiThuySuaChua    = $mPlan->getThietBiSuaChuaThuongXuyenTheoLoai('VTT');
//        $this->html->ThietBiKhacSuaChua          = $mPlan->getThietBiKhacSuaChua();
//        $this->html->VatKienTrucSuaChua          = $mPlan->getVatKienTrucSuaChua();

    }
}
