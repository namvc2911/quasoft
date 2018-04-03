<?php

class Mobile_M506Controller extends Qss_Lib_Controller
{
    public function init()
    {
        parent::init();
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/mobile.php';
        $this->html->user = $this->_user;
    }

    public function indexAction()
    {

    }
    /******************************************************************************************************************/

    /**
     * A: Chức năng hiển yêu cầu duyệt vật tư
     */

    /**
     * Chức năng hiển thị yêu cầu duyệt vật tư - Danh sách duyệt vật tư
     */
    public function approvalIndexAction()
    {

    }

    public function myoutputsListAction()
    {
        $mInventory = new Qss_Model_Inventory_Inventory();
        $page       = $this->params->requests->getParam('page', 1);
        $perpage    = $this->params->requests->getParam('perpage', 10);
        $page       = $page?$page:1;
        $perpage    = $perpage?$perpage:10;

        $total      = $mInventory->countOutputsByUser($this->_user->user_id);
        $totalPage  = ceil($total/$perpage);

        $this->html->deptid  = $this->_user->user_dept_id;
        $this->html->list    = $mInventory->getOutputsByUser($this->_user->user_id, $page, $perpage);
        $this->html->total   = $totalPage;
        $this->html->perpage = $perpage;
        $this->html->page    = $page;
        $this->html->next    = (($page + 1) > $totalPage)?$totalPage:($page + 1);
        $this->html->prev    = (($page - 1) < 1)?1:($page - 1);
    }

    public function myoutputsEditAction()
    {

        $ifid       = $this->params->requests->getParam('ifid', 0);
        $mCommon    = new Qss_Model_Extra_Extra();
        $iform      = $mCommon->getTableFetchOne('qsiforms', array('IFID'=>$ifid));
        $mInventory = new Qss_Model_Inventory_Inventory();
        $output     = $mInventory->getOutputByIFID($ifid);


        $this->html->deptid        = $this->_user->user_dept_id;
        $this->html->TypeMaintain  = $mCommon->getTableFetchOne('OLoaiXuatKho', array('LOAI'=>'BAOTRI'));
        $this->html->output        = $output;
        $this->html->materials     = $mCommon->getTableFetchAll('ODanhSachXuatKho', array('IFID_M506'=>$ifid), array('*'), array('MaSP'));
        $this->html->status        = $output?$output->Status:1;
        $this->html->ifid          = $ifid;
    }

    public function myoutputsMoreAction()
    {
        $mInventory = new Qss_Model_Inventory_Inventory();
        $page       = $this->params->requests->getParam('page', 1);
        $perpage    = $this->params->requests->getParam('perpage', 10);
        $end     = $this->params->requests->getParam('end', 0); // Vi tri phan tu cuoi cung tinh tu 1

        $total      = $mInventory->countOutputsByUser($this->_user->user_id);
        $totalPage  = ceil($total/$perpage);
        $list      = $mInventory->getOutputsByUser($this->_user->user_id, $page, $perpage);

        $this->html->end     = $end;
        $this->html->list    = $list;
        $this->html->total   = $totalPage;
        $this->html->perpage = $perpage;
        $this->html->page    = $page;
        $this->html->next    = (($page + 1) > $totalPage)?$totalPage:($page + 1);
        $this->html->prev    = (($page - 1) < 1)?1:($page - 1);
    }

