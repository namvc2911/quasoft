<?php
class Qss_Bin_Onload_OQuaTrinhCongTac extends Qss_Lib_Onload {
    public function __doExecute() {
        parent::__doExecute();

        // @todo: So sánh giá trị mới cũ của nhân viên để load lại thông tin phòng ban ở bước 1
        if($this->_object) {
            if((int)$this->_object->i_IFID == 0
                || (
                    // $this->_form &&
                    $this->_object->intStatus == 1 &&
                    $this->_object->getFieldByCode('MaNhanVien')->getRefIOID() != $this->_params->Ref_MaNhanVien
                )
            ) {
                $mCommon     = new Qss_Model_Extra_Extra();
                $iMaNhanVien = $this->_object->getFieldByCode('MaNhanVien')->getRefIOID();
                $nhanVien    = $mCommon->getTableFetchOne('ODanhSachNhanVien', array('IOID'=>$iMaNhanVien));

                if($nhanVien && $nhanVien->Ref_MaPhongBan) {
                    // Lấy toàn bộ phòng ban cha của nhân viên
                    $sql = sprintf('
                        SELECT *
                        FROM OPhongBan
                        WHERE lft <= (SELECT lft FROM OPhongBan WHERE IOID = %1$d)
                        AND rgt >= (SELECT rgt FROM OPhongBan WHERE IOID = %1$d)
                        ORDER BY lft
                    ', $nhanVien->Ref_MaPhongBan);
                            $data = $this->_db->fetchAll($sql);

                    $sCongTy   = '';
                    $iCongTy   = 0;
                    $sPhongBan = '';
                    $iPhongBan = 0;
                    $sBoPhan   = '';
                    $iBoPhan   = 0;
                    $sNhom     = '';
                    $iNhom     = 0;

                    foreach($data as $item) {
                        // Lấy công ty đầu tiên gặp
                        if($item->Loai == 'E_COMPANY' && $iCongTy == 0) {
                            $sCongTy = $item->TenPhongBan;
                            $iCongTy = $item->IOID;
                        }

                        // Lấy phòng ban đầu tiên gặp
                        if($item->Loai == 'E_DEPARTMENT' && $iPhongBan == 0) {
                            $sPhongBan = $item->TenPhongBan;
                            $iPhongBan = $item->IOID;
                        }

                        // Lấy bộ phận lần đâu tiên gặp
                        if($item->Loai == 'E_SECTION' && $iBoPhan == 0) {
                            $sBoPhan = $item->TenPhongBan;
                            $iBoPhan = $item->IOID;
                        }

                        // Lấy nhóm lần đầu tiên gặp
                        if($item->Loai == 'E_GROUP' && $iNhom == 0) {
                            $sNhom = $item->TenPhongBan;
                            $iNhom = $item->IOID;
                        }
                    }

                    $this->_object->getFieldByCode('NoiLamViec')->setValue($nhanVien->MaPhongBan);
                    $this->_object->getFieldByCode('NoiLamViec')->setRefIOID($nhanVien->Ref_MaPhongBan);
                    $this->_object->getFieldByCode('CongTy')->setValue($sCongTy);
                    $this->_object->getFieldByCode('CongTy')->setRefIOID($iCongTy);
                    $this->_object->getFieldByCode('PhongBan')->setValue($sPhongBan);
                    $this->_object->getFieldByCode('PhongBan')->setRefIOID($iPhongBan);
                    $this->_object->getFieldByCode('BoPhan')->setValue($sBoPhan);
                    $this->_object->getFieldByCode('BoPhan')->setRefIOID($iBoPhan);
                    $this->_object->getFieldByCode('Nhom')->setValue($sNhom);
                    $this->_object->getFieldByCode('Nhom')->setRefIOID($iNhom);

                    $this->_object->getFieldByCode('NoiLamViecMoi')->setValue($nhanVien->MaPhongBan);
                    $this->_object->getFieldByCode('NoiLamViecMoi')->setRefIOID($nhanVien->Ref_MaPhongBan);
                    $this->_object->getFieldByCode('CongTyMoi')->setValue($sCongTy);
                    $this->_object->getFieldByCode('CongTyMoi')->setRefIOID($iCongTy);
                    $this->_object->getFieldByCode('PhongBanMoi')->setValue($sPhongBan);
                    $this->_object->getFieldByCode('PhongBanMoi')->setRefIOID($iPhongBan);
                    $this->_object->getFieldByCode('BoPhanMoi')->setValue($sBoPhan);
                    $this->_object->getFieldByCode('BoPhanMoi')->setRefIOID($iBoPhan);
                    $this->_object->getFieldByCode('NhomMoi')->setValue($sNhom);
                    $this->_object->getFieldByCode('NhomMoi')->setRefIOID($iNhom);
                }
            }
        }

    }
}