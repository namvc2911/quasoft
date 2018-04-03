<?php
class Button_M506Controller extends Qss_Lib_Controller
{
    public function init()
    {

        $this->i_SecurityLevel = 15;
        parent::init();
        $this->headScript($this->params->requests->getBasePath() . '/js/common.js');
    }

    public function materialIndexAction()
    {
        $ifid   = $this->params->requests->getParam('ifid', 0);
        $this->html->ifid = $ifid;
    }

    /**
     * Hien thi dialbox chon mat hang
     */
    public function materialSelectAction()
    {
        $mWorkorder  = new Qss_Model_Maintenance_Workorder();
        $mInventory  = new Qss_Model_Inventory_Inventory();
        $mOrder      = new Qss_Model_Purchase_Order();
        $mItem       = new Qss_Model_Master_Item();
        $filter      = $this->params->requests->getParam('m506_material_filter', 0);
        $ifid        = $this->params->requests->getParam('ifid', 0);

        $this->html->filter = $filter;

        if($filter == 1)
        {
            // Lay dong phieu xuat kho de lay gia tri
            $output                = $mInventory->getOutputByIFID($ifid);
            // Lay mat hang tu phieu xuat kho
            $this->html->workorder = ($output && $output->Ref_PhieuBaoTri)
                ?$mWorkorder->getMaterials(false, $output->Ref_PhieuBaoTri):array();
            // Lay mat hang tu don hang
            $this->html->order     = (Qss_Lib_System::fieldActive('OXuatKho', 'SoDonHang') && $output && $output->Ref_SoDonHang)
                ?$mOrder->getOrderLineByIOID($output->Ref_SoDonHang):array();
        }
        else
        {
            $this->html->items = $mItem->getItems();
        }
    }

    /**
     * Hien thi danh sach mat hang da chon de tao danh sach nhap kho
     */
    public function materialShowAction()
    {
        $mInventory         = new Qss_Model_Inventory_Inventory();
        $ifid               = $this->params->requests->getParam('ifid', 0);
        $this->html->output = $mInventory->getOutputLineByOuputIFID($ifid);
    }

