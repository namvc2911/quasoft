<?php

class Qss_Model_Master_Partner extends Qss_Model_Abstract
{
    public function __construct ()
    {
        parent::__construct();
    }

    public function getCustomers()
    {
        $sql = sprintf('SELECT * FROM ODoiTac WHERE ifnull(KhachHang, 0) = 1 ORDER BY TenDoiTac');
        return $this->_o_DB->fetchAll($sql);
    }
    
    public function getSuppliers()
    {
        $sql = sprintf('SELECT * FROM ODoiTac WHERE ifnull(NhaCungCap, 0) = 1 ORDER BY TenDoiTac');
        return $this->_o_DB->fetchAll($sql);
    }

    public function getPartners()
    {
        $sql = sprintf('SELECT * FROM ODoiTac ORDER BY TenDoiTac');
        return $this->_o_DB->fetchAll($sql);
    }

    public function getPartnerByIOID($ioid)
    {
        $sql = sprintf('SELECT * FROM ODoiTac WHERE IOID = %1$d', $ioid);
        return $this->_o_DB->fetchOne($sql);
    }

    public function countContactsOfPartners($partnerIOID = 0, $nhaCungCapDichVu = false, $nhaCungCapThietBi = false)
    {
        $ret   = array();
        $where = $partnerIOID?sprintf(' WHERE ODoiTac.IOID = %1$d ', $partnerIOID):'';

        if(Qss_Lib_System::fieldActive('ODoiTac', 'NhaCungCapDichVu'))
        {
            $where.= $nhaCungCapDichVu?sprintf('AND IFNULL(ODoiTac.NhaCungCapDichVu, 0) = 1'):'';
        }

        if(Qss_Lib_System::fieldActive('ODoiTac', 'NhaCungCapThietBi'))
        {
            $where.= $nhaCungCapThietBi?sprintf('AND IFNULL(ODoiTac.NhaCungCapThietBi, 0) = 1'):'';
        }

        $sql = sprintf('
            SELECT  ODoiTac.IFID_M118, Count(1) AS `Count`
            FROM ODoiTac 
            INNER JOIN OLienHeCaNhan ON ODoiTac.IFID_M118 = OLienHeCaNhan.IFID_M118
            %1$s
            GROUP BY ODoiTac.IFID_M118
        ', $where);
        // echo '<pre>'; print_r($sql); die;
        $data = $this->_o_DB->fetchAll($sql);

        foreach($data as $item)
        {
            $ret[$item->IFID_M118] = $item->Count;
        }

        return $ret;
    }

    public function getContactsOfPartners($partnerIOID = 0, $nhaCungCapDichVu = false, $nhaCungCapThietBi = false)
    {
        $where = $partnerIOID?sprintf(' WHERE ODoiTac.IOID = %1$d ', $partnerIOID):'';

        if(Qss_Lib_System::fieldActive('ODoiTac', 'NhaCungCapDichVu'))
        {
            $where.= $nhaCungCapDichVu?sprintf('AND IFNULL(ODoiTac.NhaCungCapDichVu, 0) = 1'):'';
        }

        if(Qss_Lib_System::fieldActive('ODoiTac', 'NhaCungCapThietBi'))
        {
            $where.= $nhaCungCapThietBi?sprintf('AND IFNULL(ODoiTac.NhaCungCapThietBi, 0) = 1'):'';
        }

        $sql = sprintf('
            SELECT ODoiTac.*,  OLienHeCaNhan.*
                , ODoiTac.DiaChi AS DiaChiDoiTac
                , ODoiTac.DienThoai AS DienThoaiDoiTac
                , OLienHeCaNhan.DienThoaiDiDong AS DienThoaiDiDongLienHe
                , OLienHeCaNhan.Email AS EmailLienHe
            FROM ODoiTac 
            LEFT JOIN OLienHeCaNhan ON ODoiTac.IFID_M118 = OLienHeCaNhan.IFID_M118
            %1$s
            ORDER BY ODoiTac.TenDoiTac, OLienHeCaNhan.HoTen
        ', $where);
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }
    
    public function getContactByIOID($contacIOID)
    {
        $sql = sprintf('
            SELECT *
            FROM OLienHeCaNhan 
            WHERE IOID =  %1$d
        ', $contacIOID);
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchOne($sql);
    }
}