<?php

/**
 *
 * @author ThinhTuan
 *
 */
class Report_InventoryController extends Qss_Lib_Controller
{

	public $_common;

	/**
	 *
	 * @return unknown_type
	 */
	public function init()
	{
		$this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
		parent::init();
		$this->_common = new Qss_Model_Extra_Extra();
		$this->headScript($this->params->requests->getBasePath() . '/js/report-list.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');
		$this->html->curl = $this->params->requests->getRequestUri();
		

	}

	public function inputVendorAction()
	{
		//vendorsDialBoxData
		$vendorsDialBoxData = array();
		$vendors            = $this->_common->getTable(
		array('*')
		, 'ODoiTac');
		$index = 0;
		foreach($vendors as $ven)
		{
			$temp = array();
			$temp['Display'] = $ven->MaDoiTac .' - '. $ven->TenDoiTac;
			$temp['ID']      = $ven->IOID;
			$vendorsDialBoxData[0]['Dat'][$index] = $temp;
			$index++;
		}
		$this->html->vendorsDialBoxData = $vendorsDialBoxData;

		/* // Ko Xoa
		 //stocksDialBoxData
		 $stocksDialBoxData = array();
		 $stocks            = $this->_common->getTable(
			array('*')
			, 'ODanhSachKho');
			$index = 0;
			foreach($stocks as $st)
			{
			$temp = array();
			$temp['Display'] = $st->MaKho .' - '. $st->TenKho;
			$temp['ID']      = $st->IOID;
			$stocksDialBoxData[0]['Dat'][$index] = $temp;
			$index++;
			}
			$this->html->stocksDialBoxData = $stocksDialBoxData;
		 *
		 */
	}

	public function inputVendor1Action() // Nội dung báo cáo
	{

		$wInoutModel   = new Qss_Model_Warehouse_Inout();
		$startDate     = $this->params->requests->getParam('start', '');
		$endDate       = $this->params->requests->getParam('end', '');
		$stockIOIDArr  = $this->params->requests->getParam('warehouse', array());
		$vendorIOIDArr = $this->params->requests->getParam('vendor', array());

		$data          = $wInoutModel->getInputByTime(
		Qss_Lib_Date::displaytomysql($startDate)
		, Qss_Lib_Date::displaytomysql($endDate)
		, $vendorIOIDArr
		, $stockIOIDArr);
		$inputByVendor = array();
		$oldVendorIOID = '';
		$i             = 0;
		$totalByVenDor = 0;// init

		foreach($data as $d)
		{
			if($oldVendorIOID != $d->VendorIOID)
			{
				$temp          = array();
				$temp['Code']  = $d->VendorCode;
				$temp['Name']  = $d->VendorName;
				$temp['Total'] = 0; // init
				$temp['SoLuong'] = 0; // init
				$temp['Dat']   = array();
				$totalByVenDor = 0; // reset
				$i             = 0;
				$inputByVendor[$d->VendorIOID] = $temp;
			}
			$oldVendorIOID = $d->VendorIOID;
				
			$temp2 = array();
			$temp2['DocDate']   = $d->DocDate;
			$temp2['DocNo']     = $d->DocNo;
			$temp2['ItemCode']  = $d->ItemCode;
			$temp2['ItemName']  = $d->ItemName;
			$temp2['UOM']       = $d->UOM;
			$temp2['Qty']       = $d->QTY;
			$temp2['UnitPrice'] = $d->UnitPrice;
			$temp2['Total']     = $d->Total;
			$temp2['SoLuong']     = $d->QTY;
				
			$inputByVendor[$d->VendorIOID]['Total']  += $d->Total;
			$inputByVendor[$d->VendorIOID]['SoLuong']  += $d->QTY;
			$inputByVendor[$d->VendorIOID]['Dat'][$i] = $temp2;
			$i++;
				
		}

		$this->html->start = $startDate;
		$this->html->end   = $endDate;
		$this->html->data  = $inputByVendor;
	}

    public function outputTypeAction()
    {
        //vendorsDialBoxData
        $typesDialBoxData = array();
        $types            = $this->_common->getTable(
            array('*')
            , 'OLoaiXuatKho');
        $index = 0;
        foreach($types as $ven)
        {
            $temp = array();
            $temp['Display'] = $ven->Ten;
            $temp['ID']      = $ven->IOID;
            $typesDialBoxData[0]['Dat'][$index] = $temp;
            $index++;
        }
        $this->html->typesDialBoxData = $typesDialBoxData;
    }

