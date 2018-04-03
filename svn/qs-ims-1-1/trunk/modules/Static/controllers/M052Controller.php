<?php

class Static_M052Controller extends Qss_Lib_Controller
{
    public function init() {
        parent::init();
        if($this->_mobile) {
            $this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/mwide.php';
        }
        $this->headScript($this->params->requests->getBasePath() . '/js/tabs.js');
    }

    public function indexAction() {

    }

    /**
     * View: cột bên trái cây phòng ban bộ phận
     */
    public function departmentAction() {
        $nodeID   = $this->params->requests->getParam('nodeid'  , 0);
        $search   = $this->params->requests->getParam('search'  , 0);

        echo Qss_Json::encode($this->getNodeData($nodeID, $search));
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

    /**
     * Lấy dữ liệu node
     */
    private function getNodeData($nodeid = 0, $searchDep = '')
    {
        $returnData = array();
        $model      = new Qss_Model_M052_Main();
        $i          = 0;

        $data = $model->getNodeByParentNode($nodeid, $searchDep);

        foreach ($data as $item) {
            $returnData[$i]['id']     = 'M052'.'_'.$item->IOID; // ioid của phòng ban
            $returnData[$i]['parent'] = ($nodeid == 0 || $searchDep != '')?'#':'M052'.'_'.$nodeid; // root node không có parent

            if($item->Loai == 'E_COMPANY') {
                $returnData[$i]['icon'] = "/images/jstree/location.png";
            }
            elseif($item->Loai == 'E_DEPARTMENT') {
                $returnData[$i]['icon'] = "/images/jstree/eq_type.png";
            }
            elseif($item->Loai == 'E_SECTION') {
                $returnData[$i]['icon'] = "/images/jstree/eq_group.png";
            }
            elseif($item->Loai == 'E_GROUP') {
                $returnData[$i]['icon'] = "/images/jstree/component.png";
            }
            else {
                $returnData[$i]['icon'] = "";
            }


            $returnData[$i]['text']            = "{$item->MaPhongBan} - {$item->TenPhongBan}" ;// Tên phòng ban
            $returnData[$i]['attr']['nodeid']  = $item->IOID; // ioid của phòng ban
            $returnData[$i]['state']['opened'] = false;
            $returnData[$i]['children']        = $item->HasChild?true:false;
            $i++;
        }
        // echo '<pre>'; print_r($returnData); die;

        return $returnData;
    }

    /**
     * View: cột bên trái Danh sách nhân viên phòng ban
     */
    public function employeeAction() {
        $model     = new Qss_Model_M052_Main();
        $nodeID    = $this->params->requests->getParam('nodeid'  , 0);
        $search    = $this->params->requests->getParam('search'  , '');
        $pageno    = $this->params->requests->getParam('pageno'  , 1);
        $display   = $this->params->requests->getParam('display' , 50);
        $count     = $model->countEmployees($nodeID, $search);
        $totalPage = ceil($count/$display);
        $pageno    = $pageno < $totalPage?$pageno:$totalPage;
        $pageno    = $pageno > 0?$pageno:1;

        $this->html->totalPage = $totalPage;
        $this->html->count = Qss_Lib_Util::formatNumber($count);
        $this->html->empls = $model->getEmployees($nodeID, $search, $pageno, $display);
        $this->html->page  = $pageno;
        $this->html->next  = ($pageno < $totalPage)?($pageno + 1):$totalPage;
        $this->html->prev  = ($pageno > 1)?($pageno - 1):1;
    }

    /**
     * View: Cột bên trái form search
     */
    public function searchAction() {

    }

    /**
     * Hiển thị thông tin ban đầu
     */
    public function contentIndexAction() {

    }

    /**
     * Hiển thị thông tin phòng ban khi chọn
     */
    public function contentDepartmentIndexAction() {
        $model    = new Qss_Model_M052_Main();
        $nodeID   = $this->params->requests->getParam('nodeid'  , 0);
        $phongBan = $model->getPhongBanByIOID($nodeID);

        $form = new Qss_Model_Form();
        $form->initData($phongBan->IFID_M319, $phongBan->DeptID);

        $this->html->main = $this->views->Instance->Form->Detail($form, $this->_user);
    }

    /**
     * Hiển thị thông tin nhân viên khi chọn nhân viên
     */
    public function contentEmployeeIndexAction() {
        $model    = new Qss_Model_M052_Main();
        $nodeID   = $this->params->requests->getParam('emp'  , 0);
        $phongBan = $model->getNhanVienByIOID($nodeID);

        $form = new Qss_Model_Form();
        $form->init('M316');
        $form->initData($phongBan->IFID_M316, $phongBan->DeptID);
		$this->html->ifid = $phongBan->IFID_M316;
		$this->html->deptid = $phongBan->DeptID; 
        $this->html->main = $this->views->Instance->Form->Detail($form, $this->_user);
    }
}