<?php
/**
 * Class Static_M405Controller
 * Xử lý kế hoạch mua sắm
 * Purchase request process
 */
class Static_M778Controller extends Qss_Lib_Controller
{


    public function init()
    {
        $this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
        parent::init();
        $this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
        $this->_common = new Qss_Model_Extra_Extra();

        $this->headScript($this->params->requests->getBasePath() . '/js/report-list.js');
        $this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');

        $this->_model     = new Qss_Model_Maintenance_Workorder();
    }

    /**
     * Lý lịch thiết bị
     */
    public function indexAction()
    {
        $start      = $this->params->requests->getParam('start', '');
        $end        = $this->params->requests->getParam('end', '');
        $refEq      = $this->params->requests->getParam('eq', 0);
        $eq         = $this->params->requests->getParam('eq_tag', '');

        $this->html->start = $start;
        $this->html->end   = $end;
        $this->html->eq    = $refEq;
        $this->html->eq_tag = $eq;
    }
    public function showAction()
    {
        // echo '<pre>'; print_r($this->params->requests->getParams()); die;
        $common   = new Qss_Model_Extra_Extra();
        $orderModel = new Qss_Model_Maintenance_Workorder();


        $refEq   = $this->params->requests->getParam('eq', 0);
        $start  = $this->params->requests->getParam('start', '');
        $end    = $this->params->requests->getParam('end', '');
        $mStart = ($start != '')?Qss_Lib_Date::displaytomysql($start):'';
        $mEnd   = ($end != '')?Qss_Lib_Date::displaytomysql($end):'';

        $this->html->report = $orderModel->getOrderByEquip($refEq, $mStart, $mEnd);
        $this->html->eq     = $common->getTableFetchOne('ODanhSachThietBi', array('IOID' => $refEq));
    }

    public function excelAction()
    {
        header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-disposition: attachment; filename=\"Lý lịch thiết bị.xlsx\"");


        $common     = new Qss_Model_Extra_Extra();
        $orderModel = new Qss_Model_Maintenance_Workorder();
        $start      = $this->params->requests->getParam('start', '');
        $end        = $this->params->requests->getParam('end', '');
        $mStart     = ($start != '')?Qss_Lib_Date::displaytomysql($start):'';
        $mEnd       = ($end != '')?Qss_Lib_Date::displaytomysql($end):'';
        $refEq      = $this->params->requests->getParam('eq', 0);
        $row        = 11;

        $report     = $orderModel->getOrderByEquip($refEq, $mStart, $mEnd);
        $eq         = $common->getTableFetchOne('ODanhSachThietBi', array('IOID' => $refEq));
        $file       = new Qss_Model_Excel(Qss_Lib_System::getTemplateFile('M778', 'HFH_LyLichThietBi.xlsx'));
        $main       = new stdClass();

        $main->m1   = $eq->TenThietBi;
        $main->m2   = $eq->XuatXu;
        $main->m3   = $eq->NgayDuaVaoSuDung;
        $main->m4   = $eq->MaThietBi;
        $main->m5   = $eq->MaKhuVuc;
        $main->m6   = $eq->MaTaiSan;
        $file->init(array('m'=>$main));

        foreach($report as $item)
        {
            $data      = new stdClass();
            $data->a   = Qss_Lib_Date::mysqltodisplay($item->NgayBatDau);
            $data->b   = $item->MoTa;
            $data->c   = $item->TinhTrangSauBaoTri;

            $file->newGridRow(array('s'=>$data), $row, 10);
            $row++;
        }

        $file->removeRow(10);
        $file->save();
        die();
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
}