    public function outputType1Action()
    {
        $wInoutModel   = new Qss_Model_Warehouse_Inout();
        $startDate     = $this->params->requests->getParam('start', '');
        $endDate       = $this->params->requests->getParam('end', '');
        $stockIOIDArr  = $this->params->requests->getParam('warehouse', array());
        $typeIOIDArr   = $this->params->requests->getParam('type', array());

        $data          = $wInoutModel->getOutputByType(
            Qss_Lib_Date::displaytomysql($startDate)
            , Qss_Lib_Date::displaytomysql($endDate)
            , $stockIOIDArr
            , $typeIOIDArr);

        $outputByType = array();
        $oldTypeIOID = '';
        $i             = 0;
        $totalByVenDor = 0;// init

        foreach($data as $d)
        {
            if($oldTypeIOID != $d->VendorIOID)
            {
                $temp          = array();
                $temp['Code']  = $d->VendorCode;
                $temp['Name']  = $d->VendorName;
                $temp['Total'] = 0; // init
                $temp['SoLuong'] = 0; // init
                $temp['Dat']   = array();
                $totalByVenDor = 0; // reset
                $i             = 0;
                $outputByType[$d->VendorIOID] = $temp;
            }
            $oldTypeIOID = $d->VendorIOID;

            $temp2 = array();
            $temp2['DocDate']   = $d->DocDate;
            $temp2['DocNo']     = $d->DocNo;
            $temp2['ItemCode']  = $d->ItemCode;
            $temp2['ItemName']  = $d->ItemName;
            $temp2['UOM']       = $d->UOM;
            $temp2['Qty']       = $d->QTY;
            $temp2['UnitPrice'] = $d->UnitPrice;
            $temp2['Total']     = $d->Total;
            $temp2['SoLuong']   = $d->QTY;

            $outputByType[$d->VendorIOID]['Total']  += $d->Total;
            $outputByType[$d->VendorIOID]['SoLuong']  += $d->QTY;
            $outputByType[$d->VendorIOID]['Dat'][$i] = $temp2;
            $i++;

        }

        $this->html->start = $startDate;
        $this->html->end   = $endDate;
        $this->html->data  = $outputByType;
    }

	public function volumeAction()
	{
		//	$this->v_fCheckRightsOnForm(155);
		$model = new Qss_Model_Extra_Warehouse();
		$items = $model->getAllItems();
		$this->html->groups = $this->_common->getTable(array('*'), 'ONhomSanPham', array(), array('TenNhom'), 'NO_LIMIT');
		$this->html->items = $this->groupItemByGroups($items);

	}

	public function volume1Action() // Nội dung báo cáo
	{
		//	$this->v_fCheckRightsOnForm(155);
		$model = new Qss_Model_Extra_Warehouse();
		$params = $this->params->requests->getParams();
		$this->html->inOut = $model->getStockVolume(Qss_Lib_Date::displaytomysql($params['date']), $params['items'], $params['warehouse']);
		$this->html->inventory = $model->getInventory(Qss_Lib_Date::displaytomysql($params['date']), $params['items'], $params['warehouse']);

	}

	public function volume2Action() // Sản phẩm theo nhóm
	{
		$model = new Qss_Model_Extra_Warehouse();
		$group = $this->params->requests->getParam('group');
		$items = $model->getAllItems($group);
		$this->html->items = $this->groupItemByGroups($items);

	}

	public function inoutAction()
	{
		//	$this->v_fCheckRightsOnForm(155);
		$model = new Qss_Model_Extra_Warehouse();
		$items = $model->getAllItems();
		$this->html->groups = $this->_common->getTable(array('*'), 'ONhomSanPham', array(), array('TenNhom'), 'NO_LIMIT');
		$this->html->items = $this->groupItemByGroups($items);

	}

	public function inout1Action()
	{
		$start  = $this->params->requests->getParam('start', '');
		$end    = $this->params->requests->getParam('end', '');


		$this->html->InType    = $this->_common->getTableFetchAll('OLoaiNhapKho');
		$this->html->OutType   = $this->_common->getTableFetchAll('OLoaiXuatKho');

		$this->html->report    = $this->getInOutDataGroupByInOutType(
		Qss_Lib_Date::displaytomysql($start),
		Qss_Lib_Date::displaytomysql($end),
		$this->params->requests->getParam('items', array()),
		$this->params->requests->getParam('warehouse', 0),
		$this->params->requests->getParam('has_all', 0),
		$this->params->requests->getParam('has_inventory', 0),
		$this->params->requests->getParam('has_warehouse_transaction', 0)
		);
		$this->html->startDate = $start;
		$this->html->endDate   = $end;

	}

