<?php
/**
 *
 * @author HuyBD
 *
 */
class Report_MrpController extends Qss_Lib_Controller
{
	public $_comment;
	/**
	 *
	 * @return unknown_type
	 */
	public $limit;

	public function init ()
	{
		//$this->i_SecurityLevel = 15;
		parent::init();
		$this->_common   = new Qss_Model_Extra_Extra();
		$this->limit     = Qss_Lib_Extra_Const::$DATE_LIMIT;
		$this->headScript($this->params->requests->getBasePath() . '/js/report-list.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');
		$this->html->curl = $this->params->requests->getRequestUri();
		$this->layout         = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
	}
	public function monitoringAction()
	{
		//	$this->v_fCheckRightsOnForm(155);
		$model    = new Qss_Model_Extra_Warehouse();
		$items    = $model->getAllItems();
		//getAllItemGroups();
		$this->html->groups = $this->_common->getTable(array('*'), 'ONhomSanPham', array(), array('TenNhom'), 'NO_LIMIT');
		$this->html->items  = $this->groupItemByGroups($items);
	}

	public function monitoring1Action()
	{
		$model     = new Qss_Model_Extra_Mrp();
		$warehouse = new Qss_Model_Extra_Warehouse();
		$solar     = new Qss_Model_Calendar_Solar();
		$params    = $this->params->requests->getParams();
		$limit     = $this->limit;
		$start     = Qss_Lib_Date::displaytomysql($params['start']);
		$end       = Qss_Lib_Date::displaytomysql($params['end']);
		$end       = Qss_Lib_Extra::getEndDate($start, $end, 'D', $limit);
		$items     = isset($params['items'])?$params['items']:array();
		$itemtype  = isset($params['itemtype'])?$params['itemtype']:array();
		$dateRange = $solar->createDateRangeArray($start, $end);
		$currentDate = date('Y-m-d');
		$yesterday = date("Y-m-d", strtotime("yesterday"));
		$microStart = strtotime($start);
		$microEnd = strtotime($end);
		$microCurrent = strtotime($currentDate);
		$inventoryReal = array();
		$inventoryPlan = array();
		$inventoryBefore = array();
		$items = $model->getProducts($params['group'],$items,$itemtype);
		$itemArr = array();
		$dayArr = Qss_Lib_Extra::displayRangeDate($start, $end, 'D');
		$iIndex = 0;

		foreach ($items  as $item)
		{
			$itemArr[$iIndex]['ID'] = $item->IOID;
			$iIndex++;
		}

		$compareStartWithCurrent = Qss_Lib_Date::compareTwoDate($start, $currentDate);
		$compareCurrentWithEnd   = Qss_Lib_Date::compareTwoDate($currentDate, $end);



		// Neu ngay hien tai o trong khoang st->en thi tinh lam hai khoang thuc te va ke hoach
		if($compareStartWithCurrent <= 0 && $compareCurrentWithEnd <= 0)
		{
			// Tinh tu ngay bat dau den ngay truoc ngay hien tai (thuc te)
			$inventoryReal = $model->getInventoryByRangeTime($itemArr, $start, $yesterday);
				
			// Tinh tu ngay hien tai den ngay ket thuc (ke hoach)
			$inventoryBefore  = $warehouse->getInventoryBefore($currentDate,$itemArr);
			$purchasePlan     = $model->getPurchasePlanForMonitoring($itemArr, $start, $end);
			$productionPlan   = $model->getProductionPlanForMonitoring($itemArr, $start, $end);
			$begin = $start;

		}
		// Neu ngay hien tai nam truoc ngay st thi tinh tat ca theo ke hoach
		elseif($compareStartWithCurrent == 1)
		{
			$inventoryBefore  = $warehouse->getInventoryBefore($currentDate,$itemArr);
			$purchasePlan     = $model->getPurchasePlanForMonitoring($itemArr, $currentDate, $end);
			$productionPlan   = $model->getProductionPlanForMonitoring($itemArr, $currentDate, $end);
		}
		// Neu ngay hien tai nam sau ngay en thi tinh tat ca theo thuc the
		elseif($compareCurrentWithEnd == 1)
		{
			$inventoryReal    = $model->getInventoryByRangeTime($itemArr, $start, $end);
			$purchasePlan     = $model->getPurchasePlanForMonitoring($itemArr, $start, $end);
			$productionPlan   = $model->getProductionPlanForMonitoring($itemArr, $start, $end);
		}

		$purchasePlanArr = array();
		if(count((array)$purchasePlan))
		{
			foreach ($purchasePlan as $item)
			{
				$purchasePlanArr[$item->RefItem][$item->Date] = $item->TongSo * $item->Rate;
			}
		}

		$productionPlanArr = array();
		if(count((array)$productionPlan))
		{
			foreach ($productionPlan as $item)
			{
				$productionPlanArr[$item->RefItem][$item->Date] = $item->TongSo * $item->Rate;
			}
		}

		$inventoryRealArr = array();
		foreach ($inventoryReal as $inv)
		{
			$inventoryRealArr[$inv->RefItem][$inv->Date] = $inv->TongSo;
		}

		$inventoryBeforeArr = array();
		if(isset($inventoryBefore) && $inventoryBefore)
		{
			foreach ($inventoryBefore as $item)
			{
				$inventoryBeforeArr[$item->Ref_MaSanPham] = $item->TonKhoDauKy;
			}
		}

		//echo '<pre>'; print_r($purchasePlanArr);
		//echo '<pre>'; print_r($productionPlanArr);
		//echo '<pre>'; print_r($inventoryBeforeArr); die;

		$this->html->purchase = $purchasePlanArr;
		$this->html->production = $productionPlanArr;
		$this->html->before = $inventoryBeforeArr;
		$this->html->real = $inventoryRealArr;
		$this->html->current = $currentDate;
		$this->html->products = $items;
		$this->html->dayArray = $dayArr;
		$this->html->start    = $params['start'];
		$this->html->end      = date('d-m-Y',strtotime($end));
		$this->html->totalCols= count($dateRange) + 3;

	}
	public function monitoring2Action()
	{
		$model = new Qss_Model_Extra_Warehouse();
		$params = $this->params->requests->getParams();
		$itemType = array(
				  'MuaVao'=>$params['purchase'],
				  'BanRa'=>$params['sale'],
				  'SanXuat'=>$params['production'],
				  'VatTu'=>$params['material']);
		$items = $model->getAllItems($params['group'], $itemType);
		$this->html->items = $this->groupItemByGroups($items);
	}
	private function groupItemByGroups($items)
	{
		$oldGroup = '';
		$retItems = array();
		$i        = 0;

		foreach ($items as $it)
		{
			if($oldGroup != $it->Ref_NhomSanPham)
			{
				$retItems[$it->Ref_NhomSanPham]['TenNhom'] = $it->NhomSanPham;
				$retItems[$it->Ref_NhomSanPham]['RefNhom'] = $it->Ref_NhomSanPham?$it->Ref_NhomSanPham:0;
			}
			$retItems[$it->Ref_NhomSanPham]['SanPham'][$i]['Ref']  = $it->IOID;
			$retItems[$it->Ref_NhomSanPham]['SanPham'][$i]['Code'] = $it->MaSanPham;
			$retItems[$it->Ref_NhomSanPham]['SanPham'][$i]['Name'] = $it->TenSanPham;
			$i++;
		}
		return $retItems;
	}
}
?>