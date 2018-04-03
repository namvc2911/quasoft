<?php

/**
 * M843Controller.php
 *
 * Báo cáo hiệu chuẩn kiểm định
 *
 * @category   Static
 * @author     Thinh Tuan
 */

class Static_M843Controller extends Qss_Lib_Controller
{
    public function init()
    {
        $this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
        parent::init();
        $this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
    }

    public function indexAction()
    {

    }

    public function showAction()
    {
        $mCal     = new Qss_Model_Hfhcalibration();
        $mEquip   = new Qss_Model_Maintenance_Equipment();
        $year     = $this->params->requests->getParam('year', date('Y'));
        $location = $this->params->requests->getParam('location', 0);
        $group    = $this->params->requests->getParam('group', 0);
        $type     = $this->params->requests->getParam('type', 0);
        $eq       = $this->params->requests->getParam('eq', 0);
        $start    = date($year.'-01-01');
        $end      = date($year.'-12-31');

        $this->html->report = $mCal->getCalibrations($start, $end,  $location, $type, $group, $eq);
        $this->html->year   = $year;
    }

    public function excelAction()
    {
        header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-disposition: attachment; filename=\"Danh mục thiết bị kiểm định.xlsx\"");

        //$mCal     = new Qss_Model_Maintenance_Equip_Calibration();
        $mCal     = new Qss_Model_Hfhcalibration();
        $mEquip   = new Qss_Model_Maintenance_Equipment();
        $year     = $this->params->requests->getParam('year', date('Y'));
        $location = $this->params->requests->getParam('location', 0);
        $group    = $this->params->requests->getParam('group', 0);
        $type     = $this->params->requests->getParam('type', 0);
        $eq       = $this->params->requests->getParam('eq', 0);
        $start    = date($year.'-01-01');
        $end      = date($year.'-12-31');

        $report   = $mCal->getCalibrations($start, $end, $location, $type, $group, $eq);
       //echo '<pre>'; print_r($report); die;
        $stt      = 0;
        $row      = 9;
        $merge    = array();
        $file     = new Qss_Model_Excel(Qss_Lib_System::getTemplateFile('M843', 'HFH_DanhMucThietBiKiemDinh.xlsx'));
        $main     = new stdClass();

        $main->m1 = $year;

        $file->init(array('m'=>$main));

        foreach($report as $item)
        {
            $data      = new stdClass();
            $data->a   = ++$stt;
            $data->b   = @$item->MaThietBi;
            $data->c   = @$item->TenThietBi;
            $data->d   = @$item->TenKhuVuc;
            $data->e   = @$item->SoHopDong;
            $data->f   = @$item->TenDoiTac;
            $data->g   = @$item->Ngay?Qss_Lib_Date::mysqltodisplay($item->Ngay):'';
            $data->h   = @$item->NgayKiemDinhTiepTheo?Qss_Lib_Date::mysqltodisplay($item->NgayKiemDinhTiepTheo):'';;

            $file->newGridRow(array('s'=>$data), $row, 8);
            $row++;
        }

        $file->removeRow(8);

        $file->save();
        die();
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

//    private function getData(
//        $start
//        , $end
//        , $location
//        , $type
//        , $group
//        , $eq)
//    {
//        $retval   = array();
//        $model    = new Qss_Model_Maintenance_Equip_Calibration();
//        $report   = $model->getCalibrations(
//            Qss_Lib_Date::displaytomysql($start)
//            , Qss_Lib_Date::displaytomysql($end)
//            , $location
//            , $type
//            , $group
//            , $eq);
//
//        foreach ($report as $item)
//        {
//            $key = $item->Ngay.'-'.$item->NgayKiemDinhTiepTheo;
//            $retval[$key][] = $item;
//        }
//
//        // echo '<pre>'; print_r($retval); die;
//        return $retval;
//    }
}

?>