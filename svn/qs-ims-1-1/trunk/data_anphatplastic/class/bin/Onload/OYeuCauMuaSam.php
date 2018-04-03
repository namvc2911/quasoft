<?php
class Qss_Bin_Onload_OYeuCauMuaSam extends Qss_Lib_Onload
{
    /**
     * onInsert
     */
    public function __doExecute()
    {
        parent::__doExecute();

        $mEmp = new Qss_Model_Maintenance_Employee();
        $user = Qss_Register::get('userinfo');
        $emp  = $mEmp->getEmployeeByUserID($user->user_id); // Lấy nhân viên theo user đă


        if($emp)
        {
            $maDVBT   = $emp->MaDonViThucHien ? $emp->MaDonViThucHien : '';
//            $tenDVBT  = $emp->TenDonViThucHien ? $emp->TenDonViThucHien : '';
            $ioidDVBT = $emp->RefDonViThucHien ? $emp->RefDonViThucHien : 0;

            if ($ioidDVBT && $emp->BaoTri && !$this->_object->getFieldByCode('DonViYeuCau')->getRefIOID())
            {
                $inDonVi = true;

                if(Qss_Lib_System::formSecure('M125'))
                {
                    $user = Qss_Register::get('userinfo');
                    $mTable = Qss_Model_Db::Table('ODonViSanXuat');
                    $mTable->join('INNER JOIN qsrecordrights ON ODonViSanXuat.IFID_M125 = qsrecordrights.IFID');
                    $mTable->where(sprintf('ODonViSanXuat.IOID = %1$d', $ioidDVBT));
                    $mTable->where(sprintf('qsrecordrights.UID = %1$d', $user->user_id));
                    $inDonVi = $mTable->fetchOne()?true:false;
                }

                if($inDonVi)
                {
                    $this->_object->getFieldByCode('DonViYeuCau')->setValue($maDVBT);
                    $this->_object->getFieldByCode('DonViYeuCau')->setRefIOID($ioidDVBT);
                }

            }
        }

        if ($emp && $emp->IOID && !$this->_object->getFieldByCode('NguoiDeNghi')->getRefIOID() && (int)$this->_object->i_IOID == 0)
        {
            $this->_object->getFieldByCode('NguoiDeNghi')->setValue($emp->TenNhanVien);
            $this->_object->getFieldByCode('NguoiDeNghi')->setRefIOID($emp->IOID);
        }

        if(Qss_Lib_System::fieldActive('OYeuCauMuaSam', 'MaKho'))
        {
            $emp = $mEmp->getStockEmployeeByUserID($user->user_id); // Lấy nhân viên theo user đăng nhập

            if ($emp && $emp->IOID && !$this->_object->getFieldByCode('MaKho')->getRefIOID())
            {
                $this->_object->getFieldByCode('MaKho')->setValue($emp->MaKho);
                $this->_object->getFieldByCode('MaKho')->setRefIOID($emp->IOID);
            }
        }
    }

    public function DonViYeuCau()
    {
        if(Qss_Lib_System::formSecure('M125'))
        {
            $user = Qss_Register::get('userinfo');

            $this->_object->getFieldByCode('DonViYeuCau')->arrFilters[] = sprintf(' 
                    v.IFID_M125 in (
                        SELECT IFID 
                        FROM qsrecordrights
						WHERE UID = %1$d and FormCode="M125")', $user->user_id);
        }
    }
}