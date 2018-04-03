<?php
/**
 * Model for instanse form
 *
 */
class Qss_Model_Alert extends Qss_Model_Abstract
{


	public function __construct ()
	{
		parent::__construct();

	}
	public function save($uid,$content)
	{
		$sql = sprintf('insert into qsalerts(UID,Content) values(%1$d,%2$s)'
			,$uid
			,$this->_o_DB->quote($content));
		$this->_o_DB->execute($sql);
	}
	public function getAlertByUID($uid)
	{
		$sql = sprintf('select * from qsalerts where UID = %1$s',$uid);
		$this->_o_DB->fetchAll($sql);
	}
	public function hasAlert($uid)
	{
		$sql = sprintf('select 1 from qsalerts where UID = %1$s',$uid);
		$this->_o_DB->fetchOne($sql);
	}
	public function delete($id)
	{
		$sql = sprintf('delete from qsalerts where ALID = %1$s',$id);
		$this->_o_DB->execute($sql);
	}
	public function deleteByUID($uid)
	{
		$sql = sprintf('delete from qsalerts where UID = %1$s',$uid);
		$this->_o_DB->execute($sql);
	}
}
?>