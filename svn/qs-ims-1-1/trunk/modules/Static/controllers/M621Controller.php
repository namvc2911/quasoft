<?php
class Static_M621Controller extends Qss_Lib_Controller
{
    public function init()
    {
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
    	parent::init();
        $this->headScript($this->params->requests->getBasePath() . '/js/report-list.js');
        $this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');
        $this->html->curl = $this->params->requests->getRequestUri();
    }

    public function indexAction()
    {
        $mItem  = new Qss_Model_Master_Item();
        $items  = $mItem->getItems();

        $this->html->groups = $mItem->getItemGroups();
        $this->html->items  = $this->groupItemByGroups($items);
    }

    public function showAction()
    {
        $common    = new Qss_Model_Extra_Extra();
        $mInventory = new Qss_Model_Inventory_Inventory();
        $start      = $this->params->requests->getParam('start', '');
        $end        = $this->params->requests->getParam('end', '');
        $warehouse  = $this->params->requests->getParam('warehouse', 0);

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
        $this->html->stock     = $mInventory->getWarehouseByIOID($warehouse);
    }

    public function show2Action()
    {
        $group = $this->params->requests->getParam('group', 0);
        $mItem = new Qss_Model_Master_Item();
        $items = $mItem->getItemsByGroup($group);

        $this->html->items = $this->groupItemByGroups($items);
    }

    private function groupItemByGroups($items)
    {
        $oldGroup = '';
        $retItems = array();
        $i        = 0;
        $i1       = 0;
        $i2       = 0;

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
            $code                    = @(int)$item->RefItem.'_'.@(int)$item->RefAttr.'_'.@(int)$item->RefStock;
            $ret[$code]['ItemCode']  = $item->ItemCode;
            $ret[$code]['ItemName']  = $item->ItemName;
            $ret[$code]['Attr']      = $item->Attr;
            $ret[$code]['UOM']       = $item->UOM;
            $ret[$code]['RefGroup']  = $item->RefGroup;
            $ret[$code]['RefStock']  = $item->RefStock;
            $ret[$code]['StockName'] = $item->StockName;
            $ret[$code]['StockCode'] = $item->StockCode;
            $ret[$code]['GroupName'] = $item->GroupName;
            $ret[$code]['Input']     = 0;
            $ret[$code]['Output']    = 0;

            if($item->HasCostTable == 2)
            {
                $ret[$code]['OpeningStock'] = $item->LastQty;
                $ret[$code]['EndingStock']  = $item->LastQty;
            }
            else
            {
                $ret[$code]['OpeningStock'] = $item->FirstQty;
                $ret[$code]['EndingStock']  = $item->LastQty;
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
                    $ret[$code]['RefGroup']      = $item->RefGroup;
                    $ret[$code]['RefStock']      = $item->RefStock;
                    $ret[$code]['StockName']     = $item->StockName;
                    $ret[$code]['StockCode']     = $item->StockCode;
                    $ret[$code]['GroupName']     = $item->GroupName;
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
            $ret[$code]['StockName']       = $item->StockName;
            $ret[$code]['StockCode']       = $item->StockCode;
            $ret[$code]['GroupName']       = $item->GroupName;
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
    public function excelAction()
    {
    	header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
		header("Content-disposition: attachment; filename=\"report.xlsx\"");
        $common    = new Qss_Model_Extra_Extra();
        $mInventory = new Qss_Model_Inventory_Inventory();
        $start      = $this->params->requests->getParam('start', '');
        $end        = $this->params->requests->getParam('end', '');
        $warehouse  = $this->params->requests->getParam('warehouse', 0);

        $report    = $this->getInOutDataOrderByStockAndGroup(
            Qss_Lib_Date::displaytomysql($start),
            Qss_Lib_Date::displaytomysql($end),
            $this->params->requests->getParam('items', array()),
            $warehouse,
            $this->params->requests->getParam('has_all', 0),
            $this->params->requests->getParam('has_inventory', 0),
            $this->params->requests->getParam('has_warehouse_transaction', 0)
        );
        $stock     = $mInventory->getWarehouseByIOID($warehouse);
        $m = new Qss_Model_Excel(QSS_DATA_DIR.'/template/M621/basic.xlsx');
        $main = new stdClass();
        $main->Kho = $stock->TenKho;
        $main->NgayBatDau = $start;
        $main->NgayKetThuc = $end;
        $data = array('main'=>$main);
		$m->init($data);
		$i = 10;
		$stt = 0;
        $oldStock = '';
       	$oldGroup = '';
       	foreach ($report as $item)
       	{
			/*if($oldStock != $item['RefStock'])
			{
				$stockTitle = $item['RefStock']?$item['StockCode'].' '.$item['StockName']:'Unknown stock';
				$oldGroup = '';// rese
				$stt =  0;
				$data = new stdClass();
				$data->Kho = $stockTitle;
				$m->newGridRow(array('sub'=>$data), $i, 9);
				$i++;
			}*/
			$oldStock = $item['RefStock'];
			if($oldGroup != $item['RefGroup'])
			{
				$groupTitle = $item['RefGroup']?$item['GroupName']:'Uncategory';
				$data = new stdClass();
				$data->NhomSP = $groupTitle;
				$m->newGridRow(array('sub'=>$data), $i, 8);
				$i++;
			}
            $oldGroup = $item['RefGroup'];
			$begin = 0;
			$data = new stdClass();
            $data->STT = ++$stt;
            $data->MaSP = $item['ItemCode'];
            $data->TenSP = $item['ItemName'];
            $data->DVT = $item['UOM'];
			$data->TonDau = @(double)$item['OpeningStock'];
			$data->Nhap = @(double)$item['Input'];
			$data->Xuat = @(double)$item['Output'];
			$data->TonCuoi = @(double)$item['EndingStock']; 
			$m->newGridRow(array('sub'=>$data), $i, 9);
			$i++;
      	}
		$m->removeRow(9);
        $m->removeRow(8);
        //$m->removeRow(9);
		$m->save();
		die();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
    }
    
}
?>