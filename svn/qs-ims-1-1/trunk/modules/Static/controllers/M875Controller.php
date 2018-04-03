<?php

class Static_M875Controller extends Qss_Lib_Controller
{
    public function init()
    {
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
        parent::init();
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
    }

    public function indexAction() {

    }

    public function showAction() {
        $projectIOID = $this->params->requests->getParam('project', 0);
        $ycccIOID = $this->params->requests->getParam('yccc', 0);
        $common      = new Qss_Model_Extra_Extra();
        $model       = new Qss_Model_M875_Main();

        $this->html->data    = $model->getData($projectIOID, 0,$ycccIOID);
        $this->html->project = $common->getTableFetchOne('ODuAn', array('IOID'=>$projectIOID));
    }
}