	public function inout2Action()
	{
		$model = new Qss_Model_Extra_Warehouse();
		$group = $this->params->requests->getParam('group');
		$items = $model->getAllItems($group);
		$this->html->items = $this->groupItemByGroups($items);

	}

	public function inoutvalAction()
	{
		//	$this->v_fCheckRightsOnForm(155);
		$model = new Qss_Model_Extra_Warehouse();
		$items = $model->getAllItems();
		$this->html->groups = $this->_common->getTable(array('*'), 'ONhomSanPham', array(), array('TenNhom'), 'NO_LIMIT');
		$this->html->items = $this->groupItemByGroups($items);

	}

	public function inoutval1Action()
	{
		$common    = new Qss_Model_Extra_Extra();
		$start     = $this->params->requests->getParam('start', '');
		$end       = $this->params->requests->getParam('end', '');
		$warehouse = $this->params->requests->getParam('warehouse', 0);

		$this->html->report    = $this->getInOutData(
		Qss_Lib_Date::displaytomysql($start),
		Qss_Lib_Date::displaytomysql($end),
		$this->params->requests->getParam('items', array()),
		$warehouse,
		$this->params->requests->getParam('has_all', 0),
		$this->params->requests->getParam('has_inventory', 0),
		$this->params->requests->getParam('has_warehouse_transaction', 0)
		);

		$this->html->startDate = $start;
		$this->html->endDate   = $end;
		$this->html->stock     = $common->getTableFetchOne('ODanhSachKho', array('IOID'=>$warehouse));

	}

	public function inoutval2Action()
	{
		$model = new Qss_Model_Extra_Warehouse();
		$group = $this->params->requests->getParam('group');
		$items = $model->getAllItems($group);
		$this->html->items = $this->groupItemByGroups($items);


	}

	public function inoutbasicAction()
	{
		//	$this->v_fCheckRightsOnForm(155);
		$model = new Qss_Model_Extra_Warehouse();
		$items = $model->getAllItems();
		$this->html->groups = $this->_common->getTable(array('*'), 'ONhomSanPham', array(), array('TenNhom'), 'NO_LIMIT');
		$this->html->items = $this->groupItemByGroups($items);
	}

	public function inoutbasic1Action()
	{
		$common    = new Qss_Model_Extra_Extra();
		$start     = $this->params->requests->getParam('start', '');
		$end       = $this->params->requests->getParam('end', '');
		$warehouse = $this->params->requests->getParam('warehouse', 0);

		$this->html->report    = $this->getInOutDataOrderByStockAndGroup(
		Qss_Lib_Date::displaytomysql($start),
		Qss_Lib_Date::displaytomysql($end),
		$this->params->requests->getParam('items', array()),
		$warehouse,
		$this->params->requests->getParam('has_all', 0),
		$this->params->requests->getParam('has_inventory', 0),
		$this->params->requests->getParam('has_warehouse_transaction', 0)
		);

		$this->html->startDate = $start;
		$this->html->endDate   = $end;
		$this->html->stock     = $common->getTableFetchOne('ODanhSachKho', array('IOID'=>$warehouse));
	}

	public function inoutbasic2Action()
	{
		$model = new Qss_Model_Extra_Warehouse();
		$group = $this->params->requests->getParam('group');
		$items = $model->getAllItems($group);
		$this->html->items = $this->groupItemByGroups($items);

	}


    public function inoutwcAction()
    {
        //	$this->v_fCheckRightsOnForm(155);
        $model = new Qss_Model_Extra_Warehouse();
        $items = $model->getAllItems();
        $this->html->groups = $this->_common->getTable(array('*'), 'ONhomSanPham', array(), array('TenNhom'), 'NO_LIMIT');
        $this->html->items = $this->groupItemByGroups($items);
    }

