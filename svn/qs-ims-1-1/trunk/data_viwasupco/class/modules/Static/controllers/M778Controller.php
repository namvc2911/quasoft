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
       
    }

	public function showAction()
	{
        $mCommon    = new Qss_Model_Extra_Extra();
	    $mEquip     = new Qss_Model_M705_Equipment();
	    $mBreakdown = new Qss_Model_M759_Breakdown();
	    $mWorkorder = new Qss_Model_M759_Workorder();
	    $mM778      = new Qss_Model_M778_Viwasupco();
        $refEq      = $this->params->requests->getParam('eq', 0);

        $this->html->eq                 = $mCommon->getTableFetchOne('ODanhSachThietBi', array('IOID' => $refEq));
        $this->html->equipStructures    = $mEquip->getStructuresOfEquip($refEq);
        $this->html->techSpecifications = $mEquip->getTechSpecificationsOfEquip($refEq);
        $this->html->closedBreakdown    = $mM778->getBreakdownsByEquip($refEq);
        $this->html->closedUnbroken     = $mM778->getPreventiveByEquip($refEq);
        $this->html->plans              = $mM778->getPlansByEquip($refEq);
	}

    public function excelAction()
    {
        $refEq      = $this->params->requests->getParam('eq', 0);
        if(!$refEq) { return; }

        header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-disposition: attachment; filename=\"Lý lịch thiết bị.xlsx\"");

        $mCommon    = new Qss_Model_Extra_Extra();
        $mEquip     = new Qss_Model_M705_Equipment();
        $mBreakdown = new Qss_Model_M759_Breakdown();
        $mWorkorder = new Qss_Model_M759_Workorder();
        $mPlan      = new Qss_Model_M724_Plan();
        $mM778      = new Qss_Model_M778_Viwasupco();
        $refEq      = $this->params->requests->getParam('eq', 0);
        $row        = 23;

        $main               = new stdClass();
        $file               = new Qss_Model_Excel(QSS_DATA_DIR.'/template/M778/Viwasupco_LyLichThietBi.xlsx');
        $eq                 = $mCommon->getTableFetchOne('ODanhSachThietBi', array('IOID' => $refEq));
        $equipStructures    = $mEquip->getStructuresOfEquip($refEq);
        $closedBreakdown    = $mM778->getBreakdownsByEquip($refEq);
        $closedUnbroken     = $mM778->getPreventiveByEquip($refEq);
        $techSpecifications = $mEquip->getTechSpecificationsOfEquip($refEq);
        $plans              = $mM778->getPlansByEquip($refEq);

        // Dien thong tin chinh
        $main->TenThietBi     = $eq->TenThietBi;
        $main->MaThietBi      = $eq->MaThietBi;
        $main->NhomThietBi    = $eq->NhomThietBi;
        $main->NhaSanXuat     = $eq->XuatXu;
        $main->DacTinhKyThuat = $eq->DacTinhKT;
        $main->NamSanXuat     = $eq->NamSanXuat;
        $main->DonViQuanLy    = @$eq->DonViQuanLy;
        $main->TinhTrang      = @$eq->Ref_TrangThai;
        $main->QuyDinh        = @$eq->QuyDinhKhiSuDungBaoDuong;
        $file->init(array('m'=>$main));

        // Dien thong tin thong so ky thuat
        $paramNo = 0;
        foreach($techSpecifications as $item)
        {
            $data    = new stdClass();
            $data->a = ++$paramNo;
            $data->b = $item->Ten;
            $data->c = $item->DonViTinh;
            $data->d = $item->GiaTri;

            $file->newGridRow(array('s'=>$data), $row, 22);
            $row++;
        }

        // Dien thong tin cau truc
        $startCauTruc = $row + 4;
        $row          = $row + 5;
        $paramNo      = 0;

        foreach($equipStructures as $item)
        {
            $data    = new stdClass();
            $data->a = $item->ViTri;
            $data->b = $item->BoPhan;
            $data->c = $item->DacTinhKyThuat;
            $data->d =  "{$item->TenSP}". ($item->MaSP?"({$item->MaSP})":'');
            $data->e = $item->DonViTinh;
            $data->f = Qss_Lib_Util::formatNumber($item->SoLuongHC);

            $file->newGridRow(array('s'=>$data), $row, $startCauTruc);
            $row++;
        }

        // Dien thong tin kế hoạch bảo trì
        $startKeHoach = $row + 4;
        $row          = $row + 5;
        $paramNo      = 0;

        foreach($plans as $item)
        {
            $data    = new stdClass();
            $data->a = ++$paramNo;
            $data->b = $item->BoPhan;
            $data->c = $item->MoTa;
            $data->d = $item->TongHopChuKy;
            $data->e = $item->TongHopVatTuExcel;

            $file->newGridRow(array('s'=>$data), $row, $startKeHoach);
            $row++;
        }

        // Dien thong tin sự cố
        $startSuCo    = $row + 4;
        $row          = $row + 5;
        $paramNo      = 0;

        foreach($closedBreakdown as $item)
        {
            $data    = new stdClass();
            $data->a = ++$paramNo;
            $data->b = $item->BoPhan;
            $data->c = $item->MoTa;
            $data->d = Qss_Lib_Date::mysqltodisplay($item->NgayBatDau);
            $data->e = $item->MaNguyenNhanSuCo;
            $data->f = Qss_Lib_Util::formatNumber($item->ThoiGianDungMay);
            $data->g = $item->XuLy;
            $data->h = $item->TongHopVatTuExcel;

            $file->newGridRow(array('s'=>$data), $row, $startSuCo);
            $row++;
        }

        // Dien thong tin bảo dưỡng
        $startBaoDuong = $row + 4;
        $row           = $row + 5;
        $paramNo       = 0;

        foreach($closedUnbroken as $item)
        {
            $data    = new stdClass();
            $data->a = ++$paramNo;
            $data->b = $item->BoPhan;
            $data->c = $item->MoTa;
            $data->d = Qss_Lib_Date::mysqltodisplay($item->NgayBatDau);
            $data->e = '';
            $data->f = $item->NguoiThucHien;
            $data->g = $item->TongHopVatTuExcel;
            $data->h = @$item->GhiChu;

            $file->newGridRow(array('s'=>$data), $row, $startBaoDuong);
            $row++;
        }

        $file->removeRow($startBaoDuong);
        $file->removeRow($startSuCo);
        $file->removeRow($startKeHoach);
        $file->removeRow($startCauTruc);
        $file->removeRow(22);

        $file->save();
        die();
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

	protected function getWorkOrderHistoryDetailOfEquipment($refEq)
	{
		$orderModel = new Qss_Model_Maintenance_Workorder();
		$orders     = $orderModel->getClosedWorkOrderByEquipment($refEq);

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
			$ordersIFIDArr[]             = $item->IFID_M759;
			$tempInfo                    = array();
			$tempInfo['IFID']            = $item->IFID_M759;
			$tempInfo['DocNo']           = @$item->SoPhieu;
			$tempInfo['Code']            = $item->MaThietBi;
			$tempInfo['Name']            = $item->TenThietBi;
			$tempInfo['Type']            = $item->LoaiBaoTri;
			$tempInfo['BoPhan']          = $item->BoPhan;
			$tempInfo['TypeCode']        = $item->Loai;
			$tempInfo['Shift']           = $item->Ca;
			$tempInfo['WorkCenter']      = $item->TenDVBT;
			$tempInfo['Employee']        = $item->NguoiThucHien;
			$tempInfo['Line']            = 0;
			//$tempInfo['Status']          = $item->Name;
            $tempInfo['StatusNo']        = ($item->StepNo > 0)?$item->StepNo:1;
			//$tempInfo['Review']          = $item->DanhGia;
			$tempInfo['ReqDate']         = $item->NgayYeuCau;
			$tempInfo['SDate']           = $item->NgayBatDau;
			$tempInfo['EDate']           = $item->Ngay;
			$tempInfo['Des']             = $item->MoTa;
			$tempInfo['Intervention']    = $item->XuLy;

			// Su co
			$tempInfo['BreakDate']         = $item->NgayDungMay;
			$tempInfo['Downtime']          = $item->ThoiGianDungMay;
			$tempInfo['BreakCode']         = $item->MaNguyenNhanSuCo;




			$tempInfo['MIndex']          = 0;
			$tempInfo['TIndex']          = 0;
			$tempInfo['NotMat']          = 0;
			$retval[$item->IFID_M759]['Info'] = $tempInfo;
			$retval[$item->IFID_M759]['Component'] = array();
		}

		// ===== GET TASKS AND MATERIALS =====
		$tasks        = $orderModel->getTasksByIFID($ordersIFIDArr);
		$materials    = $orderModel->getMaterialsByIFID($ordersIFIDArr);
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
			$temp['BoPhan']  = $item->BoPhan . ($item->ViTri?(' - '.$item->ViTri):'');
			$temp['MoTa']    = $item->MoTaCongViec;
			$temp['Ten']     = $item->Ten;
            $temp['NguoiThucHien']= @$item->NguoiThucHien;
			$temp['GhiChu']  = $item->GhiChuCongViec;
			$temp['DanhGia'] = $item->DanhGia;
			$temp['Dat']     = $item->Dat;

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
			$temp['DonViTinh']      = $item->DonViTinh;
			$temp['SoLuong']        = $item->SoLuong;
			$temp['GhiChu']         = $item->GhiChu;
			$temp['DacTinhKyThuat'] = $item->DacTinhKyThuat;
			$temp['GhiChu']         = $item->GhiChu;

			$retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]['Material'][$mIndex] = $temp;
			$mIndex++;
			$retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri]['MIndex']  = $mIndex;
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
		return $retval;
	}	
	
}