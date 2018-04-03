<?php
class Qss_Model_Maintenance_Notification extends Qss_Model_Abstract
{
	
	public function __construct()
	{
		parent::__construct();
	}

	public function getNewNotification()
	{
		$sql = sprintf('select * from OYeuCauBaoTri as yc
				inner join qsiforms on qsiforms.IFID = yc.IFID_M747
				where Status = 1');
		return $this->_o_DB->fetchAll($sql);			
	}

}
