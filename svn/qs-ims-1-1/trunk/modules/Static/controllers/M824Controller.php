<?php
class Static_M824Controller extends Qss_Lib_Controller
{
    public function init()
    {
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
        parent::init();
        $this->headScript($this->params->requests->getBasePath() . '/js/report-list.js'); //lay js
        $this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');
        $this->html->curl = $this->params->requests->getRequestUri(); //lay crurl (giong data) de su dung trong html
    }

    public function indexAction()
    {

    }

    public function showAction()
    {
        $mShift         = new Qss_Model_Master_Shift();
        $mDaily         = new Qss_Model_Extra_Maintain();
        $start          = $this->params->requests->getParam('start', '');
        $end            = $this->params->requests->getParam('end', '');
        $location       = $this->params->requests->getParam('location', '');
        $group          = $this->params->requests->getParam('group', '');
        $type           = $this->params->requests->getParam('type', '');
        $equip          = $this->params->requests->getParam('equip', '');

        $activeByShifts = array();
        $oDaiLy         = new stdClass();
        $daily          = $mDaily->getThoiGianHoatDongCuaThietBi(
            Qss_Lib_Date::displaytomysql($start)
            , Qss_Lib_Date::displaytomysql($end)
            , $location
            , $group
            , $type
            , $equip
        );
        $shifts         = $mShift->getShifts();
        $aDate          = Qss_Lib_Extra::displayRangeDate(
            Qss_Lib_Date::displaytomysql($start)
            , Qss_Lib_Date::displaytomysql($end)
            , 'D'
        );


        foreach($daily as $item)
        {
            // Lay so hoat dong theo ngay va ca
            if($item->NgayNhatTrinh && $item->Ref_Ca)
            {
                if(isset($activeByShifts[$item->IOID][$item->NgayNhatTrinh][$item->Ref_Ca]))
                {
                    $activeByShifts[$item->IOID][$item->NgayNhatTrinh][$item->Ref_Ca] = 0;
                }

                $activeByShifts[$item->IOID][$item->NgayNhatTrinh][$item->Ref_Ca] += $item->SoHoatDong;
            }

            // Chi lay thiet bi mot lan vao mang hien thi
            if(!isset($oDaiLy->{$item->IFID_M705}))
            {
                $oDaiLy->{$item->IFID_M705} = new stdClass();
                $oDaiLy->{$item->IFID_M705} = $item;
            }
        }
        // echo '<pre>'; print_r($oDaiLy); die;

        $this->html->aDate          = $aDate;
        $this->html->countDate      = count($aDate);
        $this->html->shifts         = $shifts;
        $this->html->countShifts    = count($shifts);
        $this->html->activeByShifts = $activeByShifts;
        $this->html->oDaily         = $oDaiLy;
        $this->html->start          = $start;
    }
}
