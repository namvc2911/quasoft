<?php
class Qss_Bin_Calculate_OYeuCauMuaSam_SoPhieu extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
        $donVi = $this->_object->getFieldByCode('DonViYeuCau')->getRefIOID();

        if(!$donVi)
        {
            $mEmp = new Qss_Model_Maintenance_Employee();
            $user = Qss_Register::get('userinfo');
            $emp  = $mEmp->getEmployeeByUserID($user->user_id); // Lấy nhân viên theo user đă

            if($emp) {
                $donVi   = $emp->RefDonViThucHien ? $emp->RefDonViThucHien : 0;
            }
        }


        if(Qss_Lib_System::formSecure('M125'))
        {
            $user = Qss_Register::get('userinfo');
            $mTable = Qss_Model_Db::Table('ODonViSanXuat');
            $mTable->join('INNER JOIN qsrecordrights ON ODonViSanXuat.IFID_M125 = qsrecordrights.IFID');
            $mTable->where(sprintf('ODonViSanXuat.IOID = %1$d', $donVi));
            $mTable->where(sprintf('qsrecordrights.UID = %1$d', $user->user_id));
            $donVi = $mTable->fetchOne()?$donVi:0;
        }


        $sql   = sprintf('select * from ODonViSanXuat where IOID = %1$d', $donVi);
        $dat   = $this->_db->fetchOne($sql);
        $pre   = 'RFP.';
        $mDoc  = new Qss_Model_Extra_Document($this->_object);

        if($dat)
        {
            if($dat->Ma)
            {
                $pre = $dat->Ma.'.';
            }
            else
            {
                if($dat->BaoTri)
                {
                    $pre = 'KT.';
                }

                if($dat->Ma == 'KHO')
                {
                    $pre = 'CC.';
                }
            }

        }

        $mDoc->setDocField('SoPhieu');
        $mDoc->setPrefix($pre);
        $mDoc->setLenth(4);

		return $mDoc->getDocumentNo();
	}
}
?>