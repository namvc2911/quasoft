<?php

/**
 *
 * @author: ThinhTuan
 * @todo: bo ca ham thua, /cost/manufacturing, /cost/group/
 * @todo: gop ba bao cao ve dung may theo ky, nhom, khu vuc
 * @todo: Can them dieu kien cho mot so bao cao ve pbt voi tinh trang pbt la hoan thanh
 * @todo: Sua bao cao m750
 */
class Static_M791Controller extends Qss_Lib_Controller
{
	// property
	protected $_model;  /* Remove */

	public function init()
	{
		$this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
		parent::init();

		/* @todo: Remove headScript */
		$this->headScript($this->params->requests->getBasePath() . '/js/report-list.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');

		/* @todo: Remove $_model, $_params, $_common */
		$this->_model     = new Qss_Model_Maintenance_Workorder();

		/* @todo: Remove curl */
		//$this->html->curl = $this->params->requests->getRequestUri();
	}

	public function indexAction()
	{

	}

	public function showAction()
	{
		$workordercmodel = new Qss_Model_Maintenance_Workorder();
		$location   = $this->params->requests->getParam('location', 0);
		$workcenter = $this->params->requests->getParam('workcenter', 0);
		$date = $this->params->requests->getParam('date','');
		$date = $date?Qss_Lib_Date::displaytomysql($date):'';

		$worksOfMaintainOrder  = $workordercmodel->getInCompletedTasksOfWorkOrder($date, $location, 0, 0, $workcenter);
		$maintainReturn        = $this->getMaintainDataForMaintainOrder($date, $location);//array(), $worksOfMaintainOrder);
		$this->html->report    = $maintainReturn;
	}
	private function getMaintainDataForMaintainOrder($date, $location)
	{
		$orderModel   = new Qss_Model_Maintenance_Workorder();
		$orders       = $orderModel->getOrders($date, $date, $location);

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
		$tasks        = $orderModel->getTasksByIFID($ordersIFIDArr);
		$materials    = $orderModel->getMaterialsByIFIDGroupByIFID($ordersIFIDArr);
		// 		$outsources   = $orderModel->getOutsourcesByIFID($ordersIFIDArr);

		// ===== ADD TASKS TO MAINT ARRAY  =====
		foreach($tasks as $item)
		{
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
				$tempCom['RowSpan'] = 0;
				$retval[$item->IFID]['Component'][@(int)$item->Ref_ViTri] = $tempCom;
			}

			$temp            = array();
			$temp['MoTa']    = $item->MoTaCongViec;
			$temp['GhiChu']  = $item->GhiChuCongViec;
			$temp['DanhGia'] = $item->DanhGia;
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
?>