<?php
class Qss_Bin_Onload_Base_OYeuCauBaoTri extends Qss_Lib_Onload
{
    /**
     * Điền mã khu vực khi nhập mã thiết bị
     */
    protected function _DienMaKhuVuc() {
        if(Qss_Lib_System::fieldActive('OYeuCauBaoTri', 'MaKhuVuc')) {
            $maKhuVuc     = trim($this->_object->getFieldByCode('MaKhuVuc')->getValue());
            $refMaThietBi = (int)$this->_object->getFieldByCode('MaThietBi')->getRefIOID();

            if($maKhuVuc == '') {
                $mThietBi = Qss_Model_Db::Table('ODanhSachThietBi');
                $mThietBi->where($mThietBi->ifnullNumber('IOID', $refMaThietBi));
                $oThietBi = $mThietBi->fetchOne();

                if($oThietBi && @$oThietBi->Ref_MaKhuVuc) {
                    $this->_object->getFieldByCode('MaKhuVuc')->setValue($oThietBi->MaKhuVuc);
                    $this->_object->getFieldByCode('MaKhuVuc')->setRefIOID($oThietBi->Ref_MaKhuVuc);
                }
            }
        }
    }

    /**
     * Onload lại tên mặt hàng có thể sửa khi sử dụng mã tạm
     */
    protected function _MaTam()
    {
        if (Qss_Lib_System::fieldActive('ODanhSachThietBi', 'MaTam')) {

            if(!$this->_object->getFieldByCode('MaThietBi')->bReadOnly) {
                $item = (int)$this->_object->getFieldByCode('MaThietBi')->getRefIOID();

//            if (!$item) {
//                $mTable = Qss_Model_Db::Table('OYeuCauBaoTri');
//                $mTable->where(sprintf('IFID_M747 = %1$d', $this->_object->i_IFID));
//                $data = $mTable->fetchOne();
//
//                if ($data) {
//                    $item = (int)$data->Ref_MaThietBi;
//                }
//            }

                $this->_object->getFieldByCode('TenThietBi')->bReadOnly = true;

                $mItem = Qss_Model_Db::Table('ODanhSachThietBi');
                $mItem->where(sprintf('IOID = %1$d', $item));
                $oItem = $mItem->fetchOne();

                if ($oItem) {
                    if ($oItem->MaTam) {
                        $this->_object->getFieldByCode('TenThietBi')->bReadOnly = false;
                    } else {
                        $this->_object->getFieldByCode('TenThietBi')->setValue($oItem->TenThietBi);
                    }
                }
            }

        }
    }

    protected function _NguoiYeuCau() {
        $mEmp = new Qss_Model_Maintenance_Employee();
        $user = Qss_Register::get('userinfo');
        $emp  = $mEmp->getEmployeeByUserID($user->user_id); // Lấy nhân viên theo user đă

        if ($emp && $emp->IOID && !$this->_object->getFieldByCode('NguoiYeuCau')->getRefIOID())
        {
            $this->_object->getFieldByCode('NguoiYeuCau')->setValue($emp->TenNhanVien);
            $this->_object->getFieldByCode('NguoiYeuCau')->setRefIOID($emp->IOID);
        }
    }

    public function MaThietBi()
    {
        $this->_object->getFieldByCode('MaThietBi')->arrFilters[] = sprintf(' IFNULL(v.TrangThai, 0) = 0 ');

        $suDungMaTam = '';
        $makhuvuc    = $this->_object->getFieldByCode('MaKhuVuc')->getRefIOID();

        // Nếu sử dụng mã tạm kiểm tra thì lấy thiết bị trong khu vực đó và các thiết bị là mã tạm
        // Ngược lại thì chỉ lấy thiết bị trong khu vực đó thôi
        if(Qss_Lib_System::fieldActive('OYeuCauBaoTri', 'MaKhuVuc') && $makhuvuc) {
            if(Qss_Lib_System::fieldActive('ODanhSachThietBi', 'MaTam')) {
                $suDungMaTam = 'or IFNULL(v.MaTam, 0) = 1';
            }

            $this->_object->getFieldByCode('MaThietBi')->arrFilters[] = sprintf(' 
                    (
                        v.Ref_MaKhuVuc in (
                            SELECT IOID 
                            FROM OKhuVuc
						    WHERE OKhuVuc.lft >= (SELECT lft FROM OKhuVuc WHERE IOID=%1$d) 
						        and OKhuVuc.rgt <= (SELECT rgt FROM OKhuVuc WHERE IOID = %1$d)
                        )
                        %2$s
                    )', $makhuvuc, $suDungMaTam);
        }
    }

    public function NguoiYeuCau()
    {
        if(Qss_Lib_System::fieldActive('OYeuCauBaoTri', 'MaKhuVuc'))
        {
            $makhuvuc = $this->_object->getFieldByCode('MaKhuVuc')->getRefIOID();
            if($makhuvuc)
            {
                $sql = sprintf('select * from OKhuVuc where IOID = %1$d',$makhuvuc);
                $KhuVuc = $this->_db->fetchOne($sql);
                $this->_object->getFieldByCode('NguoiYeuCau')->arrFilters[] = sprintf('
            	v.IOID in (SELECT Ref_MaNV FROM ONhanVien
            			inner join ODonViSanXuat on ODonViSanXuat.IFID_M125 =  ONhanVien.IFID_M125
						WHERE IFNULL(BaoTri, 0) = 0 AND ODonViSanXuat.IFID_M125 in (SELECT IFID_M125 FROM OThietBi
						inner join OKhuVuc on OKhuVuc.IOID = OThietBi.Ref_Ma
						WHERE OKhuVuc.lft <= %1$d and OKhuVuc.rgt >= %2$d))'
                    ,$KhuVuc->lft
                    ,$KhuVuc->rgt);
            }
        }
    }
}