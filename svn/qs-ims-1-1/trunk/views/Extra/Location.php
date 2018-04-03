<?php
class Qss_View_Extra_Location extends Qss_View_Abstract
{

	public function __doExecute ($form,$selected)
	{
		$user = Qss_Register::get('userinfo');
		$rights = $form->i_fGetRights($user->user_group_list);
		$this->html->rights = $rights;
		//$where = sprintf(' DeptID in (%1$s)',$user->user_dept_list);
		$location = new Qss_Model_Maintenance_Location();
		$locations = $location->getLocationByCurrentUser();
		$where = '';
		foreach ($locations as $item)
		{
			if($where)
			{
				$where .= ' or ';
			}
			$where .= sprintf('(lft >= %1$d and rgt <= %2$d)',$item->lft,$item->rgt);
		}

		$where = $where?" ({$where}) ":'';

		$model = new Qss_Model_Extra_Extra();
		$this->html->khuvuc = $model->getNestedSetTable('OKhuVuc',$where);
	}
}
?>