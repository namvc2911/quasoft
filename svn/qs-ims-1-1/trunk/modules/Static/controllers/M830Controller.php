<?php
class Static_M830Controller extends Qss_Lib_Controller
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
        $mMaintain      = new Qss_Model_Extra_Maintain();
        $start          = $this->params->requests->getParam('start', '');
        $end            = $this->params->requests->getParam('end', '');
        $location       = $this->params->requests->getParam('location', '');
        $group          = $this->params->requests->getParam('group', '');
        $type           = $this->params->requests->getParam('type', '');
        $equip          = $this->params->requests->getParam('equip', '');

        $report         = $this->getReportData(
            Qss_Lib_Date::displaytomysql($start)
            , Qss_Lib_Date::displaytomysql($end)
            , $location
            , $group
            , $type
            , $equip
        );

        $this->html->month  = date('m', strtotime($start));
        $this->html->year   = date('Y', strtotime($start));
        $this->html->report = $report;
    }

    /**
     * @todo: Sau co the can loc loai thiet bi theo dieu kien chu khong phai lay tat nhu code hien tai
     * @todo: Sau can dem phieu bao tri theo buoc
     * @param $start
     * @param $end
     * @param $location
     * @param $group
     * @param $type
     * @param $equip
     */
    private function getReportData($start, $end, $location, $group, $type, $equip)
    {
        $mMaintain = new Qss_Model_Extra_Maintain();
        $mEquip    = new Qss_Model_Maintenance_Equipment();
        $mPlan     = new Qss_Model_Maintenance_GeneralPlans();
        $oNhomTB   = array();
        $orders    = $mMaintain->countSoLanBaoTriTheoNhomThietBi($start, $end, $location, $group, $type, $equip);
        $groups    = $mEquip->getEquipGroups();
        $plans     = $mPlan->countPlansByEquipGroups($start, $end , $this->_user->user_dept_id,$location, $group, $type, $equip);

        foreach($plans as $item)
        {
            $oNhomTB[$item->Ref_NhomThietBi]['KeHoach'] = $item->SoLuongTheoNhom;
        }

        foreach($orders as $item)
        {
            $oNhomTB[$item->Ref_NhomThietBi]['BaoTri']  = $item->Total;
        }

        foreach($groups as $item)
        {
            $item->SoLuongKeHoach = 0;
            $item->SoLanBaoTri    = 0;

            if(isset($oNhomTB[$item->IOID]['KeHoach']))
            {
                $item->SoLuongKeHoach = $oNhomTB[$item->IOID]['KeHoach'];
            }

            if(isset($oNhomTB[$item->IOID]['BaoTri']))
            {
                $item->SoLanBaoTri = $oNhomTB[$item->IOID]['BaoTri'];
            }
        }

        return $groups;

    }
}