    public function myoutputsSaveAction()
    {
        $params = $this->params->requests->getParams();
        $params['user'] = $this->_user;
        $params['OXuatKho_NgayChuyenHang'] = $params['OXuatKho_NgayBatDau'];
        $params['OXuatKho_NgayChungTu']    = $params['OXuatKho_NgayBatDau'];

        if ($this->params->requests->isAjax())
        {
            $service = $this->services->Inventory->Output->Save($params);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }


    /**
     * Chức năng hiển thị: Công việc của tôi - Thêm vật tư
     */
    public function myoutputsAddmaterialsIndexAction()
    {
        $mInv    = new Qss_Model_Inventory_Inventory();
        $mCommon = new Qss_Model_Extra_Extra();
        $ifid    = $this->params->requests->getParam('ifid', 0);
        $ioid    = $this->params->requests->getParam('ioid', 0);
        $iform   = $mCommon->getTableFetchOne('qsiforms', array('IFID'=>$ifid));

        $this->html->deptid        = $this->_user->user_dept_id;
        $this->html->ifid          = $ifid;
        $this->html->ioid          = $ioid;
        $this->html->material      = $mInv->getOutputLineByOuputLineIOID($ioid);
        $this->html->status        = $iform?$iform->Status:0;

        // echo '<pre>'; print_r($this->html->material); die;
    }

    public function myoutputsAddmaterialsSaveAction()
    {
        $params = $this->params->requests->getParams();
        $params['user'] = $this->_user;
        $mCommon = new Qss_Model_Extra_Extra();
        $item    = $mCommon->getTableFetchOne('OSanPham', array('IFID_M113'=>$params['ODanhSachXuatKho_MaSP']));
        $params['ODanhSachXuatKho_MaSP']    = $item?$item->IOID:0;


        if ($this->params->requests->isAjax())
        {
            $service = $this->services->Inventory->Output->SaveItem($params);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    public function myoutputsAddmaterialsDeleteAction()
    {
        $ifid     = array();
        $ifid[]   = $this->params->requests->getParam('ifid', 0);
        $deptid   = array();
        $deptid[] = $this->_user->user_dept_id;

        $service = $this->services->Form->Delete($ifid, $deptid);
        echo $service->getMessage();

        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    /**
     * Chức năng hiển thị yêu cầu duyệt vật tư - Danh sách duyệt vật tư
     */
    public function approvalListAction()
    {
        $mInventory = new Qss_Model_Inventory_Inventory();
        $page       = $this->params->requests->getParam('page', 1);
        $perpage    = $this->params->requests->getParam('perpage', 10);
        $page       = $page?$page:1;
        $perpage    = $perpage?$perpage:10;

        $total      = $mInventory->countOutputsByUser($this->_user->user_id);
        $totalPage  = ceil($total/$perpage);

        $this->html->deptid  = $this->_user->user_dept_id;
        $this->html->list    = $mInventory->getOutputsByUser($this->_user->user_id, $page, $perpage, array(1,3));
        $this->html->total   = $totalPage;
        $this->html->perpage = $perpage;
        $this->html->page    = $page;
        $this->html->next    = (($page + 1) > $totalPage)?$totalPage:($page + 1);
        $this->html->prev    = (($page - 1) < 1)?1:($page - 1);
    }

    /**
     * Chức năng hiển thị yêu cầu duyệt vật tư - Duyệt dòng vật tư
     */
    public function approvalMoreAction()
    {
        $mInventory = new Qss_Model_Inventory_Inventory();
        $page       = $this->params->requests->getParam('page', 1);
        $perpage    = $this->params->requests->getParam('perpage', 10);
        $end     = $this->params->requests->getParam('end', 0); // Vi tri phan tu cuoi cung tinh tu 1

        $total      = $mInventory->countOutputsByUser($this->_user->user_id);
        $totalPage  = ceil($total/$perpage);
        $list      = $mInventory->getOutputsByUser($this->_user->user_id, $page, $perpage);



        $this->html->end     = $end;
        $this->html->list    = $list;
        $this->html->total   = $totalPage;
        $this->html->perpage = $perpage;
        $this->html->page    = $page;
        $this->html->next    = (($page + 1) > $totalPage)?$totalPage:($page + 1);
        $this->html->prev    = (($page - 1) < 1)?1:($page - 1);
    }

    /**
     * Chức năng hiển thị yêu cầu duyệt vật tư - Duyệt dòng vật tư
     */
    public function approvalApproveAction()
    {
        $ifids   = $this->params->requests->getParam('ifid', '');
        $deptids = $this->_user->user_dept_id;
        $stepno  = 5;
        $comment = '';
        $ifids   = explode(',',$ifids);
        $deptids = explode(',',$deptids);

        foreach ($ifids as $key=>$ifid)
        {
            $deptid = $deptids[$key];
            $form = new Qss_Model_Form();
            $form->initData($ifid, $deptid);
            $check = $this->checkApproveRights($form,5);
            if($check)
            {
                $service = $this->services->Form->Request($form, $stepno, $this->_user, $comment);
            }
        }

        if($check && count($ifids) > 1)
        {
            $service->setError(false);
        }

        if($check)
        {
            echo $service->getMessage();
        }

        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    public function materialsAction()
    {
        $params    = $this->params->requests->getParams();
        $mCommon   = new Qss_Model_Extra_Extra();
        $materials = $mCommon->getDataLikeString('OSanPham', array('MaSanPham', 'TenSanPham'), $params['tag'], array('MaSanPham'));
        $retval    = array();

        foreach ($materials as $item)
        {
            if($item->MaSanPham)
            {
                $display  = "{$item->MaSanPham} - {$item->TenSanPham}";
                $retval[] = array('id'=>$item->IFID_M113, 'value'=>$display);
            }

        }

        echo Qss_Json::encode($retval);
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
}
