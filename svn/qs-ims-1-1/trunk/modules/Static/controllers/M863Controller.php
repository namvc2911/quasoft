<?php
class Static_M863Controller extends Qss_Lib_Controller
{
    public function init()
    {
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
        parent::init();
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
    }

    public function indexAction()
    {

    }

    public function showAction()
    {
        $mRequest = new Qss_Model_M759_Material();

        $start    = $this->params->requests->getParam('start');
        $end      = $this->params->requests->getParam('end');
        $location = $this->params->requests->getParam('location');
        $group    = $this->params->requests->getParam('group');
        $type     = $this->params->requests->getParam('type');
        $equip    = $this->params->requests->getParam('equipment');

        $this->html->report = $mRequest->trackMaterials(
            Qss_Lib_Date::displaytomysql($start)
            , Qss_Lib_Date::displaytomysql($end)
            , $location
            , $group
            , $type
            , $equip
        );
        $this->html->start  = $start;
        $this->html->end    = $end;
    }
}