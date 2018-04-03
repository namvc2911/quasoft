<?php

/**
 * Class Static_M816Controller
 * Nhập chỉ số hoạt động của thiết bị
 */
class Static_M765Controller extends Qss_Lib_Controller
{
    public function init()
    {
        parent::init();
        $this->headLink($this->params->requests->getBasePath() . '/css/print.css');
    }

    public function indexAction()
    {
        // Lay nhan vien nhap lieu mac dinh theo user hien tai
        $mEmployee        = new Qss_Model_Maintenance_Employee();
        $employee         = $mEmployee->getEmployeeByUserID($this->_user->user_id);
        $displayEmployee  = $employee?"{$employee->MaNhanVien} - {$employee->TenNhanVien}":'';

        // Lay thong tin khac
        $date             = $this->params->requests->getParam('input_date', date('d-m-Y'));
        $time             = $this->params->requests->getParam('input_time', date('h:i:s'));
        $user_id          = $this->params->requests->getParam('m765_author', @(int)$employee->IOID);
        $user_name        = $this->params->requests->getParam('m765_author_tag', $displayEmployee);

        $this->html->date            = $date;
        $this->html->time            = $time;
        $this->html->m765_author     = $user_id;
        $this->html->m765_author_tag = $user_name;
        $this->html->locations       = $this->getLocations();
        $this->html->equiptypes      = $this->getEquipTypes();
        $this->html->equipparams     = $this->getEquipParams();
        $this->html->shifts          = $this->getShifts();
        $this->html->lines           = $this->getLines();
    }

    /**
     * Hien thi nhap lieu danh sach diem do theo cay khu vuc
     *
     */
    public function showAction()
    {
        // Lay nhan vien nhap lieu mac dinh la user hien thoi
        $mEmployee        = new Qss_Model_Maintenance_Employee();
        $employee         = $mEmployee->getEmployeeByUserID($this->_user->user_id);
        $displayEmployee  = $employee?"{$employee->MaNhanVien} - {$employee->TenNhanVien}":'';

        $mForm     = new Qss_Model_Form();
        $mForm->init('M765');
        $approvers = $mForm->getApproveByStep('M765', 2, $this->_user->user_id);
        $this->html->form = $mForm;

        // Lay bo loc du lieu, thoi gian va nhan vien nhap lieu
        $date             = $this->params->requests->getParam('input_date', date('d-m-Y'));
        $time             = $this->params->requests->getParam('input_time', date('h:i:s'));
        $user_id          = $this->params->requests->getParam('m765_author', @(int)$employee->IOID);
        $user_name        = $this->params->requests->getParam('m765_author_tag', $displayEmployee);
        $location         = $this->params->requests->getParam('filter_location', 0);
        $eqType           = $this->params->requests->getParam('filter_type', 0);
        $param            = $this->params->requests->getParam('filter_param', 0);
        $equip            = $this->params->requests->getParam('filter_equip', 0);
        $line             = $this->params->requests->getParam('filter_line', 0);
        $shift            = $this->params->requests->getParam('m765_shift', 0);
        $paramType        = $this->params->requests->getParam('param_type', 0);

        // Lay danh sach diem do theo cay khu vuc
        $this->html->tree            = $this->getMonitorsByDate(
            Qss_Lib_Date::displaytomysql($date)
            , $location
            , $eqType
            , $param
            , $equip
            , $shift
            , $line
        );
        $this->html->deptid          = $this->_user->user_dept_id;
        $this->html->approvers       = $approvers;
        $this->html->date            = $date; // Thoi gian
        $this->html->time            = $time; // Thoi gian
        $this->html->m765_author     = $user_id; // ID Nguoi nhap
        $this->html->m765_author_tag = $user_name; // Nguoi nhap
        $this->html->paramType       = $paramType;
        $this->html->status          = Qss_Lib_System::getFieldRegx('ONhatTrinhThietBi', 'TinhTrang');
    }

