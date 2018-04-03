<?php
class Qss_View_Extra_Search_M504 extends Qss_View_Abstract
{

	public function __doExecute ($form)
	{
		$filter = Qss_Cookie::get('form_' . $form->FormCode . '_filter', 'a:0:{}');
		$filter = unserialize($filter);
		$user = Qss_Register::get('userinfo');
		$this->html->selected         = (int) @$filter['khuvucbanhang'];
		$where = '';
		if (Qss_Lib_System::formSecure('M848'))
		{
			$where .= sprintf(' IFID_M848 in(select IFID from qsrecordrights where UID = %1$d '
				,$user->user_id);
		}
		$tbl = Qss_Model_Db::Table('OKhuVucBanHang');
		$this->html->phases = $tbl->fetchAll();
	}
}
?>