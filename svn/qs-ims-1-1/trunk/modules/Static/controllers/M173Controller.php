<?php
class Static_M173Controller extends Qss_Lib_Controller
{
    const TAB_LOCATION   = 'LOCATION';
    const TAB_LINE       = 'LINE';
    const TAB_COSTCENTER = 'COSTCENTER';
    const TAB_MANAGER    = 'MANAGER';

    public function init()
    {
        parent::init();
    	if($this->_mobile)
		{
			$this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/mwide.php';
		}
    }

    public function indexAction()
    {

    }

    /**
     * Hien thi du lieu theo tab chon
     */
    public function filterAction()
    {
        $tab = $this->params->requests->getParam('tab', 'LOCATION'); // Lay tab da chon
        $dat = new stdClass();

        $mLoc  = new Qss_Model_Maintenance_Location();
        $mLine = new Qss_Model_Maintenance_Line();
        $mCC   = new Qss_Model_Master_Costcenter();
        $mEmp  = new Qss_Model_Maintenance_Employee();
        $i     = 0;

        $dat->{$i}          = new stdClass();
        $dat->{$i}->id      = 0;
        $dat->{$i}->class   = '';
        $dat->{$i}->display = "Tất cả";
        $i++;

        switch($tab)
        {
            case 'LOCATION': // Lay theo khu vuc
                $locs = $mLoc->getLocations();


                foreach($locs as $item)
                {
                    $dat->{$i}          = new stdClass();
                    $dat->{$i}->id      = $item->LocIOID;
                    $dat->{$i}->class   = ($item->Level>1)?'tree_level_'.($item->Level-1).' ':'';
                    $dat->{$i}->display = "{$item->LocCode} - {$item->LocName}";
                    $i++;
                }
            break;

            case 'LINE': // Lay theo day chuyen
                $lines = $mLine->getLines();

                foreach($lines as $item)
                {
                    $dat->{$i}          = new stdClass();
                    $dat->{$i}->id      = $item->IOID;
                    $dat->{$i}->class   = '';
                    $dat->{$i}->display = "{$item->MaDayChuyen} - {$item->TenDayChuyen}";
                    $i++;
                }
            break;

            case 'COSTCENTER': // Lay theo trung tam chi phi
                $costcenters = $mCC->getCostCenter();

                foreach($costcenters as $item)
                {
                    $dat->{$i}          = new stdClass();
                    $dat->{$i}->id      = $item->IOID;
                    $dat->{$i}->class   = '';
                    $dat->{$i}->display = "{$item->Ma} - {$item->Ten}";
                    $i++;
                }
            break;

            case 'MANAGER': // Lay theo nguoi quan ly
                $employees = $mEmp->getAllEmployee();

                foreach($employees as $item)
                {
                    $dat->{$i}          = new stdClass();
                    $dat->{$i}->id      = $item->IOID;
                    $dat->{$i}->class   = '';
                    $dat->{$i}->display = "{$item->MaNhanVien} - {$item->TenNhanVien}";
                    $i++;
                }
            break;
        }

        echo Qss_Json::encode(array('error' => 0, 'data' => $dat, 'message' => '', 'filter' => $tab));

        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    /**
     * Hien thi thiet bi de chon
     */
    public function getAction()
    {
        $tab     = $this->params->requests->getParam('tab', 'LOCATION'); // Lay tab da chon
        $filter  = $this->params->requests->getParam('filter', 0); // Lay tab da chon
        $parentEquip  = $this->params->requests->getParam('parent_equip', 0); // Lay tab da chon
        $dat     = new stdClass();
        $mEquip  = new Qss_Model_Maintenance_Equip_List();

        $equips  = array();


        switch($tab)
        {
            case 'LOCATION': // Lay theo khu vuc
                $equips = $mEquip->getEquipsTreeByLocation($filter, $parentEquip);
                $i      = 0;

                foreach($equips as $item)
                {
                    $dat->{$i}               = new stdClass();
                    $dat->{$i}->id           = $item->IOID;
                    $dat->{$i}->display      = "{$item->MaThietBi} - {$item->TenThietBi} ({$item->MaKhuVuc})";
                    $dat->{$i}->parent       = (int)$item->Ref_MaKhuVuc;
                    $dat->{$i}->ifid         = (int)$item->IFID_M705;
                    $dat->{$i}->class        = (@$item->Level>1)?'tree_level_'.(@$item->Level-1).' ':'';
                    $dat->{$i}->space        = @$item->Level?str_repeat('&nbsp;&nbsp;&nbsp;', (@$item->Level-1)):'';
                    $dat->{$i}->parent_equip = @(int)$item->Ref_TrucThuoc;
                    $dat->{$i}->lft          = @(int)$item->lft;
                    $dat->{$i}->rgt          = @(int)$item->rgt;
                    $dat->{$i}->level        = @(int)$item->Level;
                    $i++;
                }
            break;

            case 'LINE': // Lay theo day chuyen
                $equips = $mEquip->getEquipByLine($filter);
                $i      = 0;

                foreach($equips as $item)
                {
                    $dat->{$i}          = new stdClass();
                    $dat->{$i}->id      = $item->IOID;
                    $dat->{$i}->display = "{$item->MaThietBi} - {$item->TenThietBi} ({$item->DayChuyen})";
                    $dat->{$i}->parent  = (int)$item->Ref_DayChuyen;
                    $dat->{$i}->ifid    = (int)$item->IFID_M705;
                    $dat->{$i}->class   = '';
                    $dat->{$i}->space   = '';
                    $dat->{$i}->lft     = '';
                    $dat->{$i}->rgt     = '';
                    $dat->{$i}->level   = '';
                    $i++;
                }
            break;

            case 'COSTCENTER': // Lay theo trung tam chi phi
                $equips = $mEquip->getEquipByCostCenter($filter);
                $i      = 0;

                foreach($equips as $item)
                {
                    $dat->{$i}          = new stdClass();
                    $dat->{$i}->id      = $item->IOID;
                    $dat->{$i}->display = "{$item->MaThietBi} - {$item->TenThietBi} ({$item->TrungTamChiPhi})";
                    $dat->{$i}->parent  = (int)$item->Ref_TrungTamChiPhi;
                    $dat->{$i}->ifid    = (int)$item->IFID_M705;
                    $dat->{$i}->class   = '';
                    $dat->{$i}->space   = '';
                    $dat->{$i}->lft     = '';
                    $dat->{$i}->rgt     = '';
                    $dat->{$i}->level   = '';
                    $i++;
                }
            break;

            case 'MANAGER': // Lay theo nguoi quan ly
                $equips = $mEquip->getEquipByManager($filter);
                $i      = 0;

                foreach($equips as $item)
                {
                    $dat->{$i}          = new stdClass();
                    $dat->{$i}->id      = $item->IOID;
                    $dat->{$i}->display = "{$item->MaThietBi} - {$item->TenThietBi} ({$item->QuanLy})";
                    $dat->{$i}->parent  = (int)$item->Ref_QuanLy;
                    $dat->{$i}->ifid    = (int)$item->IFID_M705;
                    $dat->{$i}->class   = '';
                    $dat->{$i}->space   = '';
                    $dat->{$i}->lft     = '';
                    $dat->{$i}->rgt     = '';
                    $dat->{$i}->level   = '';
                    $i++;
                }
            break;
        }

        //echo '<pre>'; print_r($dat); die;
        echo Qss_Json::encode(array('error' => 0, 'data' => $dat, 'message' => '', 'filter' => $tab));

        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }


    /**
     * Luu lai di chuyen thiet bi
     */
    public function saveAction()
    {
        $params = $this->params->requests->getParams();

        if ($this->params->requests->isAjax())
        {
            $service = $this->services->Maintenance->Equip->Install($params);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
}