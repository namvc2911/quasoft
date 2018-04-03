<?php
class Qss_View_Extra_Warehouse extends Qss_View_Abstract
{

	public function __doExecute ($form,$selected)
	{
		$user = Qss_Register::get('userinfo');
		$rights = $form->i_fGetRights($user->user_group_list);
		$this->html->rights = $rights;
		$where = ' 1=1 ';
		if(Qss_Lib_System::formSecure('M601'))
		{
			$user = Qss_Register::get('userinfo');
			$where = sprintf(' IFID_M601 in (SELECT IFID FROM qsrecordrights
						WHERE UID = %1$d and FormCode="M601")'
						,$user->user_id);
		}
		$model = new Qss_Model_Extra_Extra();
		$this->html->kho = $model->getTable('*','ODanhSachKho',$where);
		//echo '<pre>';print_r($this->html->kho);die;
	}
}
?>