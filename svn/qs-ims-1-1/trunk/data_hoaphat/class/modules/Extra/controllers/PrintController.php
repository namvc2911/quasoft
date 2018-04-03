<?php
/**
 * @author: ThinhTuan
 * @component: 
 * @place: modules/Extra/Controllers/PrintController.php
 */
class Extra_PrintController extends Qss_Lib_PrintController
{
	/**
	 *
	 * @return unknown_type
	 */
	
	public function init ()
	{
		//$this->i_SecurityLevel = 15;
                $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/print.php';
		parent::init();
	}
	
	
	public function pmWoAction()
	{
		$extra = new Qss_Model_Extra_Extra(); 
		$this->html->location = $extra->getTable(array('*'), 'ODanhSachThietBi', array('IOID'=>$this->_params->Ref_MaThietBi), array(), 1, 1);
		$this->html->data = $this->_params;
	}
        
        public function pmPwoAction()
	{
		$extra = new Qss_Model_Extra_Extra(); 
		$this->html->location = $extra->getTable(array('*'), 'ODanhSachThietBi', array('IOID'=>$this->_params->Ref_MaThietBi), array(), 1);
		$this->html->data = $this->_params;
	}
}
?>