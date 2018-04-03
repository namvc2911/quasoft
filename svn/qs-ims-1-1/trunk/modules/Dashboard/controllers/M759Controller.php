<?php

class Dashboard_M759Controller extends Qss_Lib_Controller
{
    public function init()
    {
        parent::init();
        $this->headScript($this->params->requests->getBasePath() . '/js/form-list.js');
    }

    public function mytasksAction()
    {
        $mWorkorder  = new Qss_Model_Maintenance_Workorder();

        $formM759  = new Qss_Model_Form();
        $formM759->init('M759', $this->_user->user_dept_id, $this->_user->user_id);

        $rightM759 = $this->getFormRights($formM759);

        $this->html->countWO          = $mWorkorder->countIssueWorkordersByUser($this->_user->user_id);
        $this->html->rightM759        = $rightM759&1 || $rightM759&4;
    }
}