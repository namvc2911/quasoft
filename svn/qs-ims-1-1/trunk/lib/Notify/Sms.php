<?php
/**
 *
 * @author HuyBD
 *
 */
class Qss_Lib_Notify_Sms extends Qss_Lib_Notify
{
	protected $_sms; 
	
	protected $_step;
	
	public function init($step = null)
	{
		parent::init();
		$this->_step = $step;
		//$this->_maillist = array();
		$this->_sms = new Qss_Model_Sms();
		$model = new Qss_Model_Notify_Sms();
		$dataSQL = $model->getNotifyMembers(get_class ($this));
		foreach($dataSQL as $item)
		{
			if($item->Mobile)
			{
				$this->_maillist[] = $item->Mobile;
			}	
		}
		$dataSQL = $model->getNotifyGroups(get_class ($this));
		foreach($dataSQL as $item)
		{
			if($item->Mobile)
			{
				$this->_maillist[] = $item->Mobile;
			}	
		}
		$dataSQL = $model->getSMS(get_class ($this));
		if($dataSQL)
		{
			$extra = $dataSQL->Extra;
			if($extra)
			{
				$sms = explode(',',$extra);
				foreach ($sms as $item)
				{
					$this->_maillist[] = $item;	
				}
			}	
		}
		
	}
}
?>