    // Lưu lại mặt hàng đã chọn
    public function materialSaveAction()
    {
        $params = $this->params->requests->getParams();

        $params['deptid'] = $this->_user->user_dept_id;

        if ($this->params->requests->isAjax())
        {
            $service = $this->services->Inventory->Output->SaveLines($params);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    //==================================================================================================================

    public function positionIndexAction()
    {
        $user               = Qss_Register::get('userinfo');
        $mInv = new Qss_Model_Inventory_Inventory();
        $ifid = $this->params->requests->getParam('ifid', 0);
        $ioid = $this->params->requests->getParam('ioid', 0);
        $item       =  $this->params->requests->getParam('item', 0);
        $uom        =  $this->params->requests->getParam('uom', 0);
        $qty        =  $this->params->requests->getParam('qty', 0);

        $in   = $mInv->getOutputByIFID($ifid);
        $rKho = $in?$in->Ref_Kho:0;
        $ss   = $ioid?$mInv->getBinConfigOfOutputLine(@(int)$in->IFID_M506, $ioid)
            :$mInv->getStockStatusByItem($rKho, $item, $uom);

        $mForm              = new Qss_Model_Form();
        $form               = $mForm->initData($ifid, $user->user_dept_id);
        $outObject          = $mForm->o_fGetObjectByCode('ODanhSachXuatKho');


        $form_rights        = $mForm->i_fGetRights($user->user_group_list);
        if(!$outObject->bInsert)
        {
            $form_rights = $form_rights & ~Qss_Lib_Const::FORM_RIGHTS_CREATE;
        }

        if(!$outObject->bEditing)
        {
            $form_rights = $form_rights & ~Qss_Lib_Const::FORM_RIGHTS_UPDATE;
        }

        if(!$outObject->bDeleting)
        {
            $form_rights = $form_rights & ~Qss_Lib_Const::FORM_RIGHTS_DELETE;
        }

        $rights = $form_rights;
        $rights = $rights & $outObject->intRights;


        //echo '<pre>'; print_r($ioid); die;
        $this->html->stockstatus    = $ss;
        $this->html->outputLineIOID = $ioid;
        $this->html->rights         = $rights;
        $this->html->qty            = ($qty != 0)?$qty:0;
        $this->html->ifid           = $ifid;
        $this->html->deptid         = $this->_user->user_dept_id;
    }

    public function positionSaveAction()
    {
        $params = $this->params->requests->getParams();

        if ($this->params->requests->isAjax())
        {
            $service = $this->services->Inventory->Output->SavePositions($params);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    public function getbyrequestIndexAction() {
        $ifid = $this->params->requests->getParam('ifid', 0);
        $this->html->ifid   = $ifid;
        $this->html->deptid = $this->_user->user_dept_id;
    }

    public function getbyrequestShowAction() {
        $common = new Qss_Model_Extra_Extra();
        $mM751  = new Qss_Model_M751_Request();
        $mM506  = new Qss_Model_M506_Output();
        $tool      = $this->params->requests->getParam('M506_Get_By_Request_Tool', 0);
        $material  = $this->params->requests->getParam('M506_Get_By_Request_Material', 0);
        $ifid      = $this->params->requests->getParam('M506_Get_By_Request_IFID', 0);
        $strItem   = $this->params->requests->getParam('M506_Get_By_Request_Item', '');
        $page      = $this->params->requests->getParam('M506_Get_By_Request_PageNo', 1);
        $perpage   = $this->params->requests->getParam('M506_Get_By_Request_Display', 20);
        $page      = $page?$page:1;
        $perpage   = $perpage?$perpage:20;
        $OYeuCauVatTu = array();
        $total     = 0;
        $totalPage = 0;

        // Lấy thông tin bản ghi xuất kho
        $OXuatKho = $common->getTableFetchOne('OXuatKho', array('IFID_M506'=>$ifid));

        // Lấy thông tin phiếu yêu cầu (Chỉ lấy khi có yêu cầu điều động và chỉ lấy theo 1 yêu cầu điều động)
        if($OXuatKho && $OXuatKho->SoYeuCau) {
            $OYeuCauDieuDong = $common->getTableFetchOne('OYeuCauTrangThietBiVatTu', array('IOID'=>(int)$OXuatKho->Ref_SoYeuCau));

            if($OYeuCauDieuDong) {
                $total      = $mM751->countItemsByRequest($OXuatKho->Ref_SoYeuCau, 0 , $strItem, $tool, $material);
                $totalPage  = ceil($total/$perpage);
                $page       = $page < $totalPage?$page:$totalPage;

                $OYeuCauVatTu = $mM506->getItemsByRequest($OYeuCauDieuDong->IOID, $OXuatKho->IFID_M506, $OXuatKho->Ref_Kho
                    , $page, $perpage, 0, $strItem, $tool, $material);
            }
        }

        $this->html->OXuatKho     = $OXuatKho;
        $this->html->OYeuCauVatTu = $OYeuCauVatTu;
        $this->html->page         = $page;
        $this->html->display      = $perpage;
        $this->html->totalPage    = $totalPage;
        $this->html->total        = $total;
        $this->html->next         = ($page < $totalPage)?($page + 1):$totalPage;
        $this->html->prev         = ($page > 1)?($page - 1):1;
    }

    public function getbyrequestSaveAction() {
        $params = $this->params->requests->getParams();

        if ($this->params->requests->isAjax())
        {
            $service = $this->services->Button->M506->Getbyrequest->Save($params);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    public function getbystockIndexAction() {
        $ifid = $this->params->requests->getParam('ifid', 0);
        $this->html->ifid   = $ifid;
        $this->html->deptid = $this->_user->user_dept_id;
    }

    public function getbystockShowAction() {
        $common    = new Qss_Model_Extra_Extra();
        $mMaterial = new Qss_Model_M602_Inventory();
        $mOutput   = new Qss_Model_M506_Output();
        $mInv      = new Qss_Model_M602_Inventory();
        $tool      = $this->params->requests->getParam('M506_Get_By_Stock_Tool', 0);
        $material  = $this->params->requests->getParam('M506_Get_By_Stock_Material', 0);
        $ifid      = $this->params->requests->getParam('M506_Get_By_Stock_IFID', 0);
        $strItem   = $this->params->requests->getParam('M506_Get_By_Stock_Item', '');
        $page      = $this->params->requests->getParam('M506_Get_By_Stock_PageNo', 1);
        $perpage   = $this->params->requests->getParam('M506_Get_By_Stock_Display', 20);
        $page      = $page?$page:1;
        $perpage   = $perpage?$perpage:20;
        $OKho      = array();
        $total     = 0;
        $totalPage = 0;

        // Lấy thông tin bản ghi xuất kho
        $OXuatKho = $common->getTableFetchOne('OXuatKho', array('IFID_M506'=>$ifid));

        // Lấy thông tin phiếu yêu cầu (Chỉ lấy khi có yêu cầu điều động và chỉ lấy theo 1 yêu cầu điều động)
        if($OXuatKho && $OXuatKho->Kho) {
            $total      = $mInv->countLineByStock($OXuatKho->Ref_Kho, 0 , $strItem, $tool, $material);
            $totalPage  = ceil($total/$perpage);
            $page       = $page < $totalPage?$page:$totalPage;
            $OKho       = $mOutput->getItemsByStock(
                $OXuatKho->IFID_M506, $OXuatKho->Ref_SoYeuCau, $OXuatKho->Ref_Kho
                , $page, $perpage,0, $strItem, $tool, $material
            );
        }

        // echo '<pre>'; print_r($OKho); die;

        $this->html->OXuatKho = $OXuatKho;
        $this->html->OKho      = $OKho;
        $this->html->page      = $page;
        $this->html->display   = $perpage;
        $this->html->totalPage = $totalPage;
        $this->html->total     = $total;
        $this->html->next      = ($page < $totalPage)?($page + 1):$totalPage;
        $this->html->prev      = ($page > 1)?($page - 1):1;
    }

    public function getbystockSaveAction() {
        $params = $this->params->requests->getParams();

        if ($this->params->requests->isAjax())
        {
            $service = $this->services->Button->M506->Getbystock->Save($params);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
}