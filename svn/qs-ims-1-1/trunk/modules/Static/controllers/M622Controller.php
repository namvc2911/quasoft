<?php
class Static_M622Controller extends Qss_Lib_Controller
{
    public function init()
    {
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
        parent::init();
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
        $mInventory  = new Qss_Model_Inventory_Inventory();
        $mWorkcenter = new Qss_Model_Master_Workcenter();
        $start       = $this->params->requests->getParam('start', '');
        $end         = $this->params->requests->getParam('end', '');
        $warehouse   = $this->params->requests->getParam('warehouse', 0);

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
        $this->html->stock      = $mInventory->getWarehouseByIOID($warehouse);
        $this->html->workcenter = $mWorkcenter->getWorkcenterByIOID(@(int)$this->html->stock->Ref_DonViQuanLy);
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
}