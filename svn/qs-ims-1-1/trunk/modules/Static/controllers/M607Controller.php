<?php
class Static_M607Controller extends Qss_Lib_Controller
{
    public function init()
    {
        parent::init();
    }

    public function indexAction()
    {
        $this->html->stocks      = $this->getStocks();
        $this->html->inputTypes  = $this->getInputTypes();
        $this->html->outputTypes = $this->getOutputTypes();
    }

    public function showAction()
    {
        $mInv            = new Qss_Model_Inventory_Inventory();
        $inTrue          = $this->params->requests->getParam('input', false);
        $outTrue         = $this->params->requests->getParam('output', false);
        $start           = $this->params->requests->getParam('start', '');
        $end             = $this->params->requests->getParam('end', '');
        $stockIOID       = $this->params->requests->getParam('stock', 0);
        $inputTypeIOID   = $this->params->requests->getParam('input_type', 0);
        $outputTypeIOID  = $this->params->requests->getParam('output_type', 0);
        $itemIOID        = $this->params->requests->getParam('item', 0);
        $page            = $this->params->requests->getParam('einfo_history_page', 1);
        $display         = $this->params->requests->getParam('einfo_history_display', 20);
        $total           = $mInv->countTransaction(
            $inTrue
            , $outTrue
            , Qss_Lib_Date::displaytomysql($start)
            , Qss_Lib_Date::displaytomysql($end)
            , $stockIOID
            , $inputTypeIOID
            , $outputTypeIOID
            , $itemIOID
        );
        $total           = $total?$total->Total:0;
        $cpage           = ceil($total / $display);


        $this->html->deptid    = $this->_user->user_dept_id;
        $this->html->report    = $mInv->getTransaction(
            $inTrue
            , $outTrue
            , Qss_Lib_Date::displaytomysql($start)
            , Qss_Lib_Date::displaytomysql($end)
            , $stockIOID
            , $inputTypeIOID
            , $outputTypeIOID
            , $itemIOID
            , $page
            , $display
        );
        $this->html->page      = $page;
        $this->html->display   = $display;
        $this->html->totalPage = $cpage?$cpage:1;
        $this->html->next      = ($page < $cpage)?($page + 1):$cpage;
        $this->html->back      = ($page > 1)?($page - 1):1;
        $this->html->sttAdd    = ($page - 1) * $display;
    }

    public function getStocks()
    {
        $mStock = new Qss_Model_Inventory_Inventory();
        $stocks = $mStock->getWarehouses();
        $ret    = array();

        foreach($stocks as $item)
        {
            $ret[$item->IOID] = "{$item->MaKho} - {$item->TenKho}";
        }
        return $ret;
    }

    public function getInputTypes()
    {
        $mStock = new Qss_Model_Inventory_Inventory();
        $stocks = $mStock->getInputTypes();
        $ret    = array();

        foreach($stocks as $item)
        {
            $ret[$item->IOID] = "{$item->Ten}";
        }
        return $ret;
    }

    public function getOutputTypes()
    {
        $mStock = new Qss_Model_Inventory_Inventory();
        $stocks = $mStock->getOutputTypes();
        $ret    = array();

        foreach($stocks as $item)
        {
            $ret[$item->IOID] = "{$item->Ten}";
        }
        return $ret;
    }

    public function itemsAction()
    {
        $params = $this->params->requests->getParams();
        $mItem  = new Qss_Model_Master_Item();
        $items  = $mItem->getItemByCodeOrName($params['tag']);
        $retval = array();

        foreach ($items as $item)
        {
            $display  = "{$item->MaSanPham} - {$item->TenSanPham}";
            $retval[] = array('id'=>$item->IOID, 'value'=>$display);
        }

        echo Qss_Json::encode($retval);
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
}