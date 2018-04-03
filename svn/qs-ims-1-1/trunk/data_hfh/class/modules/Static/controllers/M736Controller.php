<?php

class Static_M736Controller extends Qss_Lib_Controller
{
    public function init()
    {

        $this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
        parent::init();
    }

    public function indexAction()
    {

    }

    public function showAction()
    {
        $mGPlan   = new Qss_Model_Maintenance_GeneralPlans();
        $mCommon  = new Qss_Model_Extra_Extra();
        $year     = $this->params->requests->getParam('year', date('Y'));
        $date     = $this->params->requests->getParam('date', '');
        $date     = ($date != '')?date('d/m/Y', strtotime($date)):'';
        $mEquip   = new Qss_Model_Maintenance_Equipment();
        $location = $this->params->requests->getParam('location', 0);
        $group    = $this->params->requests->getParam('group', 0);
        $type     = $this->params->requests->getParam('type', 0);
        $oLoc     = $mCommon->getTableFetchOne('OKhuVuc', array('IOID'=>$location));
        $start    = date($year.'-01-01');
        $end      = date($year.'-12-31');

        $this->html->report = $mGPlan->getDetail($start, $end, $location, $type, $group);
        $this->html->date   = $date;
        $this->html->year   = $year;
        $this->html->location = mb_strtoupper(@$oLoc->Ten, 'UTF-8');
    }

    public function excelAction()
    {
        header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-disposition: attachment; filename=\"Kế hoạch bảo dưỡng.xlsx\"");

        $mGPlan   = new Qss_Model_Maintenance_GeneralPlans();
        $mCommon  = new Qss_Model_Extra_Extra();
        $mEquip   = new Qss_Model_Maintenance_Equipment();
        $year     = $this->params->requests->getParam('year', date('Y'));
        $date     = $this->params->requests->getParam('date', '');
        $date     = ($date != '')?date('d/m/Y', strtotime($date)):'';
        $location = $this->params->requests->getParam('location', 0);
        $group    = $this->params->requests->getParam('group', 0);
        $type     = $this->params->requests->getParam('type', 0);
        $oLoc     = $mCommon->getTableFetchOne('OKhuVuc', array('IOID'=>$location));
        $start    = date($year.'-01-01');
        $end      = date($year.'-12-31');

//        $report = $this->getPlans(
//            Qss_Lib_Date::displaytomysql($start), Qss_Lib_Date::displaytomysql($end), $location, $group, $type);

        $report  = $mGPlan->getDetail($start, $end, $location, $type, $group);
        $mStart  = date_create(date('d-m-Y'));
        $stt = 0;
        $row        = 10;

        $file       = new Qss_Model_Excel(Qss_Lib_System::getTemplateFile('M736', 'HFH_KeHoachBaoDuong.xlsx'), true);
        $main       = new stdClass();

        $main->m1   = $year;
        $main->m2   = $date;
        $main->m3   = mb_strtoupper(@$oLoc->Ten, 'UTF-8');

        $file->init(array('m'=>$main));

        foreach($report as $item)
        {
            $data    = new stdClass();
            $data->a = ++$stt;
            $data->b = $item->TenThietBi;
            $data->c = $item->MaThietBi;
            $data->d = $item->MaTaiSan;
            $data->e = @$item->DoiTac;
            $data->f = @$item->DoiTac?'':'X';
            $data->g = @$item->SoLanTrenNam;
            $data->h = @$item->DuKienThucHien;
            $data->i = @$item->NgayBaoDuongThucTe;;

            $file->newGridRow(array('s'=>$data), $row, 9);
            $row++;
        }

        $row++;

        $file->setCellValue('A'.$row, '                            GIÁM ĐỐC');
        $file->setStyles('A'.$row, '', '', true);
        $file->setCellValue('C'.$row, '                            T/P BẢO DƯỠNG');
        $file->setStyles('C'.$row, '', '', true);
        $file->setCellValue('G'.$row, '                              NGƯỜI LẬP BIỂU');
        $file->setStyles('G'.$row, '', '', true);

        $file->removeRow(9);

        $file->save();
        die();
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

//    public function getPlans($start, $end, $location, $group, $type)
//    {
//        $mStart =  date_create($start);
//        $mEnd   =  date_create($end);
//        $mPlan  = new Qss_Model_Maintenance_Plan();
//        $print  = array();
//
//        // $plans  = $mPlan->getPlansOfEquips();
//        //foreach ($plans as $item)
//
//        while($mStart <= $mEnd)
//        {
//            $date  = $mStart->format('Y-m-d');
//            $plans = $mPlan->getAllPlansByDate($date, $location, 0, 0, $group, $type);
//
//            foreach($plans as $item)
//            {
//                $key = (int)$item->Ref_MaKhuVuc.'-'.(int)$item->Ref_MaThietBi.'-'.(int)$item->Ref_BoPhan.'-'.(int)$item->Ref_LoaiBaoTri;
//
//                if(!isset($print[$key]))
//                {
//                    $print[$key] = $item;
//                    $print[$key]->Dem = 0;
//                    $print[$key]->CacNgayBaoTri = '';
//                    $print[$key]->CacNgayBaoTriThucTe = '';
//                }
//
//                $print[$key]->CacNgayBaoTri        .= ($print[$key]->CacNgayBaoTri?'<br/> ':'').$mStart->format('d-m-Y');
//                $print[$key]->CacNgayBaoTriThucTe  .= ($print[$key]->CacNgayBaoTriThucTe?'<br/> ':'').Qss_Lib_Date::mysqltodisplay($item->NgayBatDauBaoTri);
//
//                ++$print[$key]->Dem;
//            }
//
//            $mStart = Qss_Lib_Date::add_date($mStart, 1);
//        }
//
//        return $print;
//    }
}

?>