<?php
class Static_M859Controller extends Qss_Lib_Controller
{
    public function init()
    {
        $this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
        parent::init();
        $this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
    }

    public function indexAction()
    {

    }

    public function showAction()
    {
        $mOrder      = new Qss_Model_M724_Material();
        $mInventory  = new Qss_Model_M602_Inventory();
        $sDate       = $this->params->requests->getParam('start');
        $eDate       = $this->params->requests->getParam('end');
        $eqGroupIOID = $this->params->requests->getParam('group');
        $eqTypeIOID  = $this->params->requests->getParam('type');
        $locIOID     = $this->params->requests->getParam('location');
        $mSDate      = Qss_Lib_Date::displaytomysql($sDate);
        $mEDate      = Qss_Lib_Date::displaytomysql($eDate);
        $planMaterials = $mOrder->getTotalMaterialsOfM724($mSDate, $mEDate, $locIOID, $eqGroupIOID, $eqTypeIOID);
        $items         = array();

        foreach($planMaterials as $refVatTu=>$vatTu) {
            $items[] = $refVatTu;
        }

        $this->html->print     = $mOrder->getTotalMaterialsOfM724($mSDate, $mEDate, $locIOID, $eqGroupIOID, $eqTypeIOID);
        $this->html->inventory = $mInventory->getArrayInventoryOfItems($items);
        $this->html->start     = $sDate;
        $this->html->end       = $eDate;
    }
}

?>