    public function inoutwc1Action()
    {
        $common    = new Qss_Model_Extra_Extra();
        $start     = $this->params->requests->getParam('start', '');
        $end       = $this->params->requests->getParam('end', '');
        $warehouse = $this->params->requests->getParam('warehouse', 0);

        $this->html->report    = $this->getInOutDataOrderByStockAndGroup(
            Qss_Lib_Date::displaytomysql($start),
            Qss_Lib_Date::displaytomysql($end),
            $this->params->requests->getParam('items', array()),
            $warehouse,
            $this->params->requests->getParam('has_all', 0),
            $this->params->requests->getParam('has_inventory', 0),
            $this->params->requests->getParam('has_warehouse_transaction', 0)
        );

        $this->html->startDate  = $start;
        $this->html->endDate    = $end;
        $this->html->stock      = $common->getTableFetchOne('ODanhSachKho', array('IOID'=>$warehouse));
        $this->html->workcenter = $common->getTableFetchOne('ODonViSanXuat', array('IOID'=>@(int)$this->html->stock->DonViQuanLy));
    }

    public function inoutwc2Action()
    {
        $model = new Qss_Model_Extra_Warehouse();
        $group = $this->params->requests->getParam('group');
        $items = $model->getAllItems($group);
        $this->html->items = $this->groupItemByGroups($items);

    }

	/**
	 * @note: gia tri tinh bang ngay dau tien, neu o hai thang khac nhau thi gia co the bi lech
	 * @param type $start
	 * @param type $end
	 * @param type $itemIOIDs
	 * @param type $stockIOID
	 * @param type $hasAll
	 * @param type $hasFirstQty
	 * @param type $hasTransaction
	 * @return type
	 */
	private function getInOutDataGroupByInOutType(
	$start
	, $end
	, $itemIOIDs = array()
	, $stockIOID = 0
	, $hasAll = 0
	, $hasFirstQty = 0
	, $hasTransaction = 0)
	{
		$inoutModel   = new Qss_Model_Warehouse_Inout();
		$inout        = $inoutModel->getInOutDataGroupByInOutType($start, $end, $itemIOIDs, $stockIOID);
		$costTable    = $inoutModel->getCostTable($start, $itemIOIDs, $stockIOID);
		$ret          = array();
		$firstOfStart = date('Y-m-01', strtotime($start));
		$firstOfEnd   = date('Y-m-01', strtotime($end));



		foreach($costTable as $item)
		{
			$code = @(int)$item->RefItem.'_'.@(int)$item->RefAttr;
			$ret[$code]['ItemCode']     = $item->ItemCode;
			$ret[$code]['ItemName']     = $item->ItemName;
			$ret[$code]['Attr']         = $item->Attr;
			$ret[$code]['UOM']          = $item->UOM;

			if($item->HasCostTable == 2)
			{
				$ret[$code]['Input']           = array();
				$ret[$code]['Output']          = array();
				$ret[$code]['OpeningStock']    = $item->LastQty;
				$ret[$code]['EndingStock']     = $item->LastQty;
			}
			else
			{
				$ret[$code]['Input']           = array();
				$ret[$code]['Output']          = array();
				$ret[$code]['OpeningStock']    = $item->FirstQty;
				$ret[$code]['EndingStock']     = $item->LastQty;
			}
		}

		if(Qss_Lib_Date::compareTwoDate($firstOfStart, $start) == -1)
		{
			$inoutFromFirstDayOfStartToStart = $inoutModel->getInOutDataGroupByInOutType($firstOfStart, $start, $itemIOIDs, $stockIOID);

			foreach($inoutFromFirstDayOfStartToStart as $item)
			{
				$code = @(int)$item->RefItem.'_'.@(int)$item->RefAttr;
				if(isset($ret[$code]['OpeningStock']))
				{
					$ret[$code]['OpeningStock'] += $item->Input;
					$ret[$code]['OpeningStock'] -= $item->Output;
				}
				else
				{
					$ret[$code]['ItemCode']      = $item->ItemCode;
					$ret[$code]['ItemName']      = $item->ItemName;
					$ret[$code]['Attr']          = $item->Attr;
					$ret[$code]['UOM']           = $item->UOM;
					$ret[$code]['OpeningStock']  = 0;
					$ret[$code]['OpeningStock'] += $item->Input;
					$ret[$code]['OpeningStock'] -= $item->Output;
				}
			}
		}

		foreach ($inout as $item)
		{
			$code  = @(int)$item->RefItem.'_'.@(int)$item->RefAttr;
			$first = 0;

			if(isset($ret[$code]['OpeningStock']) && $ret[$code]['OpeningStock'])
			{
				$first = $ret[$code]['OpeningStock'];
			}

			$last  = $first;

			//			foreach ($inOut as $item)
			//			{
			//				if(isset($ret[$item->RefItem]))
			//				{
			$ret[$code]['Input'][$item->RefType]  = @(double)$item->Input;
			$ret[$code]['Output'][$item->RefType] = @(double)$item->Output;

            $ret[$code]['NormalReturn'] = @(double)$item->NormalReturn;
            $ret[$code]['BreakReturn']  = @(double)$item->BreakReturn;

			$last += $item->Input;
			$last -= $item->Output;
			//				}
			//			}

			$ret[$code]['ItemCode']        = $item->ItemCode;
			$ret[$code]['ItemName']        = $item->ItemName;
			$ret[$code]['Attr']            = $item->Attr;
			$ret[$code]['UOM']             = $item->UOM;
			$ret[$code]['OpeningStock']    = $first;
			$ret[$code]['EndingStock']     = $last;
		}

		foreach ($ret as $key=>$item)
		{
			// Neu khong co so luong dau ky va khong co nhap xuat kho thi khong hien ra
			if(!$hasAll && $hasFirstQty && !$item['OpeningStock'] && !count($item['Input'])  && !count($item['Output']))
			{
				unset($ret[$key]);
			}

			// Neu khong co giao dich kho trong ky, co the van co so luong dau ky
			if(!$hasAll && $hasTransaction && !count($item['Input'])  && !count($item['Output']))
			{
				unset($ret[$key]);
			}
		}

		return $ret;
	}


