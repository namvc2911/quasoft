<?php
class Static_M748Controller extends Qss_Lib_Controller
{
    public function init()
    {
        parent::init();
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
    }


    public function indexAction()
    {
        $common                = new Qss_Model_Extra_Extra();
        $loaiBaoTriDialBoxData = array();
        $loaiBaoTri            = $common->getTable(array('*'), 'OPhanLoaiBaoTri', array(), array('Loai'));
        $i                     = 0;

        foreach($loaiBaoTri as $dat)
        {
            $loaiBaoTriDialBoxData[0]['Dat'][$i]['ID']      = $dat->IOID;
            $loaiBaoTriDialBoxData[0]['Dat'][$i]['Display'] = $dat->Loai;
            $i++;
        }
        $this->html->loaiBaoTriDialBoxData = $loaiBaoTriDialBoxData;
    }

    public function showAction()
    {
        $mPlan            = new Qss_Model_Maintenance_Plan();
        $mLocation        = new Qss_Model_Maintenance_Location();
        $mEquip           = new Qss_Model_Maintenance_Equip_List();
        $equipIOID        = $this->params->requests->getParam('equipment', 0);
        $maintTypeIOIDArr = $this->params->requests->getParam('maintype', array());
        $locationIOID     = $this->params->requests->getParam('location', 0);
        $eqGroupIOID      = $this->params->requests->getParam('group', 0);
        $eqTypeIOID       = $this->params->requests->getParam('type', 0);
        $equipDetail      = $mEquip->getEquipByIOID($equipIOID);
        $locationDetail   = $equipDetail?$mLocation->getLocationByIOID((int)$equipDetail->Ref_MaKhuVuc):NULL;

        $planWorks        = $mPlan->getPlanConfigs($locationIOID, $eqTypeIOID, $eqGroupIOID, $equipIOID);


//            $mPlan->getPlanDetail(
//            $filterdate = ''
//            , $locationIOID
//            , $eqTypeIOID
//            , $eqGroupIOID
//            , 0
//            , $maintTypeIOIDArr
//            , $equipIOID
//        );

        $this->html->report   = $planWorks;
        $this->html->equip    = $equipDetail;
        $this->html->location = $locationDetail;
    }

}

?>