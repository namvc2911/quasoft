<?php
class Button_M183Controller extends Qss_Lib_Controller
{
    public function init()
    {

        $this->i_SecurityLevel = 15;
        parent::init();
        $this->headScript($this->params->requests->getBasePath() . '/js/common.js');
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/popup.php';

    }

    /**
     * Button: Nút tạo thu hoi
     */
    public function clawbackIndexAction()
    {
        $ifid     = $this->params->requests->getParam('ifid', 0);
        $mCommon  = new Qss_Model_Extra_Extra();
        $clawback = $mCommon->getTableFetchOne('OPhieuThuHoiTaiSan', array('IFID_M183'=>$ifid));

        $this->html->ifid      = $ifid;
        $this->html->employees = $this->getEmployees();
        $this->html->deptid    = $this->_user->user_dept_id;
        $this->html->clawback  = $clawback;
        $this->html->types     = Qss_Lib_System::getFieldRegx('OChiTietThuHoiTaiSan', 'Loai');
    }

    /**
     * Button: Nút tạo thu hoi
     */
    public function clawbackShowAction()
    {
        $ifid           = $this->params->requests->getParam('ifid', 0);
        $employees      = $this->params->requests->getParam('employees', array());
        $type           = $this->params->requests->getParam('genType', 0);
        $newEmployee    = $this->params->requests->getParam('newEmployee', 0);
        $newEmployeeStr = $this->params->requests->getParam('newEmployee_tag', '');

        $mAsset    = new Qss_Model_Maintenance_Asset();
        $mCommon  = new Qss_Model_Extra_Extra();
        $clawback = $mCommon->getTableFetchOne('OPhieuThuHoiTaiSan', array('IFID_M183'=>$ifid));

        $this->html->ifid      = $ifid;
        $this->html->deptid    = $this->_user->user_dept_id;
        $this->html->assets    = $mAsset->getClawBackAssetsByEmployees($employees, $ifid);
        $this->html->employees = $this->getEmployeesCombobox();
        $this->html->types     = Qss_Lib_System::getFieldRegx('OChiTietThuHoiTaiSan', 'Loai');
        $this->html->type      = $type;
        $this->html->new       = $newEmployee;
        $this->html->newStr    = $newEmployeeStr;
    }

    public function clawbackSaveAction()
    {
        $params = $this->params->requests->getParams();

        $params['deptid'] = $this->_user->user_dept_id;

        if ($this->params->requests->isAjax())
        {
            $service = $this->services->Maintenance->Asset->Clawback->Save($params);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    /**
     * Button: Nút tạo ban giao
     */
    public function handoverIndexAction()
    {
        $ifid = $this->params->requests->getParam('ifid', 0);
        $mCommon = new Qss_Model_Extra_Extra();

        $this->html->ifid = $ifid;
    }

    public function employeesAction()
    {
        $tag      = $this->params->requests->getParam('tag', '');
        $mCommon  = new Qss_Model_Extra_Extra();
        $request  = $mCommon->getDataLikeString('ODanhSachNhanVien', array('MaNhanVien', 'TenNhanVien'), $tag, array('MaNhanVien'));

        $retval   = array();

        $retval[] = array('id'=>''
        ,'value'=>'Tạo mới'
        ,'extra'=>sprintf('onclick = "popupFormInsert(\'M316\',{})" class="italic green"'));

        foreach($request as $item)
        {
            $titleBoPhan  = $item->TenBoPhan;
            $titleBoPhan .= $item->TenBoPhan && $item->TenPhongBan?' - ':'';
            $titleBoPhan .= $item->TenPhongBan;

            $display  = "{$item->MaNhanVien} - {$item->TenNhanVien} ({$titleBoPhan})";
            $retval[] = array('id'=>$item->IOID, 'value'=>$display);
        }

        echo Qss_Json::encode($retval);
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    public function getEmployeesCombobox()
    {
        $common  = new Qss_Model_Extra_Extra();
        $emps    = $common->getTable(array('*'), 'ODanhSachNhanVien', array(), array('TenNhanVien'));
        $ret     = array();

        foreach($emps as $item)
        {
            $titleBoPhan  = $item->TenBoPhan;
            $titleBoPhan .= $item->TenBoPhan && $item->TenPhongBan?' - ':'';
            $titleBoPhan .= $item->TenPhongBan;

            $ret[$item->IOID] = "{$item->MaNhanVien} - {$item->TenNhanVien} ({$titleBoPhan})";
        }

        return $ret;
    }

    public function getEmployees()
    {
        $common     = new Qss_Model_Extra_Extra();
        $retval     = array();
        $employees  = $common->getTable(array('*'), 'ODanhSachNhanVien', array(), array('MaNhanVien'), 'NO_LIMIT');
        $i          = 0;

        foreach($employees as $dat)
        {
            $titleBoPhan  = $dat->TenBoPhan;
            $titleBoPhan .= $dat->TenBoPhan && $dat->TenPhongBan?' - ':'';
            $titleBoPhan .= $dat->TenPhongBan;

            $retval[0]['Dat'][$i]['ID']      = $dat->IOID;
            $retval[0]['Dat'][$i]['Display'] = "{$dat->MaNhanVien} - {$dat->TenNhanVien} ({$titleBoPhan})";
            $i++;
        }

        return $retval;
    }
}