<?php
class Button_M751Controller extends Qss_Lib_Controller
{
    /**
     * Hien thi chon nhieu thiet bi trong tab thiet bi - yeu cau m751
     */
    public function getequipsIndexAction() {
        // Lay ifid de truyen sang cac action khac
        // $mStock  = new Qss_Model_Warehouse_Main();
        $ifid    = $this->params->requests->getParam('ifid', 0);
        $filter  = $this->params->requests->getParam('m412_material_filter', 0);

        // $this->html->stocks = ($filter != 1)?$mStock->getAll($this->_user):array();
        $this->html->ifid = $ifid;
    }

    /**
     * Hien thi noi dung thiet bi theo tim kiem
     */
    public function getequipsSelectAction() {
        $mCommon = new Qss_Model_Extra_Extra();
        $this->html->data = $mCommon->getTableFetchAll('OLoaiThietBi', array(), array('*'), array('lft'));
    }

    /**
     * Cac thiet bi da su
     */
    public function getequipsShowAction() {
        $ifid   = $this->params->requests->getParam('ifid', 0);
        $mTable = Qss_Model_Db::Table('OYeuCauTrangThietBi');
        $mTable->where(sprintf(' IFID_M751 = %1$d ', $ifid));

        $this->html->data = $mTable->fetchAll();
    }

    /**
     * Luu lai cac thiet bi da chon vao thiet bi M751
     */
    public function getequipsSaveAction() {
        $params = $this->params->requests->getParams();

        $params['deptid'] = $this->_user->user_dept_id;

        if ($this->params->requests->isAjax())
        {
            $service = $this->services->Button->M751->Getequips->Save($params);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    /**
     * Hien thi chon nhieu vat tu trong tab thiet bi - yeu cau m751
     */
    public function getmaterialsIndexAction() {
        // Lay ifid de truyen sang cac action khac
        // $mStock  = new Qss_Model_Warehouse_Main();
        $ifid    = $this->params->requests->getParam('ifid', 0);
        $filter  = $this->params->requests->getParam('m412_material_filter', 0);

        // $this->html->stocks = ($filter != 1)?$mStock->getAll($this->_user):array();
        $this->html->ifid = $ifid;
    }

    /**
     * Hien thi noi dung thiet bi theo tim kiem
     */
    public function getmaterialsSelectAction() {
        $mCommon = new Qss_Model_Extra_Extra();
        $filter  = $this->params->requests->getParam('m412_material_filter', 0);
        $mTable  = Qss_Model_Db::Table('OSanPham');

        if($filter == 1) { // Vật tư
            $mTable->where(' IFNULL(CongCu, 0) = 0 ');
            $mTable->where(' IFNULL(MaTam, 0) = 0 ');
            $mTable->orderby('MaSanPham');
        }
        else { // Công cụ dụng cụ
            $mTable->where(' IFNULL(CongCu, 0) = 1 ');
            $mTable->where(' IFNULL(MaTam, 0) = 0 ');
            $mTable->orderby('MaSanPham');
        }

        $this->html->data = $mTable->fetchAll();

    }

    /**
     * Hien thi noi dung vat tu da chon
     */
    public function getmaterialsShowAction() {
        $ifid   = $this->params->requests->getParam('ifid', 0);
        $mTable = Qss_Model_Db::Table('OYeuCauVatTu');
        $mTable->where(sprintf(' IFID_M751 = %1$d ', $ifid));

        $this->html->data = $mTable->fetchAll();
    }

    /**
     * Luu lai cac vat tu da chon vao vat tu - cong cu M751
     */
    public function getmaterialsSaveAction() {
        $params = $this->params->requests->getParams();

        $params['deptid'] = $this->_user->user_dept_id;

        if ($this->params->requests->isAjax())
        {
            $service = $this->services->Button->M751->Getmaterials->Save($params);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
}