    /**
     * Hien thi mau in nhap lieu
     */
    public function printAction()
    {
        // Hien thi du lieu tren layout in an <popup>
        $this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/print.php';

        // Lay nhan vien nhap lieu mac dinh la user hien thoi
        $mEmployee        = new Qss_Model_Maintenance_Employee();
        $employee         = $mEmployee->getEmployeeByUserID($this->_user->user_id);
        $displayEmployee  = $employee?"{$employee->MaNhanVien} - {$employee->TenNhanVien}":'';

        $mForm     = new Qss_Model_Form();
        $mForm->init('M765');
        $approvers = $mForm->getApproveByStep('M765', 2, $this->_user->user_id);
        $this->html->form = $mForm;

        // Lay bo loc du lieu, thoi gian va nhan vien nhap lieu
        $date             = $this->params->requests->getParam('input_date', date('d-m-Y'));
        $time             = $this->params->requests->getParam('input_time', date('h:i:s'));
        $user_id          = $this->params->requests->getParam('m765_author', @(int)$employee->IOID);
        $user_name        = $this->params->requests->getParam('m765_author_tag', $displayEmployee);
        $location         = $this->params->requests->getParam('filter_location', 0);
        $eqType           = $this->params->requests->getParam('filter_type', 0);
        $param            = $this->params->requests->getParam('filter_param', 0);
        $equip            = $this->params->requests->getParam('filter_equip', 0);
        $line             = $this->params->requests->getParam('filter_line', 0);
        $shift            = $this->params->requests->getParam('m765_shift', 0);
        $paramType        = $this->params->requests->getParam('param_type', 0);

        // Lay danh sach diem do theo cay khu vuc
        $this->html->tree            = $this->getMonitorsByDate(
            Qss_Lib_Date::displaytomysql($date)
            , $location
            , $eqType
            , $param
            , $equip
            , $shift
            , $line
        );
        $this->html->deptid          = $this->_user->user_dept_id;
        $this->html->approvers       = $approvers;
        $this->html->date            = $date; // Thoi gian
        $this->html->time            = $time; // Thoi gian
        $this->html->m765_author     = $user_id; // ID Nguoi nhap
        $this->html->m765_author_tag = $user_name; // Nguoi nhap
        $this->html->paramType       = $paramType;
        $this->html->status          = Qss_Lib_System::getFieldRegx('ONhatTrinhThietBi', 'TinhTrang');
    }

    /**
     * Luu chi so thiet bi vao Nhat trinh thiet bi
     */
    public function saveAction()
    {
        $mMoniter = new Qss_Model_Maintenance_Equip_Monitor();
        $params   = $this->params->requests->getParams();

        if(isset($params['refdiemdo']) && count($params['refdiemdo']))
        {
            $mMoniter->updateMonitor($params);
        }



        echo Qss_Json::encode(array('error' => 0, 'message' => '', 'redirect' => null));

        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }


    /**
     * Lay danh sach diem do theo khu vuc
     * @param $date
     * @param int $locIOID
     * @param int $eqTypeIOID
     * @param int $param
     * @param int $equipIOID
     * @return array
     */
    private function getMonitorsByDate(
        $date
        , $locIOID = 0
        , $eqTypeIOID = 0
        , $param = 0
        , $equipIOID = 0
        , $shift = 0
        , $line = 0)
    {
        $mCommon  = new Qss_Model_Extra_Extra();
        $mMonitor = new Qss_Model_Maintenance_Equip_Monitor();
        $loc      = $mCommon->getNestedSetTable( 'OKhuVuc');   // Khu vuc theo left
        $equip    = $mMonitor->getMonitorsByDate($date, $locIOID, $eqTypeIOID, $param, $equipIOID, $shift, $line);
        $retval   = array();
        $j        = 0;

        // Khoi tao mang voi khu vuc lam key
        // Muc dich de gan cac thiet bi vao mang khu vuc da sap xep theo hinh cay
        foreach($loc as $item)
        {
            $retval[$item->IOID]['Code'] = $item->MaKhuVuc;
            $retval[$item->IOID]['Name'] = $item->Ten;
            $retval[$item->IOID]['Lft']  = $item->lft;
            $retval[$item->IOID]['Rgt']  = $item->rgt;
            $retval[$item->IOID]['Level']= $item->LEVEL;
        }

        // Gan thiet bi vao trong khu vuc
        foreach($equip as $item)
        {
            // Neu chua ton tai khu vuc thi tao mot khu vuc moi <Truong hop nay kho the xay ra vi da chon khu vuc o trem>
            if(!isset($retval[$item->LOCIOID]))
            {
                $retval[$item->LOCIOID]['Code'] = $item->MaKhuVuc;
                $retval[$item->LOCIOID]['Name'] = $item->TenKhuVuc;
                $retval[$item->LOCIOID]['Lft']  = $item->lft;
                $retval[$item->LOCIOID]['Rgt']  = $item->rgt;
                $retval[$item->LOCIOID]['Level']= 1;
            }

            // Gan diem do vao cho khu vuc
            if($retval[$item->LOCIOID]['Lft'] == $item->lft && $retval[$item->LOCIOID]['Rgt'] == $item->rgt)
            {
                $retval[$item->LOCIOID]['Equip'][$j]      = $item;
                $retval[$item->LOCIOID]['Equip'][$j]->Old = $item->SoGio?$item->SoGio:0;
//                $retval[$item->LOCIOID]['Equip'][$j]['NhatTrinhIOID']    = $item->NhatTrinhIOID;
//                $retval[$item->LOCIOID]['Equip'][$j]['DiemDoIOID']       = $item->MonitorIOID;
//                $retval[$item->LOCIOID]['Equip'][$j]['DiemDo']           = $item->Ma;
//                $retval[$item->LOCIOID]['Equip'][$j]['EQIOID']           = $item->EQIOID;
//                $retval[$item->LOCIOID]['Equip'][$j]['Code']             = $item->MaThietBi;
//                $retval[$item->LOCIOID]['Equip'][$j]['Name']             = $item->TenThietBi;
//                $retval[$item->LOCIOID]['Equip'][$j]['Old']              = $item->SoGio?$item->SoGio:0;
//                $retval[$item->LOCIOID]['Equip'][$j]['ChiSo']            = $item->ChiSo;
//                $retval[$item->LOCIOID]['Equip'][$j]['Ma']               = $item->Ma;
//                $retval[$item->LOCIOID]['Equip'][$j]['BoPhan']           = $item->BoPhan;
//                $retval[$item->LOCIOID]['Equip'][$j]['RefBoPhan']        = $item->Ref_BoPhan;
//                $retval[$item->LOCIOID]['Equip'][$j]['DinhLuong']        = $item->DinhLuong;
//                $retval[$item->LOCIOID]['Equip'][$j]['DonViTinh']        = $item->DonViTinh;
//                $retval[$item->LOCIOID]['Equip'][$j]['Dat']              = $item->Dat;
//                $retval[$item->LOCIOID]['Equip'][$j]['TinhTrang']        = $item->TinhTrang;
//                $retval[$item->LOCIOID]['Equip'][$j]['GioiHanTren']      = $item->GioiHanTren;
//                $retval[$item->LOCIOID]['Equip'][$j]['GioiHanDuoi']      = $item->GioiHanDuoi;
//                $retval[$item->LOCIOID]['Equip'][$j]['ThietBiDeptID']    = $item->ThietBiDeptID;
                $j++;
            }
        }

        // Loai bo cac khu vuc khong co diem do nao
        foreach($retval as $key=>$item)
        {
            if(!isset($item['Equip']) || !count($item['Equip']))
            {
                unset($retval[$key]);
            }
        }

        return $retval;
    }

