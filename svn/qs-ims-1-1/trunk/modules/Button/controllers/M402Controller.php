<?php
class Button_M402Controller extends Qss_Lib_Controller
{
    public function init()
    {

        $this->i_SecurityLevel = 15;
        parent::init();
        $this->headScript($this->params->requests->getBasePath() . '/js/common.js');
    }

    //==================================================================================================================

    /**
     * Button: Nút lấy phụ tùng vật tư
     */
    public function materialIndexAction()
    {
        // Lay ifid de truyen sang cac action khac
        $ifid = $this->params->requests->getParam('ifid', 0);
        $this->html->ifid = $ifid;
    }

    /**
     * Hien thi dialbox chon mat hang
     */
    public function materialSelectAction()
    {
        $mInventory  = new Qss_Model_Inventory_Inventory();
        $mOrder      = new Qss_Model_Purchase_Order();
        $mItem       = new Qss_Model_Master_Item();
        $filter      = $this->params->requests->getParam('m402_material_filter', 0);
        $ifid        = $this->params->requests->getParam('ifid', 0);

        $this->html->filter = $filter;

        if($filter == 1)
        {
            // Lay dong phieu nhap kho de lay gia tri
            $input              = $mInventory->getInputByIFID($ifid);
            // Lay mat hang tu phieu xuat kho
            $this->html->output = ($input && $input->Ref_PhieuXuatKho)
                ?$mInventory->getOutputLineByOuputIOID($input->Ref_PhieuXuatKho):array();
            // Lay mat hang tu don hang
            $this->html->order  = (Qss_Lib_System::fieldActive('ONhapKho', 'SoDonHang') && $input && $input->Ref_SoDonHang)
                ?$mOrder->getOrderLineByIOID($input->Ref_SoDonHang):array();
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
        $this->html->input  = $mInventory->getInputLineByInputIFID($ifid);
    }


    /**
     * Luu lai mat hang da chon vao phieu nhap sau khi xoa danh sach da co
     */
    public function materialSaveAction()
    {
        $params = $this->params->requests->getParams();

        $params['deptid'] = $this->_user->user_dept_id;

        if ($this->params->requests->isAjax())
        {
            $service = $this->services->Inventory->Input->SaveLines($params);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    //==================================================================================================================

    public function positionIndexAction()
    {
        $user       = Qss_Register::get('userinfo');

        // -------------------------------------
        $mProduct   = new Qss_Model_Extra_Products();
        $mInv       = new Qss_Model_Inventory_Inventory();
        $mItem      = new Qss_Model_Master_Item();
        $ifid       = $this->params->requests->getParam('ifid', 0);
        $ioid       = $this->params->requests->getParam('ioid', 0);
        $item       =  $this->params->requests->getParam('item', 0);
        $uom        =  $this->params->requests->getParam('uom', 0);
        $qty        =  $this->params->requests->getParam('qty', 0);

        $in         = $mInv->getInputByIFID($ifid);
        $bin        = ($item && $uom && $qty != 0)?$mInv->getBinListByWarehouse(@(int)$in->Ref_Kho):array();
        $ss         = $ioid?$mInv->getBinConfigOfInputLine(@(int)$in->IFID_M402, $ioid):array();
        $itemConfig = ($item && $uom && $qty != 0)?$mItem->getItemByIOID($item):'';
        // -------------------------------------


        $mForm              = new Qss_Model_Form();
        $form               = $mForm->initData($ifid, $user->user_dept_id);
        $outObject          = $mForm->o_fGetObjectByCode('ODanhSachNhapKho');


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
        $this->html->stockstatus   = $ss;
        $this->html->positions     = $bin;
        $this->html->inputLineIOID = $ioid;
        $this->html->rights        = $rights;
        $this->html->itemConfig    = $itemConfig;
        // -------------------------------------
        $this->html->qty           = ($qty != 0)?$qty:0;
        // -------------------------------------

        $this->html->ifid          = $ifid;
        $this->html->deptid        = $this->_user->user_dept_id;
        $this->html->auto          = $itemConfig->DanhMaTuDong?$itemConfig->DanhMaTuDong:0;
        $this->html->length        = $itemConfig->DoDaiMa?$itemConfig->DoDaiMa:1;
        $this->html->prefix        = $itemConfig->KyTuDauMa?$itemConfig->KyTuDauMa:'';
        $this->html->last          = $mProduct->getLastSerial($itemConfig->KyTuDauMa);
    }

    public function positionSaveAction()
    {
        $params = $this->params->requests->getParams();

        if ($this->params->requests->isAjax())
        {
            $service = $this->services->Inventory->Input->SavePositions($params);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }


    public function getbypoIndexAction()
    {
        $mReceive = new Qss_Model_Inventory_Inventory();
        $ifid     = $this->params->requests->getParam('ifid', 0);
        $receive  = $mReceive->getInputByPO($ifid);

        $this->html->receive = $receive;
        $this->html->ifid    = $ifid;
        $this->html->deptid  = $this->_user->user_dept_id;
    }

    public function getbypoSaveAction()
    {
        $params = $this->params->requests->getParams();

        if ($this->params->requests->isAjax())
        {
            $service = $this->services->Inventory->Input->InsertDetail($params);
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
        $ifid   = $this->params->requests->getParam('ifid', 0);
        $OYeuCauVatTu = array();

        // Lấy thông tin bản ghi xuất kho
        $ONhapKho = $common->getTableFetchOne('ONhapKho', array('IFID_M402'=>$ifid));

        // Lấy thông tin phiếu yêu cầu (Chỉ lấy khi có yêu cầu điều động và chỉ lấy theo 1 yêu cầu điều động)
        if($ONhapKho && $ONhapKho->SoYeuCau) {
            $OYeuCauDieuDong = $common->getTableFetchOne('OYeuCauTrangThietBiVatTu', array('IOID'=>(int)$ONhapKho->Ref_SoYeuCau));

            if($OYeuCauDieuDong) {
                $OYeuCauVatTu = $mM751->getRequestItemsFromInput($OYeuCauDieuDong->IFID_M751, $ONhapKho->IFID_M402);
            }
        }

        $this->html->OXuatKho     = $ONhapKho;
        $this->html->OYeuCauVatTu = $OYeuCauVatTu;
    }

    public function getbyrequestSaveAction() {
        $params = $this->params->requests->getParams();

        if ($this->params->requests->isAjax())
        {
            $service = $this->services->Button->M402->Getbyrequest->Save($params);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
}