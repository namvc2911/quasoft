<?php
class Static_M784Controller extends Qss_Lib_Controller
{
    public function init()
    {
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
        parent::init();
    }

    public function indexAction()
    {

    }

    public function showAction()
    {
        $WModel    = new Qss_Model_Extra_Warehouse();
        $sort      = array();

        $warehouse = $this->params->requests->getParam('warehouse', 0); // IFID
        $bin       = $this->params->requests->getParam('bin', 0); // IOID
        $item      = $this->params->requests->getParam('item', 0); // IOID

        $data      = $WModel->getCurrentInventoryForBinReport($warehouse, $bin, $item);

        foreach($data as $val)
        {
            $sort[$val->WIOID]['Code']                       = $val->WCode;
            $sort[$val->WIOID]['Name']                       = $val->WName;
            $sort[$val->WIOID]['Bin'][$val->BIOID]['Code']   = $val->BCode;
            $sort[$val->WIOID]['Bin'][$val->BIOID]['Name']   = $val->BName;
            $sort[$val->WIOID]['Bin'][$val->BIOID]['Data'][] = $val;
        }

        $this->html->report = $sort;
    }
}
