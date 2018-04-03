<?php
class Static_M779Controller extends Qss_Lib_Controller
{
    protected $_report;
    protected $_rowCount;

    public function init() {
        $this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
        parent::init();
        $this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
    }

    public function indexAction() {

    }

    public function showAction() {
        $mM759         = new Qss_Model_M779_Viwasupco();
        $mCommon       = new Qss_Model_Extra_Extra();
        $templateNo    = $this->params->requests->getParam('title', 0);
        $date          = $this->params->requests->getParam('date', '');
        $enddate       = $this->params->requests->getParam('enddate', '');
        $location      = $this->params->requests->getParam('location', -1);
        $workcenter    = $this->params->requests->getParam('workcenter', array(0));
        $equip         = $this->params->requests->getParam('equipment', 0);
        $locName       = $mCommon->getTableFetchOne('OKhuVuc', array('IOID' => $location));
        $objWorkorders = $mM759->getDetailWorkorderForM779(
            Qss_Lib_Date::displaytomysql($date), Qss_Lib_Date::displaytomysql($enddate),$location, $equip, $workcenter, true
        ); // #add

        // echo '<pre>'; print_r($objWorkorders); die;
        $inOneMonth    = '';

        if(
            ( (int)date('d', strtotime($date)) == 1 )
            && ( (int)date('d', strtotime($enddate)) == (int)date('t', strtotime($enddate)) )
            && ( (int)date('m', strtotime($date)) == (int)date('m', strtotime($enddate)) )
        ) {
            $inOneMonth = date('m-Y', strtotime($date));
        }

        $this->html->date        = $date;
        $this->html->enddate     = $enddate;
        $this->html->loc         = $location;
        $this->html->locName     = $locName ? "{$locName->MaKhuVuc} - {$locName->Ten}" : '';
        $this->html->report      = $objWorkorders;
        $this->html->rowCount    = $this->_rowCount;
        $this->html->templateNo  = $templateNo;
        $this->html->inOneMonth  = $inOneMonth;
        // echo '<Pre>'; print_r($this->_report); die;
    }

    private function _getWorkOrder($startdate, $enddate,$location, $equip, $workcenter) {
        // Lấy toàn bộ phiếu bảo trì trong khoảng thời gian
        // Sau đó nhét hết phiếu bảo trì vào một mảng với key là IFID của phiếu
        // Lấy hết công việc theo IFID đã có
        // Lấy hết vật tư theo IFID đã có

        $mM759         = new Qss_Model_M779_Viwasupco();
        $objWorkorders = $mM759->getDetailWorkorderForM779($startdate, $enddate,$location, $equip, $workcenter); // #add
        $oldSoPhieu    = '';

        foreach($objWorkorders as $item) {
            if($item->SoPhieu != $oldSoPhieu && in_array($item->Status, array(1,2)) ) {

            }
        }





    }

    private function getWorkOrder($startdate, $enddate,$location, $equip, $workcenter)
    {
        $start = date_create($startdate);
        $end   = date_create($enddate);
        $i     = 0;

        while ($start <= $end)
        {
            $row = 0;
            if(!isset($this->_report[$start->format('d-m-Y')]))
            {
                $this->_report[$start->format('d-m-Y')] = array();
            }
            $this->_report[$start->format('d-m-Y')] = array_merge($this->_report[$start->format('d-m-Y')],
                $this->getMaintainDataForMaintainOrder(
                    $start->format('Y-m-d')
                    , $location
                    , $equip
                    , $workcenter
                    , $row ));
            if(!isset($this->_rowCount[$start->format('d-m-Y')]))
            {
                $this->_rowCount[$start->format('d-m-Y')] = 0;
            }
            $this->_rowCount[$start->format('d-m-Y')] += $row;
            $start = Qss_Lib_Date::add_date($start,1);
            $i++;
        }
    }

    protected function getMaintainDataForMaintainOrder($date, $location, $equipIOID, $workcenter,&$row = 0 )
    {
        $mMaterial     = new Qss_Model_M759_Material();
        $orderModel    = new Qss_Model_Maintenance_Workorder();
        $orders        = $orderModel->getOrders($date, $date, $location, 0, array(), 0, 0, $equipIOID, $workcenter);
        $retval        = array();
        $ordersIFIDArr = array();
        $oldIFID       = '';
        $oldPosition   = '';
        $mOldIFID      = ''; // Material
        $mOldPosition  = ''; // Material
        $tIndex        = 0;
        $mIndex        = 0;

        // ===== INIT MAINT ORDER ARRAY =====
        foreach ($orders as $item) {
            $row++;
            $ordersIFIDArr[]                       = $item->IFID_M759;
            $retval[$item->IFID_M759]['Info']      = $item;
            $retval[$item->IFID_M759]['Component'] = array();
        }

        // ===== GET TASKS AND MATERIALS =====
        $tasks         = $orderModel->getTasksByIFID($ordersIFIDArr);
        $materials     = $mMaterial->getMaterialsByIFIDs($ordersIFIDArr);

        // ===== ADD TASKS TO MAINT ARRAY  =====
        foreach($tasks as $item) {
            $row++;
            if($oldIFID != $item->IFID || $oldPosition != $item->Ref_ViTri) {
                $tIndex = 0;
            }

            $oldIFID     = $item->IFID;
            $oldPosition = $item->Ref_ViTri;

            if(!isset($retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri])) {
                $tempCom               = array();
                $tempCom['BoPhan']     = $item->BoPhan;
                $tempCom['ViTri']      = $item->ViTri;
                $tempCom['BoPhanCha']  = $item->BoPhanCha;
                $tempCom['RowSpan']    = 0;
                $retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri] = $tempCom;
            }

            $retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]['Work'][$tIndex] = $item;
            $tIndex++;

            $retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]['TIndex']  = $tIndex;
            $retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]['RowSpan'] = $tIndex;
        }

        // ===== ADD MATERIALS TO MAINT ORDER ARRAY  =====
        foreach($materials as $item) {
            if($mOldIFID != $item->IFID_M759 || $mOldPosition != $item->Ref_ViTri) {
                $mIndex = 0;
            }

            if(!isset($retval[$item->IFID_M759]['Component'][@(int)$item->Ref_ViTri])) {
                $row++;
                $tempCom              = array();
                $tempCom['BoPhan']    = $item->BoPhan;
                $tempCom['ViTri']     = $item->ViTri;
                $tempCom['RowSpan']   = 0;
                $retval[$item->IFID_M759]['Component'][@(int)$item->Ref_ViTri] = $tempCom;
            }

            $retval[$item->IFID_M759]['Component'][@(int)$item->Ref_ViTri]['Material'][$mIndex] = $item;
            $mIndex++;

            $mOldIFID     = $item->IFID_M759;
            $mOldPosition = $item->Ref_ViTri;
        }

        // echo '<pre>'; print_r($retval); die;
        return $retval;
    }

}