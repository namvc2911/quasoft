<?php
/**
 *
 * @author HuyBD
 *
 */
class Qss_Lib_Notify_Validate extends Qss_Lib_Notify
{
	protected $_alert;

	protected $_step;
	
	public function init($step = null)
	{
		parent::init();
		$this->_step = $step;
		//$this->_maillist = array();
		$this->_alert = new Qss_Model_Alert();
		
		$model = new Qss_Model_Notify_Validate();
//		$dataSQL = $model->getNotifyMembers(get_class ($this));
//		foreach($dataSQL as $item)
//		{
//			$this->_maillist[] = $item->UID;
//		}
//		$dataSQL = $model->getNotifyGroups(get_class ($this));
//		foreach($dataSQL as $item)
//		{
//			$this->_maillist[] = $item->UID;
//		}
		
	}
}
?>