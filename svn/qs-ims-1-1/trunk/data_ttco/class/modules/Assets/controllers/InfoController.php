<?php

class Assets_InfoController extends Qss_Lib_Controller
{

    public $_user;

    public function init()
    {
    	$this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
        parent::init();
        $this->_user = Qss_Register::get('userinfo');
    }

    public function profileAction()
    {

    }

    public function profile1Action()
    {
        $common    = new Qss_Model_Extra_Extra();
        $assetIOID = $this->params->requests->getParam('asset', 0);

        $this->html->asset  = $common->getTableFetchOne('OQuanLyTaiSan', array('IOID'=>$assetIOID));
        $this->html->report = $common->getTable(array('*'), 'OThongTinTaiSan', array('Ref_MaTaiSan'=>$assetIOID), array(' TuNgay ASC '), 'NO_LIMIT');
    }

}