<?php

class Dashboard_M765Controller extends Qss_Lib_Controller
{
    public function init()
    {
        parent::init();
    }

    public function mytasksAction()
    {
        $formM816  = new Qss_Model_Form();
        $formM816->init('M765', $this->_user->user_dept_id, $this->_user->user_id);
        $rightM816 = $this->getFormRights($formM816);

        $this->html->rightM816       = $rightM816&1 || $rightM816&2 || $rightM816&4;
    }
}