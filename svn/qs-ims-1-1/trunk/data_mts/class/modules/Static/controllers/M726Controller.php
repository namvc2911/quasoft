<?php
class Static_M726Controller extends Qss_Lib_Controller
{
    public function init()
    {
        $this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
        parent::init();
        $this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
    }


    public function indexAction()
    {

    }

    public function showAction()
    {
    	$nhom = $this->params->requests->getParam('group');
    	$model = new Qss_Model_Mtsequips();
    	$tbl = Qss_Model_Db::Table('ONhomThietBi');
    	$tbl->select('*');
    	$tbl->where('IOID='.(int)$nhom);
    	$this->html->nhom = $tbl->fetchOne();
        $this->html->equips = $model->getThietBiTheoNhom($nhom);
    }
}

?>