	private function getInOutDataOrderByStockAndGroup(
	$start
	, $end
	, $itemIOIDs = array()
	, $stockIOID = 0
	, $hasAll = 0
	, $hasFirstQty = 0
	, $hasTransaction = 0)
	{
		$inoutModel   = new Qss_Model_Warehouse_Inout();
		$inout        = $inoutModel->getInOutDataGroupByStockAndItemGroup($start, $end, $itemIOIDs, $stockIOID);
		$costTable    = $inoutModel->getCostTableOrderByStockAndItemGroup($start, $itemIOIDs, $stockIOID);
		$ret          = array();
		$firstOfStart = date('Y-m-01', strtotime($start));
		$firstOfEnd   = date('Y-m-01', strtotime($end));


		foreach($costTable as $item)
		{
			$code = @(int)$item->RefItem.'_'.@(int)$item->RefAttr.'_'.@(int)$item->RefStock;
			$ret[$code]['ItemCode']     = $item->ItemCode;
			$ret[$code]['ItemName']     = $item->ItemName;
			$ret[$code]['Attr']         = $item->Attr;
			$ret[$code]['UOM']          = $item->UOM;
			$ret[$code]['RefGroup']     = $item->RefGroup;
			$ret[$code]['RefStock']        = $item->RefStock;
			$ret[$code]['StockName']        = $item->StockName;
			$ret[$code]['StockCode']        = $item->StockCode;
			$ret[$code]['GroupName']        = $item->GroupName;
			$ret[$code]['Input']           = 0;
			$ret[$code]['Output']          = 0;

			if($item->HasCostTable == 2)
			{
				$ret[$code]['OpeningStock']    = $item->LastQty;
				$ret[$code]['EndingStock']     = $item->LastQty;
			}
			else
			{
				$ret[$code]['OpeningStock']    = $item->FirstQty;
				$ret[$code]['EndingStock']     = $item->LastQty;
			}
		}

		if(Qss_Lib_Date::compareTwoDate($firstOfStart, $start) == -1)
		{
			$inoutFromFirstDayOfStartToStart = $inoutModel->getInOutData($firstOfStart, $start, $itemIOIDs, $stockIOID);

			foreach($inoutFromFirstDayOfStartToStart as $item)
			{
				$code = @(int)$item->RefItem.'_'.@(int)$item->RefAttr.'_'.@(int)$item->RefStock;
				if(isset($ret[$code]['OpeningStock']))
				{
					$ret[$code]['OpeningStock'] += $item->Input;
					$ret[$code]['OpeningStock'] -= $item->Output;
				}
				else
				{
					$ret[$code]['ItemCode']      = $item->ItemCode;
					$ret[$code]['ItemName']      = $item->ItemName;
					$ret[$code]['Attr']          = $item->Attr;
					$ret[$code]['UOM']           = $item->UOM;
					$ret[$code]['RefGroup']     = $item->RefGroup;
					$ret[$code]['RefStock']        = $item->RefStock;
					$ret[$code]['StockName']        = $item->StockName;
					$ret[$code]['StockCode']        = $item->StockCode;
					$ret[$code]['GroupName']        = $item->GroupName;
					$ret[$code]['OpeningStock']  = 0;
					$ret[$code]['OpeningStock'] += $item->Input;
					$ret[$code]['OpeningStock'] -= $item->Output;
				}
			}
		}

		foreach ($inout as $item)
		{
			$code  = @(int)$item->RefItem.'_'.@(int)$item->RefAttr.'_'.@(int)$item->RefStock;
			$first = 0;

			if(isset($ret[$code]['OpeningStock']) && $ret[$code]['OpeningStock'])
			{
				$first = $ret[$code]['OpeningStock'];
			}

			$last  = $first;
			$last += $item->Input;
			$last -= $item->Output;

			$ret[$code]['ItemCode']        = $item->ItemCode;
			$ret[$code]['ItemName']        = $item->ItemName;
			$ret[$code]['Attr']            = $item->Attr;
			$ret[$code]['UOM']             = $item->UOM;
			$ret[$code]['Input']           = $item->Input;
			$ret[$code]['Output']          = $item->Output;
			$ret[$code]['RefGroup']        = $item->RefGroup;
			$ret[$code]['RefStock']        = $item->RefStock;
			$ret[$code]['StockName']        = $item->StockName;
			$ret[$code]['StockCode']        = $item->StockCode;
			$ret[$code]['GroupName']        = $item->GroupName;
			$ret[$code]['OpeningStock']    = $first;
			$ret[$code]['EndingStock']     = $last;
		}

		foreach ($ret as $key=>$item)
		{
			// Neu khong co so luong dau ky va khong co nhap xuat kho thi khong hien ra
			if($hasFirstQty && !$item['OpeningStock'] && !$item['Input']  && !$item['Output'])
			{
				unset($ret[$key]);
			}

			// Neu khong co giao dich kho trong ky, co the van co so luong dau ky
			if($hasTransaction && !$item['Input']  && !$item['Output'])
			{
				unset($ret[$key]);
			}
		}
		return $ret;
	}

