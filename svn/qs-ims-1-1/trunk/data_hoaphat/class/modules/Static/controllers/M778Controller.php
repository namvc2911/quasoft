<?php
class Static_M778Controller extends Qss_Lib_Controller
{


    public function init()
    {
        parent::init();
        $this->_common = new Qss_Model_Extra_Extra();

        $this->headScript($this->params->requests->getBasePath() . '/js/report-list.js');
        $this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');

        $this->_model = new Qss_Model_Maintenance_Workorder();

        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
    }

    public function indexAction()
    {

    }


    // Theo mau cau Hoa Phat
    public function showAction()
    {

        $locModel = new Qss_Model_Maintenance_Location();
        $eqModel = new Qss_Model_Maintenance_Equipment();
        $common = $this->_common;

        $refEq = $this->params->requests->getParam('eq', 0);
        $eq = $this->params->requests->getParam('equipmentStr', '');
        $eqArr = $refEq ? array($refEq) : array();

        $this->html->eq = $common->getTableFetchOne('ODanhSachThietBi', array('IOID' => $refEq));

        $this->html->workcenter = $locModel->getManageDepOfEquip($refEq);
        $this->html->eqParams = $eqModel->getTechnicalParameterValues($eqArr);

        $mCommon = new Qss_Model_Extra_Extra();


        if (Qss_Lib_System::objectInForm('ODacTinhThietBi', 'M705')) {
            $this->html->eqTechnicalParams = $eqModel->getTechnicalParameters($eqArr);
        } else {
            $this->html->eqTechnicalParams = array();
        }

        $this->html->sparepart   = $eqModel->getSparepartOfEquip($refEq);
        $this->html->history2    = $this->getWorkOrderHistoryDetailOfEquipment($refEq);
        $this->html->document    = $eqModel->getDocumentsOfEquip($refEq);
        $this->html->components  = $mCommon->getTableFetchAll('OCauTrucThietBi', array('IFID_M705'=>@(int)$this->html->eq->IFID_M705), array('*'), array('lft'));
    }

    private function getWorkOrderHistoryDetailOfEquipment($refEq)
    {
        $orderModel = new Qss_Model_Maintenance_Workorder();
//        $orderModel->setFilterEqIOIDForGetWorkOrdersFunc($refEq);
//        $orderModel->setOrderByEquipForGetWorkOrdersFunc();
        $orders     = $orderModel->getOrders(
            ''
            ,''
            ,0
            ,0
            ,array()
            ,0
            ,0
            ,$refEq
        );

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
        $arrVatTu     = array();

        // ===== INIT MAINT ORDER ARRAY =====
        foreach ($orders as $item)
        {
            $ordersIFIDArr[]             = $item->IFID_M759;
            $tempInfo                    = array();
            $tempInfo['IFID']            = $item->IFID_M759;
            $tempInfo['DocNo']           = @$item->SoPhieu;
            $tempInfo['Code']            = $item->MaThietBi;
            $tempInfo['Name']            = $item->TenThietBi;
            $tempInfo['Type']            = $item->LoaiBaoTri;
            $tempInfo['TypeCode']        = $item->Loai;
            $tempInfo['Shift']           = $item->Ca;
            $tempInfo['WorkCenter']      = $item->TenDVBT;
            $tempInfo['Employee']        = $item->NguoiThucHien;
            $tempInfo['Line']            = 0;
            $tempInfo['Status']          = $item->Name;
            $tempInfo['StatusNo']        = ($item->StepNo > 0)?$item->StepNo:1;
            $tempInfo['Review']          = $item->DanhGia;
            $tempInfo['ReqDate']         = $item->NgayYeuCau;
            $tempInfo['SDate']           = $item->NgayBatDau;
            $tempInfo['EDate']           = $item->Ngay;
            $tempInfo['Des']             = $item->MoTa;
            $tempInfo['Intervention']    = $item->XuLy;
            $tempInfo['MIndex']          = 0;
            $tempInfo['TIndex']          = 0;
            $tempInfo['NotMat']          = 0;
            $retval[$item->IFID_M759]['Info'] = $tempInfo;
            $retval[$item->IFID_M759]['Component'] = array();
        }

        // ===== GET TASKS AND MATERIALS =====
        $tasks        = $orderModel->getTasksByIFID($ordersIFIDArr);
        $materials    = $orderModel->getMaterialsByIFID($ordersIFIDArr);
        $materials2   = $orderModel->getMaterialsByIFIDOrderByDate($ordersIFIDArr);
        // 		$outsources   = $orderModel->getOutsourcesByIFID($ordersIFIDArr);

        // ===== ADD TASKS TO MAINT ARRAY  =====
        foreach($tasks as $item)
        {
            if($oldIFID != $item->IFID || $oldPosition != $item->Ref_ViTri)
            {
                if(isset($tIndex) && $tIndex && $oldIFID)
                {
                    if(!isset($retval[$oldIFID]['Info']['TIndex']))
                    {
                        $retval[$oldIFID]['Info']['TIndex'] = $tIndex;
                    }
                    else
                    {
                        $retval[$oldIFID]['Info']['TIndex'] += $tIndex;
                    }
                }
                $tIndex = 0;
            }

            $oldIFID     = $item->IFID;
            $oldPosition = $item->Ref_ViTri;

            if(!isset($retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]))
            {
                $tempCom            = array();
                $tempCom['BoPhan']  = $item->BoPhan;
                $tempCom['ViTri']   = $item->ViTri;
                $tempCom['RowSpan'] = 0;
                $retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri] = $tempCom;
            }

