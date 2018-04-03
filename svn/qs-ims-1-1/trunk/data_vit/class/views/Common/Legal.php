<?php
class Qss_View_Common_Legal extends Qss_View_Abstract
{

	public function __doExecute ()
	{
        $user             = Qss_Register::get('userinfo');
        $this->html->ini  = $this->loadHeaderIniFile();
        $this->html->user = $user;

        $dept = Qss_Model_Db::Table('qsdepartments');
        $dept->where(sprintf(' DepartmentID = %1$d', $user->user_dept_id));
        $objDept = $dept->fetchOne();

        $this->html->CongTy = $objDept?$objDept->Name:'';
	}
		
	
}
?>