    public function equipsAction()
    {
        $params = $this->params->requests->getParams();
        $mEquip = new Qss_Model_Maintenance_Equip_List();
        $equips = $mEquip->getEquipsByCodeOrName($params['tag'], true);
        $retval = array();

        foreach ($equips as $item)
        {
            $display  = "{$item->MaThietBi} - {$item->TenThietBi}";
            $retval[] = array('id'=>$item->IOID, 'value'=>$display);
        }

        echo Qss_Json::encode($retval);
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    public function employeesAction()
    {
        $params = $this->params->requests->getParams();
        $mEquip = new Qss_Model_Maintenance_Equip_List();
        $equips = $mEquip->getEquipsByCodeOrName($params['tag']);
        $retval = array();

        foreach ($equips as $item)
        {
            $display  = "{$item->MaThietBi} - {$item->TenThietBi}";
            $retval[] = array('id'=>$item->IOID, 'value'=>$display);
        }

        echo Qss_Json::encode($retval);
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    public function getEquipTypes()
    {
        $mEquip = new Qss_Model_Maintenance_Equipment();
        $types  = $mEquip->getEquipTypes();
        $ret    = array();

        foreach($types as $item)
        {
            if($item->MaLoai)
            {
                $ret[$item->IOID] = str_repeat('&nbsp;', ($item->Level - 1)*3 )."{$item->MaLoai} - {$item->TenLoai}";
            }
            else
            {
                $ret[$item->IOID] = str_repeat('&nbsp;', ($item->Level - 1)*3 )."{$item->TenLoai}";
            }
        }

        return $ret;
    }

    public function getLocations()
    {
        $mLocation = new Qss_Model_Maintenance_Location();
        $locations = $mLocation->getLocations();
        $ret       = array();

        foreach($locations as $item)
        {
            $ret[$item->LocIOID] = str_repeat('&nbsp;', ($item->Level - 1)*3 )."{$item->LocCode} - {$item->LocName}";
        }

        return $ret;
    }

    public function getEquipParams()
    {
        $mEquip    = new Qss_Model_Maintenance_Equip_List();
        $params    = $mEquip->getEquipParams();
        // echo '<Pre>'; print_r($params); die;

        return $params;
    }

    public function getShifts()
    {
        $ret     = array();
        if(Qss_Lib_System::fieldActive('ONhatTrinhThietBi', 'Ca'))
        {
            $mCommon = new Qss_Model_Extra_Extra();
            $shifts  = $mCommon->getTable(array('*'), 'OCa', array(), array('MaCa'));

            foreach($shifts as $item)
            {
                $ret[$item->IOID] = "{$item->MaCa} - {$item->TenCa}";
            }
        }
        return $ret;
    }

    public function getLines()
    {
        $ret     = array();
        $mCommon = new Qss_Model_Extra_Extra();
        $lines   = $mCommon->getTable(array('*'), 'ODayChuyen', array(), array('MaDayChuyen'));

        foreach($lines as $item)
        {
            $ret[$item->IOID] = "{$item->MaDayChuyen} - {$item->TenDayChuyen}";
        }
        return $ret;
    }
    
	
}