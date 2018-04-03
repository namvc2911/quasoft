<?php
class Static_M847Controller extends Qss_Lib_Controller
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
        $start    = $this->params->requests->getParam('start', '');
        $mStart   = $start?Qss_Lib_Date::displaytomysql($start):'';
        $end      = $this->params->requests->getParam('end', '');
        $mEnd     = $end?Qss_Lib_Date::displaytomysql($end):'';
        $location = $this->params->requests->getParam('location', 0);
        $group    = $this->params->requests->getParam('group', 0);
        $type     = $this->params->requests->getParam('type', 0);
        $equip    = $this->params->requests->getParam('equip', 0);

        $mWorkorder = new Qss_Model_Maintenance_Workorder();
        $this->html->report = ($start && $end)?$mWorkorder->getMovingHistoryOfComponets($mStart, $mEnd, $location, $group, $type, $equip):array();
    }

    public function excelAction()
    {
        header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-disposition: attachment; filename=\"Lịch sử đổi phụ tùng vật tư.xlsx\"");

        $start    = $this->params->requests->getParam('start', '');
        $mStart   = $start?Qss_Lib_Date::displaytomysql($start):'';
        $end      = $this->params->requests->getParam('end', '');
        $mEnd     = $end?Qss_Lib_Date::displaytomysql($end):'';
        $location = $this->params->requests->getParam('location', 0);
        $group    = $this->params->requests->getParam('group', 0);
        $type     = $this->params->requests->getParam('type', 0);
        $equip    = $this->params->requests->getParam('equip', 0);

        $mWorkorder = new Qss_Model_Maintenance_Workorder();
        $report = ($start && $end)?$mWorkorder->getMovingHistoryOfComponets($mStart, $mEnd, $location, $group, $type, $equip):array();

        $row        = 7;
        $file       = new Qss_Model_Excel(Qss_Lib_System::getTemplateFile('M847', 'AnPhat_LichSuDoiPhuTungVatTu.xlsx'));
        $main       = new stdClass();

        $main->m1 = @Qss_Lib_System::getUpperCaseReportTitle('M847');

        $file->init(array('m' => $main));

        $stt = 0;

        foreach($report as $item)
        {

                $data      = new stdClass();
                $data->a   = ++$stt;
                $data->b   = $item->MaThietBiChuyen;
                $data->c   = $item->MaThietBiNhan;
                $data->d   = $item->NgayChuyen?Qss_Lib_Date::mysqltodisplay($item->NgayChuyen):'';
                $data->e   = $item->SoPhieu;
                $data->f   = $item->ViTriNhan;
                $data->g   = $item->TenVatTu;
                $data->h   = $item->TenCongViec;

                $file->newGridRow(array('s'=>$data), $row, 6);
                $row++;

        }

        $file->removeRow(6);

        $file->save();
        die();
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    private function getData($partner = 0)
    {
        $mPartner = new Qss_Model_Master_Partner();
        $report   = $mPartner->getContactsOfPartners($partner);

        foreach ($report as $item)
        {

        }
    }
}

?>