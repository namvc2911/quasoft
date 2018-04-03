<?php
class Button_M182Controller extends Qss_Lib_Controller
{

    public function init()
    {
        $this->i_SecurityLevel = 15;
        parent::init();
        $this->headScript($this->params->requests->getBasePath() . '/js/common.js');
        $this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/popup.php';
    }

    /**
     * Button: Nút tạo bàn giao
     */
    public function handoverIndexAction()
    {
        $ifid    = $this->params->requests->getParam('ifid', 0);
        $mCommon = new Qss_Model_Extra_Extra();

        $this->html->ifid      = $ifid;
        $this->html->handover  = $mCommon->getTableFetchOne('OPhieuBanGiaoTaiSan', array('IFID_M182'=>$ifid));
    }

    public function handoverBypersonIndexAction()
    {
        $mCommon  = new Qss_Model_Extra_Extra();
        $ifid     = $this->params->requests->getParam('ifid', 0);
        $clawback = $this->params->requests->getParam('clawback', 0);

        $this->html->handover  = $mCommon->getTableFetchOne('OPhieuBanGiaoTaiSan', array('IFID_M182'=>$ifid));
        $this->html->olds      = $mCommon->getTable(array('*'), 'OChiTietBanGiaoTaiSan', array('IFID_M182'=>$ifid), array('MaTaiSan'), 'NO_LIMIT');
        $this->html->clawback  = $clawback;
    }

    public function handoverBypersonAssetsAction()
    {
        $mCommon  = new Qss_Model_Extra_Extra();
        $mAsset   = new Qss_Model_Maintenance_Asset();
        $ifid     = $this->params->requests->getParam('ifid', 0);
        $clawback = $this->params->requests->getParam('clawback', 0);
        $employee = $this->params->requests->getParam('employee', 0);
        $qty      = $this->params->requests->getParam('qty', 0);

        $this->html->assets   = $mAsset->getHandoverAssetsBaseOnClawBack($clawback, $employee);
        $this->html->employee = $mCommon->getTableFetchOne('ODanhSachNhanVien', array('IOID'=>$employee));
        $this->html->clawback = $clawback;
        $this->html->qty      = $qty;
    }

    public function handoverByassetIndexAction()
    {
        $mCommon  = new Qss_Model_Extra_Extra();
        $ifid     = $this->params->requests->getParam('ifid', 0);
        $clawback = $this->params->requests->getParam('clawback', 0);

        // echo $clawback; die;

        $mAsset   = new Qss_Model_Maintenance_Asset();

        $this->html->olds      = $mCommon->getTable(array('*'), 'OChiTietBanGiaoTaiSan', array('IFID_M182'=>$ifid), array('MaNhanVien'), 'NO_LIMIT');
        $this->html->clawback  = $clawback;
    }

    public function handoverByassetEmployeesAction()
    {
        $mAsset   = new Qss_Model_Maintenance_Asset();
        $mCommon  = new Qss_Model_Extra_Extra();
        $ifid     = $this->params->requests->getParam('ifid', 0);
        $clawback = $this->params->requests->getParam('clawback', 0);
        $asset    = $this->params->requests->getParam('asset', 0);
        $qty      = $this->params->requests->getParam('qty', 0);
        $handover = $mCommon->getTableFetchOne('OPhieuBanGiaoTaiSan', array('IFID_M182'=>$ifid));
        $mEmp     = new Qss_Model_Maintenance_Employee();

        $this->html->employees = $clawback?$mAsset->getHandoverEmployeesBaseOnClawBack($clawback, $asset):$mCommon->getTable(array('*'), 'ODanhSachNhanVien', array(), array('MaNhanVien'), 'NO_LIMIT');
        $this->html->asset     = $mCommon->getTableFetchOne('ODanhMucCongCuDungCu', array('IOID'=>$asset));
        $this->html->clawback  = $clawback;
        $this->html->qty       = $qty;
    }

    /**
     * Button: Nút tạo bàn giao
     */
    public function handoverSaveAction()
    {
        $params = $this->params->requests->getParams();

        $params['deptid'] = $this->_user->user_dept_id;

        if ($this->params->requests->isAjax())
        {
            $service = $this->services->Maintenance->Asset->Handover->Save($params);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    public function employeesAction()
    {
        $mAsset   = new Qss_Model_Maintenance_Asset();
        $tag      = $this->params->requests->getParam('tag', '');
        $clawback = $this->params->requests->getParam('clawback', 0);
        $factory  = $this->params->requests->getParam('factory', 0);
        $mEmp     = new Qss_Model_Maintenance_Employee();
        $mCommon  = new Qss_Model_Extra_Extra();

        $request  = $clawback?$mAsset->getHandoverEmployeesBaseOnClawBackLikeString($tag, $clawback):$mCommon->getDataLikeString('ODanhSachNhanVien', array('MaNhanVien', 'TenNhanVien'), $tag);
        $retval   = array();

        foreach($request as $item)
        {
            $titleBoPhan  = $item->TenBoPhan;
            $titleBoPhan .= $item->TenBoPhan && $item->TenPhongBan?' - ':'';
            $titleBoPhan .= $item->TenPhongBan;

            $display  = "{$item->MaNhanVien} - {$item->TenNhanVien} ({$titleBoPhan})";
            $retval[] = array('id'=>($clawback?$item->Ref_NhanVien:$item->IOID), 'value'=>$display);
        }

        echo Qss_Json::encode($retval);
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    public function itemsAction()
    {
        $mAsset   = new Qss_Model_Maintenance_Asset();
        $tag      = $this->params->requests->getParam('tag', '');
        $clawback = $this->params->requests->getParam('clawback', 0);
        $request  = $mAsset->getHandoverAssetsBaseOnClawBackLikeString($tag, $clawback);
        $retval   = array();

        foreach($request as $item)
        {
            if($clawback != 0)
            {
                $item->SoLuongConLai = Qss_Lib_Util::formatNumber($item->SoLuongConLai);

                $display  = "{$item->Ma} - {$item->Ten}: {$item->SoLuongConLai} {$item->DonViTinh} {$item->PhieuBanGiao}" ;
                $retval[] = array('id'=>$item->IOID, 'value'=>$display, 'class'=>12);
            }
            else
            {
                $display  = "{$item->Ma} - {$item->Ten} ({$item->DonViTinh})";
                $retval[] = array('id'=>$item->IOID, 'value'=>$display);
            }

        }

        echo Qss_Json::encode($retval);
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
}