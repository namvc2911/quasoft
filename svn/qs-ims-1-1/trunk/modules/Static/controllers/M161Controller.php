<?php
class Static_M161Controller extends Qss_Lib_Controller
{
    protected $_report;

    protected $_rowCount;

    protected $_common;

    public function init()
    {
        $this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
    	parent::init();
        $this->_common = new Qss_Model_Extra_Extra();

        $this->headScript($this->params->requests->getBasePath() . '/js/report-list.js');
        $this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');

        $this->_model     = new Qss_Model_Maintenance_Workorder();
    }


    public function indexAction()
    {

    }

    public function showAction()
    {
        $start       = $this->params->requests->getParam('start', '');
        $end         = $this->params->requests->getParam('end', '');
        $employee    = $this->params->requests->getParam('employee', 0);
        $equip       = $this->params->requests->getParam('equipment', 0);

        $model       = new Qss_Model_Maintenance_Workorder_Tasks();
        $count       = array();
        $oldNgay     = '';
        $oldIFID     = '';
        $data        =   $model->getTasksByEmployee(Qss_Lib_Date::displaytomysql($start), Qss_Lib_Date::displaytomysql($end), $employee, $equip);

        foreach($data as $item)
        {
            if(!isset($count[$item->NgayBatDau]))
            {
                $count[$item->NgayBatDau] = 0;
            }

            if($oldNgay != $item->NgayBatDau || $oldIFID != $item->IFID_M759)
            {
                $count[$item->NgayBatDau]++;
            }

            if($item->RefCongViec)
            {
                $count[$item->NgayBatDau]++;
            }

            $oldNgay  = $item->NgayBatDau;
            $oldIFID  = $item->IFID_M759;
        }

        $this->html->date     = $start;
        $this->html->enddate  = $end;
        $this->html->report   = $data;
        $this->html->rowCount = $count;
    }


    public function show2Action()
    {

        $start       = $this->params->requests->getParam('start', '');
        $end         = $this->params->requests->getParam('end', '');
        $employee    = $this->params->requests->getParam('employee', 0);
        $strEmployee = $this->params->requests->getParam('employee_tag', '');
        $equip       = $this->params->requests->getParam('equipment', 0);

        $this->getWorkOrder(Qss_Lib_Date::displaytomysql($start), Qss_Lib_Date::displaytomysql($end), $employee, $equip);

        // Lấy thông tin đánh giá
        $systemFieldModel = new Qss_Model_System_Field();
        $systemFieldModel->init('OCongViecBTPBT','DanhGia');

        $this->html->date     = $start;
        $this->html->enddate  = $end;
        $this->html->report   = $this->_report;
        $this->html->rowCount = $this->_rowCount;
        $this->html->employee = $strEmployee;
        $this->html->reviewField = $systemFieldModel->getJsonRegx();
    }

    private function getWorkOrder($startdate,$enddate,$employee, $equip)
    {
        $start = date_create($startdate);
        $end = date_create($enddate);
        $i=0;

        while ($start <= $end && $i <= 30)
        {
            $row = 0;
            if(!isset($this->_report[$start->format('d-m-Y')]))
            {
                $this->_report[$start->format('d-m-Y')] = array();
            }
            $this->_report[$start->format('d-m-Y')] = array_merge($this->_report[$start->format('d-m-Y')],
                $this->getMaintainDataForMaintainOrder($start->format('Y-m-d'), $employee, $equip, $row));

            if(!isset($this->_rowCount[$start->format('d-m-Y')]))
            {
                $this->_rowCount[$start->format('d-m-Y')] = 0;
            }
            $this->_rowCount[$start->format('d-m-Y')] += $row;
            $start = Qss_Lib_Date::add_date($start,1);
            $i++;
        }
    }

