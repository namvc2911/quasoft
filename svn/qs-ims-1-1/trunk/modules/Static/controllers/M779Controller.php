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
        $mSysField = new Qss_Model_System_Field();
        $mCommon   = new Qss_Model_Extra_Extra();
        $date      = $this->params->requests->getParam('date', '');
        $enddate   = $this->params->requests->getParam('enddate', '');
        $location  = $this->params->requests->getParam('location', -1);
        $maintype  = $this->params->requests->getParam('maintype', array(0));
        $workcenter= $this->params->requests->getParam('workcenter', array(0));
        $equip     = $this->params->requests->getParam('equipment', 0);
        $locName   = $mCommon->getTableFetchOne('OKhuVuc', array('IOID' => $location));

        $this->getWorkOrder($date, $enddate, $location, $equip, $workcenter); // Lấy phiếu bảo trì
        if(Qss_Lib_System::fieldActive('OCongViecBTPBT','DanhGia')) { // Init json danh gia
            $mSysField->init('OCongViecBTPBT','DanhGia');
        }

        $this->html->date        = $date;
        $this->html->enddate     = $enddate;
        $this->html->loc         = $location;
        $this->html->locName     = $locName ? "{$locName->MaKhuVuc} - {$locName->Ten}" : '';
        $this->html->reviewField = $mSysField->getJsonRegx();
        $this->html->report      = $this->_report;
        $this->html->rowCount    = $this->_rowCount;
        // echo '<Pre>'; print_r($this->_report); die;
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
        $orderModel   = new Qss_Model_Maintenance_Workorder();
        $orders       = $orderModel->getOrders($date, $date, $location, 0, array(), 0, 0, $equipIOID, $workcenter);

        $retval       = array();
        $ordersIFIDArr = array();
        $oldIFID      = '';
        $oldPosition  = '';
        $mOldIFID     = ''; // Material
        $mOldPosition = ''; // Material
        $tIndex       = 0;
        $mIndex       = 0;

        // ===== INIT MAINT ORDER ARRAY =====
        foreach ($orders as $item)
        {
            $row++;
            $ordersIFIDArr[]        = $item->IFID_M759;
            $tempInfo               = array();
            $tempInfo['IFID']       = $item->IFID_M759;
            $tempInfo['DocNo']      = @$item->SoPhieu;
            $tempInfo['Code']       = $item->MaThietBi;
            $tempInfo['Name']       = $item->TenThietBi;
            $tempInfo['Type']       = $item->LoaiBaoTri;
            $tempInfo['WorkCenter'] = $item->TenDVBT;
            $tempInfo['Employee']   = $item->NguoiThucHien;
            $tempInfo['Task']       = $item->MoTa;
            $tempInfo['Line']       = 0;
            $tempInfo['Status']     = $item->Name;
            $tempInfo['Review']     = '';

            if(Qss_Lib_System::fieldActive('OPhieuBaoTri','DanhGia'))
            {
                $tempInfo['Review']          = $item->DanhGia;
            }

            $retval[$item->IFID_M759]['Info'] = $tempInfo;
            $retval[$item->IFID_M759]['Component'] = array();
        }
        // ===== GET TASKS AND MATERIALS =====
        $tasks     = $orderModel->getTasksByIFID($ordersIFIDArr);
        $materials = $orderModel->getMaterialsByIFIDGroupByIFID($ordersIFIDArr);

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
            $temp['MoTa']    = $item->MoTa;
            $temp['GhiChu']  = $item->GhiChu;
            $temp['DanhGia'] = $item->DanhGia;
            $temp['NguoiThucHien'] = $item->NguoiThucHien;
            $temp['Dat']     = '';

            if(Qss_Lib_System::fieldActive('OCongViecBTPBT', 'Dat'))
            {
                $temp['Dat']     = $item->Dat;
            }

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