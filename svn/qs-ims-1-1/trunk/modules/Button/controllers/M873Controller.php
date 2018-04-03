<?php
class Button_M873Controller extends Qss_Lib_Controller
{
    public function init()
    {

        $this->i_SecurityLevel = 15;
        parent::init();
        $this->headScript($this->params->requests->getBasePath() . '/js/common.js');
    }

    public function getmovedIndexAction() {
        $ifid = $this->params->requests->getParam('ifid', 0);
        $this->html->ifid   = $ifid;
        $this->html->deptid = $this->_user->user_dept_id;
    }

    public function getmovedShowAction() {
        $common    = new Qss_Model_Extra_Extra();
        $mM873     = new Qss_Model_M873_Moving();
        $ifid      = $this->params->requests->getParam('M873_Get_By_Request_IFID', 0);
        $strItem   = $this->params->requests->getParam('M873_Get_By_Request_Equip', '');
        $page      = $this->params->requests->getParam('M873_Get_By_Request_PageNo', 1);
        $perpage   = $this->params->requests->getParam('M873_Get_By_Request_Display', 20);
        $page      = $page?$page:1;
        $perpage   = $perpage?$perpage:20;
        $total     = 0;
        $totalPage = 0;
        $OYeuCauThietBi = array();

        // Lấy thông tin bản ghi xuất kho
        $ODieuDong = $common->getTableFetchOne('ODieuDongThietBiVe', array('IFID_M873'=>$ifid));

        // Lấy thông tin phiếu yêu cầu (Chỉ lấy khi có yêu cầu điều động và chỉ lấy theo 1 yêu cầu điều động)
        if($ODieuDong && $ODieuDong->DuAn) {
            $total      = $mM873->countThietBiDuAn($ODieuDong->DuAn, $strItem);
            $totalPage  = ceil($total/$perpage);
            $page       = $page < $totalPage?$page:$totalPage;

            $OYeuCauThietBi = $mM873->getThietBiByDuAn($ODieuDong->DuAn, $ODieuDong->IFID_M873, $page, $perpage, $strItem);
        }

        $this->html->OYeuCauThietBi = $OYeuCauThietBi;
        $this->html->page           = $page;
        $this->html->display        = $perpage;
        $this->html->totalPage      = $totalPage;
        $this->html->total          = $total;
        $this->html->next           = ($page < $totalPage)?($page + 1):$totalPage;
        $this->html->prev           = ($page > 1)?($page - 1):1;
    }

    public function getmovedSaveAction() {
        $params = $this->params->requests->getParams();

        if ($this->params->requests->isAjax())
        {
            $service = $this->services->Button->M873->Getmoved->Save($params);
            echo $service->getMessage();
        }
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
}