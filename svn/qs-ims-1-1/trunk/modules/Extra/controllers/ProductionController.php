<?php

/**
 *
 * @author: ThinhTuan
 * @component: Production
 * @place: modules/Extra/controllers/ProductionController.php
 *
 */
class Extra_ProductionController extends Qss_Lib_Production_Controller
{

	public function init()
	{
		parent::init();
		$this->headScript($this->params->requests->getBasePath() . '/js/common.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/tabs.js');
	}

	// @Action: createmo/inventory/index
	// @Description: Khung tao lenh san xuat tu ton kho
	public function CreatemoInventoryIndexAction()
	{
		$this->html->shifts = $this->_common->getTable(array('*'), 'OCa', array(), array(), 'NO_LIMIT');
		$this->html->lines = $this->_common->getTable(array('*'), 'ODayChuyen', array(), array(), 'NO_LIMIT');
	}

	// @Action: createmo/inventory/item
	// @Description: lay san pham theo day chuyen
	public function CreatemoInventoryItemAction()
	{

		$items = $this->_common->getObjectByIDArr($this->_params['lineFilter'], array('code' => 'M702', 'table' => 'OSanPhamCuaDayChuyen', 'main' => 'ODayChuyen'));
		$operations = $this->_common->getObjectByIDArr($this->_params['lineFilter'], array('code' => 'M702', 'table' => 'OCongDoanDayChuyen', 'main' => 'ODayChuyen'));
		$data = array('item' => $items, 'operation' => $operations);
		echo Qss_Json::encode(array('error' => 0, 'data' => $data));

		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	// @Action: createmo/inventory/search
	// @Description: Tim kiem san pham dua theo ton kho
	// @todo: Can tim kiem theo dieu kien, hien tai khong tim kiem theo dieu kien.
	public function CreatemoInventorySearchAction()
	{
		$lines = $this->_production->getItemAndBOMByLine($this->_params['lineFilter'], $this->_params['itemFilter']);
		$bomArr = array(); // Mag id cua cac bom
		$maxQty = array(); // So luong san xuat toi da dua theo ton kho va bom
		$oldBOMIFID = ''; // Loai bo trung lap, chi lay max qty mot lan theo min cong doan
		$calendar = array(); // Lich lam viec cua day chuyen
		$oldBOM = '';
		$oldOperation = '';
		$capacity = array();
		$operation = array();
		$wWork = 0;

		foreach ($lines as $item)
		{
			$bomArr[$item->BOMIFID] = $item->BOMIFID; // Bo gia tri trung lap
			$calendar[] = $item->CalendarID; // chi co mot lich lam viec

			if ($oldBOM != $item->BOMIFID)
			{
				$i = 0; // Dung cho mang operation
			}

			if ($oldBOM != $item->BOMIFID || $oldOperation != $item->CDIOID)
			{
				// Ghi lai cong doan
				if ($item->CDIOID)
				{
					$operation[$item->BOMIFID][$i]['ID'] = $item->RefOperation;
					$operation[$item->BOMIFID][$i]['Name'] = $item->Operation;
					$operation[$item->BOMIFID][$i]['Time'] = $item->OperationTime;
					$operation[$item->BOMIFID][$i]['Assembly'] = $item->Assembly;
					$operation[$item->BOMIFID][$i]['BOMQty'] = $item->BOMQty;
					$operation[$item->BOMIFID][$i]['Item'] = $item->Item;
					$operation[$item->BOMIFID][$i]['RefItem'] = $item->RefItem;
					$operation[$item->BOMIFID][$i]['ItemCode'] = $item->ItemCode;
					$operation[$item->BOMIFID][$i]['RefAttribute'] = $item->RefAttribute;
					$operation[$item->BOMIFID][$i]['Attribute'] = $item->Attribute;
					$operation[$item->BOMIFID][$i]['BOM'] = $item->BOM;
					$operation[$item->BOMIFID][$i]['IFID'] = $item->BOMIFID;
					$operation[$item->BOMIFID][$i]['ItemUOM'] = $item->ItemUOM;
					$operation[$item->BOMIFID][$i]['Key'] = $item->BOMIFID . '-' . $item->RefItem . '-' . $item->RefAttribute;
					$i++;
				}
			}
			$oldBOM = $item->BOMIFID;
			$oldOperation = $item->CDIOID;
		}

		$maxQtyByBOM = $this->_production->getMaxQtyByBOMAndInventory($bomArr);

		// So luong co the san xuat
		foreach ($maxQtyByBOM as $item)
		{
			if ($item->Assembly == 1) // Thao ro
			{
				$maxQty[$item->BOMIFID] = $item->InventoryQty;
			}
			else // Lap dat
			{
				if ($oldBOMIFID != $item->BOMIFID)
				{
					// Lay min neu da sang mot dong moi
					if (isset($min) && $min !== '')
					{
						$maxQty[$oldBOMIFID] = floor($min);
					}
					$min = ''; // set min cho bom tiep theo
				}

				// Tinh so luong co the san xuat
				$minTmp = $item->MemberQty ? ($item->InventoryQty * $item->BOMQty) / $item->MemberQty : 0;
				if ($min == '' || $minTmp < $min)
				{
					$min = $minTmp;
				}
				$oldBOMIFID = $item->BOMIFID;
			}
		}

		// Lay min cuoi cung neu co
		if (isset($oldBOMIFID) && isset($min) && $min != '')
		{
			$maxQty[$oldBOMIFID] = floor($min);
		}

		$lichDacBiet = Qss_Lib_Extra::getLichDacBiet($calendar
		, Qss_Lib_Date::displaytomysql($this->_params['dateFilter']));
		$lichNgay = Qss_Lib_Extra::getWorkingHoursPerShiftByCal($calendar);
		//$weekday     = date('w', strtotime($this->_params['dateFilter'])); // weekday can lay
		//$date        = Qss_Lib_Date::displaytomysql($this->_params['dateFilter']); // date can lay
		$wCalendar = (isset($calendar[0]) && $calendar[0]) ? ($calendar[0]) : 0; // Lay lich lam; viec( do chi co duy nhat mot day chuyen voi mot lich lam viec)


		$startFirst = date_create($this->_params['dateFilter']);
		$endFirst = date_create($this->_params['endDateFilter']);
		$start = $startFirst;

		while ($start <= $endFirst)
		{
			//$day = $start->format('d');
			$weekday = $start->format('w');
			$date = $start->format('d-m-Y');
			//$month = $start->format('m');
			//$startToDate = $start->format('Y-m-d');
			// Lay lich lam viec uu tien lich dac biet roi den lich ngay, neu ko co tra ve 0
			$wWorkDay = isset($lichDacBiet[$wCalendar][$date]) ?
			$lichDacBiet[$wCalendar][$date] :
			(isset($lichNgay[$wCalendar][$weekday]) ?
			$lichNgay[$wCalendar][$weekday] : 0);

			foreach ($wWorkDay as $shift)
			{
				$wWork += $shift;
			}
			$start = Qss_Lib_Date::add_date($start, 1);
		}
		//echo $wWork.'x'; die;


		$oldBOM = '';
		if ($wWork > 0) // Neu co so gio lam viec lon hon 0, tim min cong doan co the san xuat cua tung sp
		{
			foreach ($lines as $item) // Khong gop duoc voi vong foreach tren
			{
				if ($oldBOM != $item->BOMIFID)
				{
					$capacity[$item->BOMIFID] = $item->QtyPerHour * $wWork;
				}

				$oldBOM = $item->BOMIFID;
			}
		}

		$this->html->operation = $operation;
		$this->html->capacity = $capacity;
		//		$this->html->capacityTime = $capacityTime;
		$this->html->lines = $lines;
		$this->html->workingHours = $wWork;
		$this->html->maxQty = $maxQty;
		$this->html->dateFilter = $this->_params['dateFilter'];
		$this->html->lineFilter = $this->_params['lineFilter'];
		$this->html->shiftFilter = $this->_params['shiftFilter'];
		$this->html->itemFilter = $this->_params['itemFilter'];
	}

	// @Action: createmo/inventory/check
	// @Description: Kiem tra kha nang san xuat
	public function CreatemoInventoryCheckAction()
	{
		$lines = $this->_production->getMaxQtyByBOMAndInventory($this->_params['bom']);
		$material = array();

		//echo '<pre>'; print_r($this->_params['itemAndAttrFilter']);
		// Cong don nguyen vat lieu
		// @todo: nêu ko co thanh phan thi phai bao loi
		// @todo: hien thi chua duoc toan bo thanh phan
		// @todo: Chua tru cho output
		$keepCode = array();
		$i = 0;
		foreach ($lines as $mat)
		{
			$code = $mat->BOMIFID . '-' . $mat->RefItemX . '-' . $mat->RefAttributeX;
			//echo '<pre>'; print_r($mat);

			if (in_array($code, $this->_params['itemAndAttrFilter']))
			{
				$code2 = $mat->RefItem . '-' . $mat->RefAttribute;

				if (!$mat->SFG)
				{
					if (key_exists($code2, $keepCode))
					{
						$material[$keepCode[$code2]]['Qty'] += $mat->Qty;
					}
					else
					{
						$material[$i]['Inv'] = $mat->InventoryQty;
						$material[$i]['Qty'] = $mat->Qty;
						$material[$i]['Assembly'] = $mat->Assembly;
						$material[$i]['Code'] = $mat->ItemCode;
						$material[$i]['Name'] = $mat->ItemName;
						$material[$i]['UOM'] = $mat->UOM;
						$material[$i]['Attr'] = $mat->Attribute;
						$material[$i]['BOMQty'] = $mat->BOMQty;
						$material[$i]['Key'] = $code;
						$keepCode[$code2] = $i;
						$i++;
					}
				}
			}
		}


		$operationArr = array();
		// cong doan san pham

		if (isset($this->_params['operation']))
		{
			foreach ($this->_params['operation'] as $operation)
			{
				//$temp = unserialize(Qss_Lib_Extra::formatUnSerialize($operation));
				$temp = unserialize($operation);
				foreach ($temp as $op)
				{
					if (!isset($operationArr[$op['ID']]))
					{
						$operationArr[$op['ID']]['Name'] = $op['Name'];
						$operationArr[$op['ID']]['ID'] = $op['ID'];
						$operationArr[$op['ID']]['Time'] = ($this->_params['itemAndAttrQty'][$op['Key']] * $op['Time']) / $op['BOMQty'];
					}
					else
					{
						$operationArr[$op['ID']]['Time'] += ($this->_params['itemAndAttrQty'][$op['Key']] * $op['Time']) / $op['BOMQty'];
					}
				}
			}
		}

		$planedOperation = $this->_production->getPlanedOperationByLineShiftDate(
		$this->_params['lineFilter']
		, Qss_Lib_Date::displaytomysql($this->_params['dateFilter'])
		, Qss_Lib_Date::displaytomysql($this->_params['endDateFilter'])
		);
		$planedArr = array();

		foreach ($planedOperation as $item)
		{
			if (isset($planedArr[$item->OperationID]))
			{
				$planedArr[$item->OperationID] += $item->OperationQty;
			}
			else
			{
				$planedArr[$item->OperationID] = $item->OperationQty;
			}
		}

		$this->html->material = $material;
		$this->html->qty = $this->_params['itemAndAttrQty'];
		$this->html->operation = $operationArr;
		$this->html->planed = $planedArr;
		$this->html->workingHours = $this->_params['workingHours'];
		$this->html->countWC = $this->_params['countWorkCenter'];
	}

	// @Action: createmo/inventory/save
	// @Description: Luu lenh san xuat thu ton kho
	public function CreatemoInventorySaveAction()
	{
		//$extra  = Qss_Model_Extra_Extra();
		if ($this->params->requests->isAjax())
		{
			$service = $this->services->Extra->Production->Createmo->Inventory->Save($this->_params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	/* ----------------------------------------------------------------------------------------------------- */

	// @Action: createmo/requirement/index
	// @Description: Khung tao lenh san xuat tu yeu cau
	public function CreatemoRequirementIndexAction()
	{
		$this->html->lines = $this->_common->getTable(array('*'), 'ODayChuyen', array(), array(), 'NO_LIMIT');
	}

	// @Action: createmo/requirement/search
	// @Description: Tim kiem cac yeu cau san xuat
	// @Not-Test-Yet: Tinh dat san xuat
	public function CreatemoRequirementSearchAction()
	{
		$reqIDArr = array(); // ifid cac dong chinh yeu cau cung ung
		$requirementArr = array(); // dong chinh yeu cau cung ung
		$reqDetailArr = array(); // chi tiet cac dong yeu cau cung ung
		$filter = array('manuLineID' => $this->_params['lineFilter']);
		$requirementMainObj = $this->_production->getProductionRequirementByLine($filter, false, array('page' => $this->_params['pageFilter'], 'display' => $this->_params['perpageFilter']));
		$itemIDArr = array(); // san pham id cua cac san pham can lay BOM
		$bomArr = array(); // bom theo san pham, voi key la san pham
		// @Algorithm: Hien thi yeu cau cung ung
		// @Description: Lay dong chinh yeu cau cung ung voi key la ifid
		// , lay cac dong phu theo dong chinh voi key la ifid >> ioid dong phu
		// , phan trang theo dong chinh.

		foreach ($requirementMainObj as $item)
		{
			$requirementArr[$item->MainIFID]['ReqNo'] = $item->ReqNo;
			$requirementArr[$item->MainIFID]['ReqStartDate'] = $item->ReqStartDate;
			$requirementArr[$item->MainIFID]['ReqEndDate'] = $item->ReqEndDate;
			$reqIDArr[] = $item->MainIFID;
		}

		$requirementDetailObj = $this->_production->getProductionRequirementDetail($reqIDArr);

		foreach ($requirementDetailObj as $item)
		{
			if (!isset($reqDetailArr[$item->MainIFID][$item->SubIOID]['ProductionQty']))
			{
				$reqDetailArr[$item->MainIFID][$item->SubIOID]['OrderedQty'] = 0;
			}

			$reqDetailArr[$item->MainIFID][$item->SubIOID]['IOID'] = $item->IOID;
			$reqDetailArr[$item->MainIFID][$item->SubIOID]['ToIOID'] = $item->ToIOID;
			$reqDetailArr[$item->MainIFID][$item->SubIOID]['Ref'] = $item->Ref;
			$reqDetailArr[$item->MainIFID][$item->SubIOID]['StartDate'] = $item->StartDate;
			$reqDetailArr[$item->MainIFID][$item->SubIOID]['EndDate'] = $item->EndDate;
			$reqDetailArr[$item->MainIFID][$item->SubIOID]['RefItem'] = $item->RefItem;
			$reqDetailArr[$item->MainIFID][$item->SubIOID]['ItemCode'] = $item->ItemCode;
			$reqDetailArr[$item->MainIFID][$item->SubIOID]['ItemName'] = $item->ItemName;
			$reqDetailArr[$item->MainIFID][$item->SubIOID]['Attribute'] = $item->Attribute;
			$reqDetailArr[$item->MainIFID][$item->SubIOID]['ItemUOM'] = $item->ItemUOM;
			$reqDetailArr[$item->MainIFID][$item->SubIOID]['ItemQty'] = $item->ItemQty;
			$reqDetailArr[$item->MainIFID][$item->SubIOID]['OrderedQty'] += $item->ProductionQty;
			$itemIDArr[] = $item->RefItem;
		}

		// @Algorithm: Lay bom theo san pham cua cac dong phu
		$bomObj = $this->_production->getBomByItems($itemIDArr);
		$i = 0;
		foreach ($bomObj as $item)
		{
			$bomArr[$item->RefItem][$i]['Name'] = $item->BOMName;
			$bomArr[$item->RefItem][$i]['Key'] = $item->BOMKey;
			$bomArr[$item->RefItem][$i]['Assembly'] = $item->Assembly;
			$i++;
		}

		$this->html->mainReq = $requirementArr;
		$this->html->detailReq = $reqDetailArr;
		$this->html->bom = $bomArr;
	}

	// @Action: createmo/requirement/page
	// @Description: Phan trang cho tim kiem yeu cau san xuat
	public function CreatemoRequirementPageAction()
	{
		$filter = array('manuLineID' => $this->_params['lineFilter']);
		$totalPage = ceil($this->_production->getProductionRequirementByLine($filter, true) / $this->_params['perpageFilter']);
		$data = array('total' => $totalPage, 'page' => $this->_params['pageFilter']);
		echo Qss_Json::encode(array('error' => 0, 'data' => $data));

		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	// @Action: createmo/requirement/check
	// @Description: Kiem tra cac yeu cau duoc chon co the san xuat hay khong
	public function CreatemoRequirementCheckAction()
	{
		// Xem xet co du nguyen vat lieu theo bom hay ko?
		// Lay nguyen vat lieu theo bom ( luu y: tru di san pham dau ra dung o cd sau)
		// Khau tru ton kho
		$bomConfig = $this->getBOMConfig($this->_params['bom']);
		$materials = $this->getMaterialByBOM($bomConfig);
		$ouput = $this->getOutputByBOM($bomConfig);
		$sparepart = $this->getSparepartByBOM($bomConfig);

		$materialRequire = $this->getQtyReality($materials, $bomConfig, $this->_params);
		$ouputReality = $this->getQtyReality($materials, $bomConfig, $this->_params);
		$sparepartRequire = $this->getQtyReality($sparepart, $bomConfig, $this->_params);
		$materialRequireTemp = isset($materialRequire['notByBOM']) ? $materialRequire['notByBOM'] : array();
		$materialInventory = $this->_warehouseCtl->getInventory($materialRequireTemp);
		$timeRequire = $this->getTimeRequire($bomConfig, $this->_params);
		$availableTime = $this->getAvailableTime($this->_params);
		$planedTime = $this->getProductionPlaned($this->_params['fromDateFilter']);


		$this->html->bom = $bomConfig;
		$this->html->materials = $materials;
		$this->html->materialRequire = $materialRequire;
		$this->html->output = $ouputReality;
		$this->html->sparepart = $sparepartRequire;
		$this->html->materialInventory = $materialInventory;
		$this->html->timeRequire = $timeRequire;
		$this->html->planedTime = $planedTime;
		$this->html->availableTime = $availableTime;
		$this->html->getCost = $this->getCost($bomConfig, $this->_params);
	}

	// @Action: createmo/requirement/save
	// @Description: Luu lai cac yeu cau thanh lenh san xuat
	public function CreatemoRequirementSaveAction()
	{
		$params = $this->params->requests->getParams();
		//$extra  = Qss_Model_Extra_Extra();
		if ($this->params->requests->isAjax())
		{
			$service = $this->services->Extra->Production->Createmo->Requirement->Save($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	/* ----------------------------------------------------------------------------------------------------- */
	/**
	 * Nut tao phieu giao viec tu lenh san xuat: Index
	 */
	public function createwoPoIndexAction()
	{
		$common = new Qss_Model_Extra_Extra();
	}

	/**
	 * Nut tao phieu giao viec tu lenh san xuat: Tim kiem lenh san xuat
	 */
	public function createwoPoSearchAction()
	{
		$ItemID = $this->params->requests->getParam('cwpo_filter_ref_item', 0);
		$LineID = $this->params->requests->getParam('cwpo_filter_ref_line', 0);
		$start = $this->params->requests->getParam('cwpo_filter_start', '');

		$this->html->ProductionOrder = $this->_production->getProductionOrdersForCreateWorkOrders(
		Qss_Lib_Date::displaytomysql($start), $LineID, $ItemID);
		$this->html->date = $start;
	}

	public function createwoPoSaveAction()
	{
		$params = $this->params->requests->getParams();
		//$extra  = Qss_Model_Extra_Extra();
		if ($this->params->requests->isAjax())
		{
			$service = $this->services->Extra->Production->Createwo->Po->Save($params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}



	
}
?>