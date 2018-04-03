<?php

class Dashboard_M840Controller extends Qss_Lib_Controller
{
    public function init()
    {
        parent::init();
    }

    public function mytasksAction()
    {
        $formM840  = new Qss_Model_Form();
        $formM840->init('M840', $this->_user->user_dept_id, $this->_user->user_id);

        $rightM840 = $this->getFormRights($formM840);

        $this->html->rightM840       = $rightM840&1 || $rightM840&2 || $rightM840&4;
        $this->html->rightCreateM840 = $rightM840&1;
    }
}