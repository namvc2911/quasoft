<?php
class Static_M608Controller extends Qss_Lib_Controller
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
        //	$this->v_fCheckRightsOnForm(155);
        $model   = new Qss_Model_Extra_Warehouse();
        $mCommon = new Qss_Model_Extra_Extra();

        $items = $model->getAllItems();
        $this->html->groups = $mCommon->getTable(array('*'), 'ONhomSanPham', array(), array('TenNhom'), 'NO_LIMIT');
        // $this->html->items  = $this->groupItemByGroups($items);

    }

    public function showAction()
    {
        $mInventory    = new Qss_Model_Inventory_Inventory();
        $itemIOIDs     = $this->params->requests->getParam('items', array());
        $warehouseIOID = $this->params->requests->getParam('warehouse', 0);
        $alertType     = $this->params->requests->getParam('alerttype', array());

        $this->html->report = $mInventory->getCurrentInventory($itemIOIDs, $warehouseIOID, $alertType);


        /*
        $model  = new Qss_Model_Extra_Warehouse();
        $params = $this->params->requests->getParams();

        $this->html->inOut     = $model->getStockVolume(Qss_Lib_Date::displaytomysql($params['date']), $params['items'], $params['warehouse']);
        $this->html->inventory = $model->getInventory(Qss_Lib_Date::displaytomysql($params['date']), $params['items'], $params['warehouse']);
        */
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
}
?>