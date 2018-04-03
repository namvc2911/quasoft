<?php

class Qss_Bin_Onload_OVatTu extends Qss_Lib_Onload
{

	public function __doExecute()
	{
		parent::__doExecute();
	}

    public function ViTri()
    {
    	$this->_doFilter($this->_object->getFieldByCode('ViTri'));
    	//Filter theo bộ phận đối tượng chính bỏ vì đã filter theo công việc
    	/*$sql     = sprintf('
            select OCauTrucThietBi.lft, OCauTrucThietBi.rgt, ODanhSachThietBi.IFID_M705 AS IFID
            from OBaoTriDinhKy 
            inner join ODanhSachThietBi ON OBaoTriDinhKy.Ref_MaThietBi = ODanhSachThietBi.IOID
            inner join OCauTrucThietBi ON ODanhSachThietBi.IFID_M705 = OCauTrucThietBi.IFID_M705
                and ifnull(OBaoTriDinhKy.Ref_BoPhan, 0) = OCauTrucThietBi.IOID
            WHERE OBaoTriDinhKy.IFID_M724 = %1$d
        ', @(int)$this->_object->i_IFID);
		$dataSQL = $this->_db->fetchOne($sql);    
        if($dataSQL)
        {
            $this->_object->getFieldByCode('ViTri')->arrFilters[] = sprintf(' v.IOID in (select IOID from OCauTrucThietBi where IFID_M705 = %1$d and lft >= %2$d and rgt <= %3$d )', $dataSQL->IFID, $dataSQL->lft, $dataSQL->rgt);
        }  */
    	$congviec = @(int)$this->_object->getFieldByCode('CongViec')->getRefIOID(); 
    	$sql = sprintf('select ct.lft,ct.rgt
                    FROM OCongViecBT as t
                    inner join OCauTrucThietBi as ct on ct.IOID = t.Ref_ViTri
                    WHERE t.IFID_M724 = %1$d
                    AND t.IOID = %2$d
            ', @(int)$this->_object->i_IFID
            , $congviec);
        $dataSQL = $this->_db->fetchOne($sql);
        if($congviec)
        {
        	if($dataSQL)
        	{
	        	$this->_object->getFieldByCode('ViTri')->arrFilters[] =
	            sprintf('(v.lft >= %1$d and v.rgt <=  %2$d )
			            ', $dataSQL->lft
			            , $dataSQL->rgt);
        	}
        }
        else 
        {
        	$this->_object->getFieldByCode('ViTri')->arrFilters[] = '1=0';
        }
    }
	public function MaVatTu()
    {
       	//Ngoài thay thế vật tư còn lại phai chọn trong cấu trúc và phụ tùng thay thế
        $hinhThuc = (int)$this->_object->getFieldByCode('HinhThuc')->getValue();
        $viTri    = (int)$this->_object->getFieldByCode('ViTri')->getRefIOID();

        if($hinhThuc != 0 )
        {
            //Lay phu tung tu cau truc va phu tung thay the
            $refItemSql = sprintf('
                    SELECT CauTruc.Ref_MaSP
                    FROM OBaoTriDinhKy AS Phieu
                    INNER JOIN ODanhSachThietBi AS ThietBi ON Phieu.Ref_MaThietBi = ThietBi.IOID
                    INNER JOIN OCauTrucThietBi AS CauTruc ON ThietBi.IFID_M705 = CauTruc.IFID_M705
                    WHERE Phieu.IFID_M724 = %1$d
                        AND IFNULL(CauTruc.Ref_MaSP, 0) != 0
                        AND IFNULL(CauTruc.PhuTung, 0) = 1
                        AND IFNULL(CauTruc.IOID, 0) = %2$d

                    UNION

                    SELECT CauTruc.Ref_MaSP
                    FROM OBaoTriDinhKy AS Phieu
                    INNER JOIN ODanhSachThietBi AS ThietBi ON Phieu.Ref_MaThietBi = ThietBi.IOID
                    INNER JOIN ODanhSachPhuTung AS CauTruc ON ThietBi.IFID_M705 = CauTruc.IFID_M705
                    WHERE Phieu.IFID_M724 = %1$d
                        AND IFNULL(CauTruc.Ref_MaSP, 0) != 0
                        AND IFNULL(CauTruc.Ref_ViTri, 0) = %2$d
            ', (int)$this->_object->i_IFID, $viTri);
            $refItem = $this->_db->fetchAll($refItemSql);

            //Lay vi tri hien tai dang chon
            $comSql = sprintf('
                SELECT IFNULL(CauTruc.PhuTung, 0) AS PhuTung
                FROM OBaoTriDinhKy AS Phieu
                INNER JOIN ODanhSachThietBi AS ThietBi ON Phieu.Ref_MaThietBi = ThietBi.IOID
                INNER JOIN OCauTrucThietBi AS CauTruc ON ThietBi.IFID_M705 = CauTruc.IFID_M705
                WHERE Phieu.IFID_M724 = %1$d AND IFNULL(CauTruc.IOID, 0) = %2$d
            ', (int)$this->_object->i_IFID, $viTri);
            $com = $this->_db->fetchOne($comSql);
             //Neu không phai phu tung thi khong hien ra gi
             //Neu la phu tung tung thi phai xet xem vi tri da co mat hang nao chua
             //Neu chua co ca trong cau truc lan phu tung thay the thi cho chon tat
             //Con mat hang co o mot trong hai thi chon theo cau truc va phu tung thay the
            if($com && $com->PhuTung == 1)
            {
                if($refItem)
                {
                    $this->_object->getFieldByCode('MaVatTu')->arrFilters[] =
                        sprintf('
                (
                    v.IOID in (
                        SELECT CauTruc.Ref_MaSP
                        FROM OBaoTriDinhKy AS Phieu
                        INNER JOIN ODanhSachThietBi AS ThietBi ON Phieu.Ref_MaThietBi = ThietBi.IOID
                        INNER JOIN OCauTrucThietBi AS CauTruc ON ThietBi.IFID_M705 = CauTruc.IFID_M705
                        WHERE Phieu.IFID_M724 = %1$d
                            AND IFNULL(CauTruc.Ref_MaSP, 0) != 0
                            AND IFNULL(CauTruc.PhuTung, 0) = 1
                            AND IFNULL(CauTruc.IOID, 0) = %2$d

                        UNION

                        SELECT CauTruc.Ref_MaSP
                        FROM OBaoTriDinhKy AS Phieu
                        INNER JOIN ODanhSachThietBi AS ThietBi ON Phieu.Ref_MaThietBi = ThietBi.IOID
                        INNER JOIN ODanhSachPhuTung AS CauTruc ON ThietBi.IFID_M705 = CauTruc.IFID_M705
                        WHERE Phieu.IFID_M724 = %1$d
                            AND IFNULL(CauTruc.Ref_MaSP, 0) != 0
                            AND IFNULL(CauTruc.Ref_ViTri, 0) = %2$d
                    )
                )
            ', (int)$this->_object->i_IFID, $viTri);
                }
            }
            else
            {
                $this->_object->getFieldByCode('MaVatTu')->arrFilters[] = sprintf(' 1=0 ');
            }
             //echo '<pre>'; print_r($this->_object->getFieldByCode('MaVatTu')->arrFilters); die;
        }
    }
}
