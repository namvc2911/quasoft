<?php
/**
 * User: huy.bv
 * Date: 4/14/2017
 * Time: 3:36 PM
 */
class Static_M849Controller extends Qss_Lib_Controller
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
        $mIndicator = new Qss_Model_Maintenance_Equip_Indicator();
        $inDecator  = $this->params->requests->getParam('checkBox_diemDo',0);
        $start      = $this->params->requests->getParam('start', '');
      
        $end        = $this->params->requests->getParam('end', '');

        $data       = $mIndicator->showChartByIndicator($inDecator,
                        Qss_Lib_Date::displaytomysql($start),Qss_Lib_Date::displaytomysql($end));

        $chartValue = array();
        foreach($data as $item)
        {
            if(!is_array($chartValue[$item->Ref_DiemDo]))
            {
                $chartValue[$item->Ref_DiemDo] = array();
            }
            $chartValue[$item->Ref_DiemDo][] = $item;
        }
        $this->html->report = $chartValue;
    }
}