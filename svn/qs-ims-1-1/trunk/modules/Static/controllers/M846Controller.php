<?php
class Static_M846Controller extends Qss_Lib_Controller
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
        $this->html->report = ($start && $end)?$mWorkorder->getWorkOrderHistory($mStart, $mEnd, $location, $group, $type, $equip):array();
    }

    public function excelAction()
    {
        header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-disposition: attachment; filename=\"Phiếu theo dõi bảo dưỡng.xlsx\"");

        $start    = $this->params->requests->getParam('start', '');
        $mStart   = $start?Qss_Lib_Date::mysqltodisplay($start):'';
        $end      = $this->params->requests->getParam('end', '');
        $mEnd     = $end?Qss_Lib_Date::mysqltodisplay($end):'';
        $location = $this->params->requests->getParam('location', 0);
        $group    = $this->params->requests->getParam('group', 0);
        $type     = $this->params->requests->getParam('type', 0);

        $row        = 6;
        $mWorkorder = new Qss_Model_Maintenance_Workorder();
        $report     = ($start && $end)?$mWorkorder->getWorkOrderHistory($mStart, $mEnd, $location, $group, $type):array();
        $file       = new Qss_Model_Excel(Qss_Lib_System::getTemplateFile('M846', 'PhieuTheoDoiBaoDuong.xlsx'));
        $main       = new stdClass();

        $main->m1 = @Qss_Lib_System::getUpperCaseReportTitle('M846');

        $file->init(array('m' => $main));

        $stt = 0;

        foreach($report as $item)
        {
            $data      = new stdClass();
            $data->a   = ++$stt;
            $data->b   = $item->SoPhieu;
            $data->c   = $item->NhomThietBi;
            $data->d   = $item->TenKhuVuc;
            $data->e   = $item->TenThietBi;
            $data->f   = $item->MaThietBi;
            $data->g   = $item->MoTa;
            $data->h   = $item->NguoiThucHien;
            $data->i   = $item->NgayYeuCau?Qss_Lib_Date::mysqltodisplay($item->NgayYeuCau):'';
            $data->j   = $item->NgayBatDau?Qss_Lib_Date::mysqltodisplay($item->NgayBatDau):'';
            $data->k   = $item->Ngay?Qss_Lib_Date::mysqltodisplay($item->Ngay):'';
            $data->l   = $item->Step;
            $data->m   = $item->GhiChu;

            $file->newGridRow(array('s'=>$data), $row, 5);
            $row++;
        }

        $file->removeRow(5);

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