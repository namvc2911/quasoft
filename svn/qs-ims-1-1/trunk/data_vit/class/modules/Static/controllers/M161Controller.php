<?php
class Static_M161Controller extends Qss_Lib_Controller {
    const M161_LAST_EMPTY_LINE = 5;

    public function init() {
        $this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
        parent::init();
        $this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
    }

    public function indexAction() {

    }

    public function showAction() {
        $orderModel  = new Qss_Model_Maintenance_Workorder();
        $start       = $this->params->requests->getParam('start', '');
        $end         = $this->params->requests->getParam('end', '');
        $employee    = $this->params->requests->getParam('employee', 0);
        $strEmployee = $this->params->requests->getParam('employee_tag', '');
        $oldDate     = '';
        $oldDocNo    = '';
        $rowCount    = array();

        $data        = $orderModel->getWorkorderDetailsGroupByTask(
            Qss_Lib_Date::displaytomysql($start)
            , Qss_Lib_Date::displaytomysql($end)
            , $employee
        );

        // Số dòng ban đầu của mỗi ngày bằng số dòng trắng điền thêm
        // Mỗi dòng số phiêu hoặc mỗi dòng công việc sẽ công thêm 1 dòng
        foreach ($data as $item) {
            if($oldDate != $item->NgayBatDauDuKien) {
                $rowCount[$item->NgayBatDauDuKien] = self::M161_LAST_EMPTY_LINE;
            }

            if($oldDocNo != $item->SoPhieu) { // Cộng thêm 1 khi sang phiếu bảo trì khác cùng ngày
                $rowCount[$item->NgayBatDauDuKien]++;
            }

            if($item->CongViecIOID) { // Cộng thêm 1 khi sang công việc khác cùng ngày
                $rowCount[$item->NgayBatDauDuKien]++;
            }

            $oldDate  = $item->NgayBatDauDuKien;
            $oldDocNo = $item->SoPhieu;
        }

        $this->html->date        = $start;
        $this->html->enddate     = $end;
        $this->html->report      = $data;
        $this->html->rowCount    = $rowCount;
        $this->html->employee    = $strEmployee;
    }
}