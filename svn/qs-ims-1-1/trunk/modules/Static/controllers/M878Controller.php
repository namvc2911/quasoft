<?php
class Static_M878Controller extends Qss_Lib_Controller {
    public function init() {
        parent::init();
    }

    public function indexAction() {

    }

    public function showAction() {
        $end         = $this->params->requests->getParam('end', '');
        $end         = Qss_Lib_Date::displaytomysql($end);
        $course      = $this->params->requests->getParam('course', 0);
        $dept        = $this->params->requests->getParam('department', 0);
        $emp         = $this->params->requests->getParam('employee', 0);
        $filterEmp   = $this->params->requests->getParam('filterEmp', 0);
        $filterTitle = $this->params->requests->getParam('filterTitle', 0);

        $mM878 = new Qss_Model_M878_Main();
        $this->html->data = $mM878->getData($end, $course, $dept, $emp, $filterEmp, $filterTitle);
    }

    public function saveAction()
    {
        $a_Params = $this->params->requests->getParams();

        if ( $this->params->requests->isAjax())
        {
            $service = $this->services->Static->M878->Createclass($a_Params);
            echo $service->getMessage();
        }

        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }
}