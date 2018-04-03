<?php

class Dashboard_M759mtsController extends Qss_Lib_Controller
{
    public function init()
    {
        parent::init();
    }

    public function implementAction()
    {
        $model  = new Qss_Model_Mtsmaintain();
        $this->html->countOut          = $model->implementByLocation();
    }
}