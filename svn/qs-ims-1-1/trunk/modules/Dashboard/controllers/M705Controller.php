<?php

class Dashboard_M705Controller extends Qss_Lib_Controller
{
    public function init()
    {
        parent::init();
    }

    public function countbygroupAction()
    {
        $model  = new Qss_Model_M705_Equipment();
        $this->html->countOut         = $model->countByGroup();
    }
}