	/**
	 * @note: gia tri tinh bang ngay dau tien, neu o hai thang khac nhau thi gia co the bi lech
	 * @param type $start
	 * @param type $end
	 * @param type $itemIOIDs
	 * @param type $stockIOID
	 * @param type $hasAll
	 * @param type $hasFirstQty
	 * @param type $hasTransaction
	 * @return type
	 */
	private function getInOutData(
	$start
	, $end
	, $itemIOIDs = array()
	, $stockIOID = 0
	, $hasAll = 0
	, $hasFirstQty = 0
	, $hasTransaction = 0)
	{
		$inoutModel   = new Qss_Model_Warehouse_Inout();
		$inout        = $inoutModel->getInOutData($start, $end, $itemIOIDs, $stockIOID);
		$costTable    = $inoutModel->getCostTable($start, $itemIOIDs, $stockIOID);
		$ret          = array();
		$firstOfStart = date('Y-m-01', strtotime($start));
		$firstOfEnd   = date('Y-m-01', strtotime($end));


		foreach($costTable as $item)
		{
			$code = @(int)$item->RefItem.'_'.@(int)$item->RefAttr;
			$ret[$code]['ItemCode']     = $item->ItemCode;
			$ret[$code]['ItemName']     = $item->ItemName;
			$ret[$code]['Attr']         = $item->Attr;
			$ret[$code]['UOM']          = $item->UOM;
			$ret[$code]['Price']        = $item->Price;

			if($item->HasCostTable == 2)
			{
				$ret[$code]['Input']           = 0;
				$ret[$code]['Output']          = 0;
				$ret[$code]['OpeningStock']    = $item->LastQty;
				$ret[$code]['EndingStock']     = $item->LastQty;
			}
			else
			{
				$ret[$code]['Input']           = $item->Input;
				$ret[$code]['Output']          = $item->Output;
				$ret[$code]['OpeningStock']    = $item->FirstQty;
				$ret[$code]['EndingStock']     = $item->LastQty;
			}

			$ret[$code]['InputVal']        = $ret[$code]['Input'] * $item->Price;
			$ret[$code]['OutputVal']       = $ret[$code]['Output'] * $item->Price;
			$ret[$code]['OpeningStockVal'] = $ret[$code]['OpeningStock'] * $item->Price;
			$ret[$code]['EndingStockVal']  = $ret[$code]['EndingStock'] * $item->Price;
		}

		if(Qss_Lib_Date::compareTwoDate($firstOfStart, $start) == -1)
		{
			$inoutFromFirstDayOfStartToStart = $inoutModel->getInOutData($firstOfStart, $start, $itemIOIDs, $stockIOID);

			foreach($inoutFromFirstDayOfStartToStart as $item)
			{
				$code = @(int)$item->RefItem.'_'.@(int)$item->RefAttr;
				if(isset($ret[$code]['OpeningStock']))
				{
					$ret[$code]['OpeningStock'] += $item->Input;
					$ret[$code]['OpeningStock'] -= $item->Output;
				}
				else
				{
					$ret[$code]['ItemCode']      = $item->ItemCode;
					$ret[$code]['ItemName']      = $item->ItemName;
					$ret[$code]['Attr']          = $item->Attr;
					$ret[$code]['UOM']           = $item->UOM;
					$ret[$code]['OpeningStock']  = 0;
					$ret[$code]['OpeningStock'] += $item->Input;
					$ret[$code]['OpeningStock'] -= $item->Output;
				}
			}
		}

		foreach ($inout as $item)
		{
			$code  = @(int)$item->RefItem.'_'.@(int)$item->RefAttr;
			$price = 0;
			$first = 0;

			if(isset($ret[$code]['Price']) && $ret[$code]['Price'])
			{
				$price = $ret[$code]['Price'];
			}

			if(isset($ret[$code]['OpeningStock']) && $ret[$code]['OpeningStock'])
			{
				$first = $ret[$code]['OpeningStock'];
			}

			$last  = $first;
			$last += $item->Input;
			$last -= $item->Output;

			$ret[$code]['ItemCode']        = $item->ItemCode;
			$ret[$code]['ItemName']        = $item->ItemName;
			$ret[$code]['Attr']            = $item->Attr;
			$ret[$code]['UOM']             = $item->UOM;
			$ret[$code]['Input']           = $item->Input;
			$ret[$code]['Output']          = $item->Output;
			$ret[$code]['OpeningStock']    = $first;
			$ret[$code]['EndingStock']     = $last;
			$ret[$code]['InputVal']        = $item->Input * $price;
			$ret[$code]['OutputVal']       = $item->Output * $price;
			$ret[$code]['OpeningStockVal'] = $first * $price;
			$ret[$code]['EndingStockVal']  = $last * $price;
		}


		foreach ($ret as $key=>$item)
		{
			// Neu khong co so luong dau ky va khong co nhap xuat kho thi khong hien ra  && !$item['Input']  && !$item['Output']
			if($hasFirstQty && !$item['OpeningStock'])
			{
				unset($ret[$key]);
			}

			// Neu khong co giao dich kho trong ky, co the van co so luong dau ky
			if($hasTransaction && !$item['Input']  && !$item['Output'])
			{
				unset($ret[$key]);
			}
		}
		return $ret;
	}

	
	public function barcodePrintAction()
	{

	}

