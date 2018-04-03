<?php

class Qss_Model_Warehouse_Main extends Qss_Model_Abstract
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getOutputNoByConfig()
    {
        $object = new Qss_Model_Object();
        $object->v_fInit('OXuatKho', 'M506');
        $document = new Qss_Model_Extra_Document($object);
        return $document->getDocumentNo();
    }

    public function getInputTypeByCode($type)
    {
        $sql = sprintf('
            SELECT *
            FROM OLoaiXuatKho
            WHERE Loai = %1$s'
            ,$this->_o_DB->quote($type)
        );
        return $this->_o_DB->fetchOne($sql);
    }

    public function getStockByUser($uid)
    {
        $sql = sprintf('
            SELECT DonVi.KhoVatTu, DonVi.Ref_KhoVatTu
            FROM ODanhSachNhanVien AS NhanVienDanhSach
            INNER JOIN ONhanVien AS NhanVienDonVi ON NhanVienDanhSach.IOID = NhanVienDonVi.Ref_MaNV 
            INNER JOIN ODonViSanXuat AS DonVi ON NhanVienDonVi.IFID_M125 = DonVi.IFID_M125
            WHERE IFNULL(NhanVienDanhSach.Ref_TenTruyCap, 0) = %1$d
            LIMIT 1
        ', $uid);
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchOne($sql);
    }

    public function getAll($user)
    {
        $where = '';
        if(Qss_Lib_System::formSecure('M601'))
        {
            $where = sprintf(' AND v.IFID_M601 in (SELECT IFID FROM qsrecordrights WHERE UID = %1$d and FormCode="M601")',$user->user_id);
        }

        $sql = sprintf('SELECT * FROM ODanhSachKho AS v WHERE 1=1 %1$s', $where);
        return $this->_o_DB->fetchAll($sql);
    }
}