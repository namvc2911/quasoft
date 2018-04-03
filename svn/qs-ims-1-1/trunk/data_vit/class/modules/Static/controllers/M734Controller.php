<?php

/**
 *
 * @author: ThinhTuan
 * @todo: bo ca ham thua, /cost/manufacturing, /cost/group/
 * @todo: gop ba bao cao ve dung may theo ky, nhom, khu vuc
 * @todo: Can them dieu kien cho mot so bao cao ve pbt voi tinh trang pbt la hoan thanh
 * @todo: Sua bao cao m750
 */
class Static_M734Controller extends Qss_Lib_Controller
{
    public function init()
    {
        parent::init();
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
    }


    public function indexAction()
    {

    }

    public function showAction()
    {
        $countM759  = array();
        $oldM759    = '';
        $start      = $this->params->requests->getParam('start', '');
        $end        = $this->params->requests->getParam('end', '');
        $material   = $this->params->requests->getParam('material', 0);
        $location   = $this->params->requests->getParam('location', 0);
        $type       = $this->params->requests->getParam('type', 0);
        $group      = $this->params->requests->getParam('group', 0);
        $equip      = $this->params->requests->getParam('equipment', 0);
        $mOMaterial = new Qss_Model_Maintenance_Workorder_Material();
        $mLocation  = new Qss_Model_Maintenance_Location();
        $maintain   = $mOMaterial->getMaterialByRangeTimeGroupByWorkOrder(
            Qss_Lib_Date::displaytomysql($start)
            , Qss_Lib_Date::displaytomysql($end)
            , $material
            , $location
            , $type
            , $group
            , $equip
        );


        // Dem so luong vat tu theo tung phieu bao tri
        foreach($maintain as $item)
        {
            if($oldM759 !== $item->IFID_M759)
            {
                if($oldM759 !== '')
                {
                    $countM759[$oldM759] = $count; // Sau can +1 cho tieu de cua phieu trong phan in
                }
                $count = 0;
            }
            $count++;
            $oldM759 = $item->IFID_M759;
        }

        if($oldM759 !== '')
        {
            $countM759[$oldM759] = $count;
        }

        $this->html->start           = $start;
        $this->html->end             = $end;
        $this->html->defaultCurrency = Qss_Lib_Extra::getDefaultCurrency();
        $this->html->maintain        = $maintain;
        $this->html->count           = $countM759;
        $this->html->location        = $mLocation->getLocationByIOID($location);

    }

}

?>