    protected function getMaintainDataForMaintainOrder($date, $employee, $equip,&$row = 0)
    {
        $orderModel   = new Qss_Model_Maintenance_Workorder();
        $orders       = $orderModel->getOrdersByEmployee($date, $date, $employee, $equip);
        $retval       = array();
        $ordersIFIDArr = array();
        $oldIFID      = '';
        $oldPosition  = '';
        $mOldIFID     = ''; // Material
        $mOldPosition = ''; // Material
        $oOldIFID     = '';
        $oOldPosition = '';
        $tIndex       = 0;
        $oIndex       = 0;
        $mIndex       = 0;

        // ===== INIT MAINT ORDER ARRAY =====
        foreach ($orders as $item)
        {
            $row++;
            $ordersIFIDArr[]             = $item->IFID_M759;
            $tempInfo                    = array();
            $tempInfo['IFID']            = $item->IFID_M759;
            $tempInfo['DocNo']           = @$item->SoPhieu;
            $tempInfo['Code']            = $item->MaThietBi;
            $tempInfo['Name']            = $item->TenThietBi;
            $tempInfo['Type']            = $item->LoaiBaoTri;
            $tempInfo['Shift']           = $item->Ca;
            $tempInfo['WorkCenter']      = $item->TenDVBT;
            $tempInfo['Employee']        = $item->NguoiThucHien;
            $tempInfo['Line']            = 0;
            $tempInfo['Status']          = $item->Name;
            $tempInfo['Review']          = $item->DanhGia;
            $retval[$item->IFID_M759]['Info'] = $tempInfo;
            $retval[$item->IFID_M759]['Component'] = array();
        }
        // ===== GET TASKS AND MATERIALS =====
        $tasks        = $orderModel->getTasksByIFID($ordersIFIDArr, $employee);
        $materials    = $orderModel->getMaterialsByIFIDGroupByIFID($ordersIFIDArr);
        // 		$outsources   = $orderModel->getOutsourcesByIFID($ordersIFIDArr);

        // ===== ADD TASKS TO MAINT ARRAY  =====

        foreach($tasks as $item)
        {
            $row++;
            if($oldIFID != $item->IFID || $oldPosition != $item->Ref_ViTri)
            {
                $tIndex = 0;
            }
            $oldIFID     = $item->IFID;
            $oldPosition = $item->Ref_ViTri;

            if(!isset($retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]))
            {
                $tempCom            = array();
                $tempCom['BoPhan']  = $item->BoPhan;
                $tempCom['ViTri']   = $item->ViTri;
                $tempCom['BoPhanCha']  = $item->BoPhanCha;
                $tempCom['RowSpan'] = 0;
                $retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri] = $tempCom;
            }

            $temp            = array();
            $temp['MoTa']    = $item->MoTaCongViec;
            $temp['GhiChu']  = $item->GhiChuCongViec;
            $temp['DanhGia'] = $item->DanhGia;
            $temp['NguoiThucHien'] = $item->NguoiThucHien;
            $temp['Dat']     = $item->Dat;

            $retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]['Work'][$tIndex] = $temp;
            $tIndex++;
            $retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]['TIndex']  = $tIndex;
            $retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]['RowSpan'] = $tIndex;
        }

        // ===== ADD MATERIALS TO MAINT ORDER ARRAY  =====
        foreach($materials as $item)
        {
            if($mOldIFID != $item->IFID || $mOldPosition != $item->Ref_ViTri)
            {
                $mIndex = 0;
            }
            $mOldIFID     = $item->IFID;
            $mOldPosition = $item->Ref_ViTri;

            if(!isset($retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]))
            {
                $row++;
                $tempCom = array();
                $tempCom['BoPhan']    = $item->BoPhan;
                $tempCom['ViTri']     = $item->ViTri;
                $tempCom['RowSpan']   = 0;
                $retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri] = $tempCom;
            }

            $temp           = array();
            $temp['VatTu']  = $item->VatTu;

            $retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]['Material'][$mIndex] = $temp;
            $mIndex++;
        }
        return $retval;
    }
}