	public function barcodePrint1Action()
	{

	}

	private function groupItemByGroups($items)
	{
        //echo '<pre>'; print_r($items); die;
		$oldGroup = '';
		$retItems = array();
		$i = 0;
		$i1 = 0;
		$i2 = 0;

		foreach ($items as $it)
		{

            $it->Ref_NhomSanPham = @(int)$it->Ref_NhomSanPham;
			if ($oldGroup != $it->Ref_NhomSanPham)
			{
				$i2 = 0;
				$retItems[$i1]['GroupID']   = $it->Ref_NhomSanPham;//0 ko phan group
				$retItems[$i1]['GroupName'] = $it->NhomSanPham;
				$i++;
			}
            if($it->IOID)
            {
				$retItems[$i1]['Dat'][$i2]['Display'] = $it->TenSanPham . ' ' . $it->MaSanPham;
				$retItems[$i1]['Dat'][$i2]['ID']      = $it->IOID;
				$i2++;
            }

		}

		return $retItems;

	}
	/**
	 * In ton kho theo bin
	 * - Loc theo kho, bin, san pham
	 * - In ra gia tri ton kho cua san pham nhom theo bin va kho
	 */
	public function binAction()
	{

	}

	public function bin1Action()
	{
		$warehouse = $this->params->requests->getParam('warehouse', 0); // IFID
		$bin       = $this->params->requests->getParam('bin', 0); // IOID
		$item      = $this->params->requests->getParam('item', 0); // IOID

		$WModel    = new Qss_Model_Extra_Warehouse();
		$data      = $WModel->getCurrentInventoryForBinReport($warehouse, $bin
		, $item);

		$sort      = array();

		foreach($data as $val)
		{
			$sort[$val->WIOID]['Code']                     = $val->WCode;
			$sort[$val->WIOID]['Name']                     = $val->WName;
			$sort[$val->WIOID]['Bin'][$val->BIOID]['Code'] = $val->BCode;
			$sort[$val->WIOID]['Bin'][$val->BIOID]['Name'] = $val->BName;
			$sort[$val->WIOID]['Bin'][$val->BIOID]['Data'][] = $val;
		}

		$this->html->report = $sort;
	}
	/**************************************************************************/

