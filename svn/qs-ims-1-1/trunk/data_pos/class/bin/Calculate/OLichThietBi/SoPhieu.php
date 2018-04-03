<?php
class Qss_Bin_Calculate_OLichThietBi_SoPhieu extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
        $user     = Qss_Register::get('userinfo');
        $sql      = sprintf('SELECT * FROM qsdepartments WHERE DepartmentID = %1$d', $user->user_dept_id);
        $dSql     = $this->_db->fetchOne($sql);
        $document = new Qss_Model_Extra_Document($this->_object);

        if($dSql) {
            $document->setPrefix('BBGN-'.date('y').'-'.$dSql->DeptCode.'-');
        }

        return $document->getDocumentNo();
	}
}
?>