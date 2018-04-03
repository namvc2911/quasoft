<?php

class Extra_DashboardController extends Qss_Lib_Controller {

    public $_user;

    public function init()
    {
        parent::init();
        $this->_user = Qss_Register::get('userinfo');
    }

    public function newsTasksAction()
    {
        $day   = $this->params->requests->getParam('day',date('d'));
        $month = $this->params->requests->getParam('month',date('m'));
        $year  = $this->params->requests->getParam('year',date('Y'));

        $this->html->monthView = $this->views->Extra->Calendar->Month($day,$month,$year, 'showTaskByDate');
    }

    // KE HOACH SUA CHUA TRONG NGAY
    public function newsPlantasksAction()
    {
        $date  = $this->params->requests->getParam('pdate', date('d-m-Y'));
        $day   = $this->params->requests->getParam('day',date('d', strtotime($date)));
        $month = $this->params->requests->getParam('month',date('m', strtotime($date)));
        $year  = $this->params->requests->getParam('year',date('Y', strtotime($date)));

        $this->html->monthView = $this->views->Extra->Workorder->Calendar->Select->Day($day, $month, $year);
    }

    public function newsPlantasks1Action()
    {
        $wo    = new Qss_Model_Maintenance_Workorder();
        $day   = $this->params->requests->getParam('day',date('d'));
        $month = $this->params->requests->getParam('month',date('m'));
        $year  = $this->params->requests->getParam('year',date('Y'));
        $date  = $year.'-'.str_pad($month, 2, "0", STR_PAD_LEFT).'-'.str_pad($day, 2, "0", STR_PAD_LEFT);

        $this->html->data      = $wo->getTasksByDate($date);
        $this->html->monthView = $this->views->Extra->Workorder->Calendar->Select->Day($day, $month, $year);
        $this->html->deptid    = $this->_user->user_dept_id;
        $this->html->date      = $date;
    }

    public function newsPlantasks2Action()
    {
        $wo    = new Qss_Model_Maintenance_Workorder();
        $day   = $this->params->requests->getParam('day',date('d'));
        $month = $this->params->requests->getParam('month',date('m'));
        $year  = $this->params->requests->getParam('year',date('Y'));
        $date  = $year.'-'.str_pad($month, 2, "0", STR_PAD_LEFT).'-'.str_pad($day, 2, "0", STR_PAD_LEFT);

        $this->html->monthView = $this->views->Extra->Workorder->Calendar->Select->Day($day, $month, $year);
    }

    public function newsPlantasks3Action()
    {
        $wo    = new Qss_Model_Maintenance_Workorder();
        $date  =  $this->params->requests->getParam('date',date('Y-m-d'));
        $sdeptid = $this->params->requests->getParam('deptid',0 );

        $this->html->data      = $wo->getTasksByDate($date);
        $this->html->sdeptid   = $sdeptid;

    }

    // KE HOACH SUA CHUA TRONG NGAY
    public function newsPlantasksreportAction()
    {
        $date               = $this->params->requests->getParam('prdate',date('d-m-Y',strtotime("-1 days")));
        $news               = new Qss_Model_Extra_News();
        $this->html->news   = $news->getPlanTasks(Qss_Lib_Date::displaytomysql($date));
        $this->html->deptid = $this->_user->user_dept_id;
        $this->html->dept   = $this->_user->user_dept_name;
        $this->html->date   = $date;
    }

    // Báo cáo sản xuất
    public function newsManufacturingsAction()
    {
        $date  = $this->params->requests->getParam('madate', date('d-m-Y',strtotime("-1 days")));
        $news               = new Qss_Model_Extra_News();
        $this->html->news   = $news->getManufacturingReport(Qss_Lib_Date::displaytomysql($date));
        $this->html->tt     = $news->getSanLuongCuaTt1Tt2Tt3(Qss_Lib_Date::displaytomysql($date));
        $this->html->deptid = $this->_user->user_dept_id;
        $this->html->dept   = $this->_user->user_dept_name;
        $this->html->date   = $date;
    }

    // Quy dinh/ sac lenh
    public function newsPolicyAction()
    {
        $news               = new Qss_Model_Extra_News();
        $this->html->news   = $news->getPolicy();
        $this->html->deptid = $this->_user->user_dept_id;
        $this->html->dept   = $this->_user->user_dept_name;
    }

    // Quan ly kien thuc
    public function newsKnowledgeAction()
    {
        $news               = new Qss_Model_Extra_News();
        $this->html->news   = $news->getKnowledge();
        $this->html->deptid = $this->_user->user_dept_id;
        $this->html->dept   = $this->_user->user_dept_name;
    }
	//Báo cáo nước công nghiệp
    public function newsWaterAction()
    {
        $date  = $this->params->requests->getParam('wdate', date('d-m-Y',strtotime("-1 days")));
        $news               = new Qss_Model_Extra_News();
        $this->html->news   = $news->getWaterReport(Qss_Lib_Date::displaytomysql($date));
        $tt1     = $news->getSanLuongTheoTuyen(Qss_Lib_Date::displaytomysql($date),'TT1');
        $tt2     = $news->getSanLuongTheoTuyen(Qss_Lib_Date::displaytomysql($date),'TT2');
        $t1= array();
        $t2 = array();
        foreach ($tt1 as $item)
        {
        	$t1[$item->Ngay] = $item->TongThanSach; 
        }
        
    	foreach ($tt2 as $item)
        {
        	$t2[$item->Ngay] = $item->TongThanSach;
        }
        $this->html->tt1 = $t1;
        $this->html->tt2 = $t2;
        $this->html->deptid = $this->_user->user_dept_id;
        $this->html->dept   = $this->_user->user_dept_name;
        $this->html->date   = $date;
    }

    public function newsElectricAction()
    {
        $date   = $this->params->requests->getParam('edate', date('d-m-Y'));
        $start  = date('Y-m-d', strtotime($date.' -1 days'));
        $news   = new Qss_Model_Extra_News();
        $data   = $news->getElectricReportByDate($start, Qss_Lib_Date::displaytomysql($date));
        $retval = array();

        foreach($data as $item)
        {
            $retval[$item->Ngay][$item->MaCa][$item->DeptCode] = $item->SanLuong;
        }

        $this->html->date = $date;
        $this->html->data = $retval;
    }
}