	/**
	 * Bao cao so sanh so luong giua bao tri va xuat nhap kho
	 */
	public function recognizeAction()
	{

	}

	public function recognize1Action()
	{
		$start   = $this->params->requests->getParam('start', 0);
		$end     = $this->params->requests->getParam('end', 0);
		$loc     = $this->params->requests->getParam('location', 0);
		$EqGroup = $this->params->requests->getParam('group', 0);
		$EqType  = $this->params->requests->getParam('type', 0);
		$EqID    = $this->params->requests->getParam('equip', 0);

		$model = new Qss_Model_Extra_Warehouse();
		$ret   = array();

		$output   = $model->getOutputForRecognizeReport($start, $end, $loc
		, $EqGroup, $EqType, $EqID);
		$input    = $model->getInputForRecognizeReport($start, $end, $loc
		, $EqGroup, $EqType, $EqID);
		$MaintOrder = $model->getMaintainOrderForRecognizeReport($start, $end, $loc
		, $EqGroup, $EqType, $EqID);


		// Xuat kho
		foreach($output as $out)
		{
			$code = $out->EID .'-'. $out->ComponentID;
			// Group
			if(!isset($ret[$code]))
			{
				$ret[$code]['ECode']     = $out->ECode;
				$ret[$code]['Component'] = $out->Component;
			}
				
			if(!isset($ret[$code]['Item'][$out->IID]))
			{
				$ret[$code]['Item'][$out->IID]['ID']   = $out->IID;
				$ret[$code]['Item'][$out->IID]['Code'] = $out->ICode;
				$ret[$code]['Item'][$out->IID]['Name'] = $out->IName;
				$ret[$code]['Item'][$out->IID]['UOM']  = $out->UOM;
			}
				
			$ret[$code]['Item'][$out->IID]['Out'] = $out->Qty;
		}


		// Nhap kho
		foreach($input as $in)
		{
			$code = $in->EID .'-'. $in->ComponentID;
			// Group
			if(!isset($ret[$code]))
			{
				$ret[$code]['ECode']     = $in->ECode;
				$ret[$code]['Component'] = $in->Component;
			}
				
				
			if(!isset($ret[$code]['Item'][$in->IID]))
			{
				$ret[$code]['Item'][$in->IID]['ID']   = $in->IID;
				$ret[$code]['Item'][$in->IID]['Code'] = $in->ICode;
				$ret[$code]['Item'][$in->IID]['Name'] = $in->IName;
				$ret[$code]['Item'][$in->IID]['UOM']  = $in->UOM;
			}
				
				
			$ret[$code]['Item'][$in->IID]['In']     = $in->Qty;
				
			if(isset($ret[$code]['Item'][$in->IID]['Out']) && $ret[$code]['Item'][$in->IID]['Out'])
			{
				$ret[$code]['Item'][$in->IID]['InLost'] = $ret[$code]['Item'][$in->IID]['Out'] - $in->Qty;
			}
			else
			{
				$ret[$code]['Item'][$in->IID]['InLost'] = 0;
			}
		}

		// Phieu bao tri
		foreach($MaintOrder as $order)
		{
			$code = $order->EID .'-'. $order->ComponentID;
			// Group
			if(!isset($ret[$code]))
			{
				$ret[$code]['ECode']     = $order->ECode;
				$ret[$code]['Component'] = $order->Component;
			}
				
			if(!isset($ret[$code]['Item'][$order->IID]))
			{
				$ret[$code]['Item'][$order->IID]['ID']   = $order->IID;
				$ret[$code]['Item'][$order->IID]['Code'] = $order->ICode;
				$ret[$code]['Item'][$order->IID]['Name'] = $order->IName;
				$ret[$code]['Item'][$order->IID]['UOM']  = $order->UOM;
			}
				
			$ret[$code]['Item'][$order->IID]['Use']    = $order->Use;
			$ret[$code]['Item'][$order->IID]['Return'] = $order->Return;
			$ret[$code]['Item'][$order->IID]['Lost']   = $order->Lost;
		}
		$this->html->report = $ret;

	}
}

?>