            $temp            = array();
            $temp['MoTa']    = $item->MoTa;
            $temp['Ten']     = $item->Ten;
            $temp['NguoiThucHien']= @$item->NguoiThucHien;
            $temp['GhiChu']  = $item->GhiChu;
            $temp['DanhGia'] = $item->DanhGia;
            $temp['IOID']    = $item->IOID;

            $retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]['Work'][$tIndex] = $temp;
            $tIndex++;
            $retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]['TIndex']  = $tIndex;
            $retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]['RowSpan'] = $tIndex;
        }

        if(isset($tIndex) && $tIndex && $oldIFID)
        {
            if(!isset($retval[$oldIFID]['Info']['TIndex']))
            {
                $retval[$oldIFID]['Info']['TIndex'] = $tIndex;
            }
            else
            {
                $retval[$oldIFID]['Info']['TIndex'] += $tIndex;
            }
        }


        // ===== ADD MATERIALS TO MAINT ORDER ARRAY  =====
        foreach($materials as $item)
        {
            if($mOldIFID != $item->IFID || $mOldPosition != $item->Ref_ViTri)
            {
                if(isset($mIndex) && $mIndex && $mOldIFID)
                {
                    if(!isset($retval[$mOldIFID]['Info']['MIndex']))
                    {
                        $retval[$mOldIFID]['Info']['MIndex'] = $mIndex;
                    }
                    else
                    {
                        $retval[$mOldIFID]['Info']['MIndex'] += $mIndex;
                    }
                }
                $mIndex = 0;
            }
            $mOldIFID     = $item->IFID;
            $mOldPosition = $item->Ref_ViTri;

            if(!isset($retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]))
            {
                $tempCom = array();
                $tempCom['BoPhan']    = $item->BoPhan;
                $tempCom['ViTri']     = $item->ViTri;
                $tempCom['RowSpan']   = 0;
                $retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri] = $tempCom;
            }

            $temp                   = array();
            $temp['MaVatTu']        = $item->MaVatTu;
            $temp['TenVatTu']       = $item->TenVatTu;
            $temp['Ngay']           = $item->Ngay;
            $temp['DonViTinh']      = $item->DonViTinh;
            $temp['SoLuong']        = $item->SoLuong;
            $temp['GhiChu']         = $item->GhiChu;
            $temp['DacTinhKyThuat'] = $item->DacTinhKyThuat;
            $temp['GhiChu']         = $item->GhiChu;



            if(isset($retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]['Work']))
            {
                foreach($retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]['Work'] as $tempWork)
                {
                    if($item->Ref_CongViec == $tempWork['IOID']) {
                        $temp['GhiChu'] = $tempWork['GhiChu'];
                    }
                }
            }



            $retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]['Material'][$mIndex] = $temp;
            $mIndex++;
            $retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]['MIndex']  = $mIndex;
        }

        foreach ($materials2 as $item) {
            $retval[$item->IFID]['Material'][] = $item;
        }

        if(isset($mIndex) && $mIndex && $mOldIFID)
        {
            if(!isset($retval[$mOldIFID]['Info']['MIndex']))
            {
                $retval[$mOldIFID]['Info']['MIndex'] = $mIndex;
            }
            else
            {
                $retval[$mOldIFID]['Info']['MIndex'] += $mIndex;
            }
        }



// echo '<pre>'; print_r($retval); die;
        return $retval;
    }

}