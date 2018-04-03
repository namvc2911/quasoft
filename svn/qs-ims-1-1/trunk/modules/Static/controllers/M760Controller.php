<?php
class Static_M760Controller extends Qss_Lib_Controller
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
        $mInventory = new Qss_Model_Inventory_Inventory();
        $start      = $this->params->requests->getParam('start', '');
        $end        = $this->params->requests->getParam('end', '');
        $warehouse  = $this->params->requests->getParam('warehouse', 0);

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
}
