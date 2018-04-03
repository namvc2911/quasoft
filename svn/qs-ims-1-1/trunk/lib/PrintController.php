<?php
/**
 *
 * @author HuyBD
 *
 */
class Qss_Lib_PrintController extends Qss_Lib_Controller
{
	protected $_form;
	
	protected $_db;
	
	protected $_params;
	/**
	 *
	 * @return void
	 */
	public function init ()
	{
		parent::init();
		$ifid = $this->params->requests->getParam('ifid',0);

		$deptid = $this->params->requests->getParam('deptid',0);

		$this->_form = new Qss_Model_Form();
		$this->_form->initData($ifid, 1);
		$rights = $this->_form->i_fGetRights($this->_user->user_group_list);
		if(!$this->b_fCheckRightsOnForm($this->_form, 2))
		{
			$this->setLayoutRender(false);
			die;
		}
		$this->_db = Qss_Db::getAdapter('main');
		$this->_params = new stdClass();
		if($this->_form->i_IFID)
		{
			foreach($this->_form->o_fGetMainObjects() as $object)
			{
				$sql = sprintf('select * from %1$s where IFID_%2$s = %3$d',$object->ObjectCode,$this->_form->FormCode,$this->_form->i_IFID);
				$dataSQL = $this->_db->fetchOne($sql);
				foreach($dataSQL as $key=>$val)
				{
					$this->_params->$key = $val;
				}
			}
		}
		foreach($this->_form->a_fGetSubObjects() as $object)
		{
			if($this->_form->i_IFID)
			{
				$param = array();
				$sql = sprintf('select * from %1$s where IFID_%2$s = %3$d',$object->ObjectCode,$this->_form->FormCode,$this->_form->i_IFID);
				$dataSQL = $this->_db->fetchAll($sql);
				foreach($dataSQL as $item)
				{
					$newobject = new stdClass();
					foreach($item as $key=>$val)
					{
						$newobject->$key = $val;
					}
					$param[] = $newobject;
				}
				$this->_params->{$object->ObjectCode} = $param;
			}
		}
	}
	
}
?>