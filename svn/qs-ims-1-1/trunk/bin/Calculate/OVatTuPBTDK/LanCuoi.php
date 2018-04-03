<?php
class Qss_Bin_Calculate_OVatTuPBTDK_LanCuoi extends Qss_Lib_Calculate
{
    protected static $_arrLanCuoi = array();

    public function __doExecute()
    {
        if(!count(self::$_arrLanCuoi) && (int)$this->_object->i_IOID)
        {
            $sql = sprintf('
                SELECT
                    VatTuPhieuHienTai.*,
                    VatTuPhieuKhac.NgayCuoi AS NgayCuoiThayVatTu,
                    VatTuPhieuKhac.SoLuong AS SoLuongThayTheCuoiCung
                FROM
                (
                    SELECT
                        OVatTuPBT.*
                        , IF( IFNULL(OVatTuPBT.Ngay, \'\') != \'\', OVatTuPBT.Ngay, OPhieuBaoTri.NgayBatDau) AS NgayCuoi
                        , OPhieuBaoTri.IOID AS IOID_M759
                    FROM OVatTuPBT
                    INNER JOIN OPhieuBaoTri ON OPhieuBaoTri.IFID_M759 = OVatTuPBT.IFID_M759
                    WHERE OPhieuBaoTri.IFID_M759 = %1$d
                ) AS VatTuPhieuHienTai
                INNER JOIN
                (
                    SELECT *
                    FROM
                    (
                        SELECT
                            OVatTuPBT.*
                            , IF( IFNULL(OVatTuPBT.Ngay, \'\') != \'\', OVatTuPBT.Ngay, OPhieuBaoTri.NgayBatDau) AS NgayCuoi
                            , OPhieuBaoTri.IOID AS IOID_M759
                        FROM OPhieuBaoTri
                        INNER JOIN OVatTuPBT ON OPhieuBaoTri.IFID_M759 = OVatTuPBT.IFID_M759
                        WHERE
                            OPhieuBaoTri.IFID_M759 != %1$d
                            AND IFNULL(OVatTuPBT.HinhThuc, 0) IN (0, 1)
                        ORDER BY IFNULL(OVatTuPBT.Ref_ViTri, 0)
                            , IFNULL(OVatTuPBT.Ref_MaVatTu, 0)
                            , IF( IFNULL(OVatTuPBT.Ngay, \'\') != \'\', OVatTuPBT.Ngay, OPhieuBaoTri.NgayBatDau) DESC
                    ) AS T
                    GROUP BY IFNULL(Ref_ViTri, 0), IFNULL(Ref_MaVatTu, 0)

                ) AS VatTuPhieuKhac ON
                    IFNULL(VatTuPhieuHienTai.Ref_ViTri, 0) = IFNULL(VatTuPhieuKhac.Ref_ViTri, 0)
                    AND IFNULL(VatTuPhieuHienTai.Ref_MaVatTu, 0) = IFNULL(VatTuPhieuKhac.Ref_MaVatTu, 0)
                WHERE IFNULL(VatTuPhieuHienTai.HinhThuc, 0) IN (0, 1)
            ', (int)$this->_object->i_IFID);

            // echo '<pre>'; print_r($sql); die;

            $dataSql = $this->_db->fetchAll($sql);

            // echo '<pre>'; print_r($dataSql); die;

            foreach($dataSql as $item)
            {
                $compare = Qss_Lib_Date::compareTwoDate($item->NgayCuoiThayVatTu, date('Y-m-d'));
                $diff    = Qss_Lib_Date::diffTime($item->NgayCuoiThayVatTu, date('Y-m-d'), 'D');
                $class   = '';

                if($compare == -1)
                {
                    if($diff > 15)
                    {
                        $class = 'bgpink';
                    }
                    elseif($diff > 7)
                    {
                        $class = 'bgyellow';
                    }
                }

                $return  = '<span class="left '.$class.'" style="" title="'.$item->SoLuongThayTheCuoiCung.' '.$item->DonViTinh.'">';
                $return .= Qss_Lib_Date::mysqltodisplay($item->NgayCuoiThayVatTu);
                $return .= '</span>';

                self::$_arrLanCuoi[$item->IOID] = $return;
            }
        }

        if(!(int)$this->_object->i_IOID)
        {
            $return    = '';
            $ngay      = $this->_object->getFieldByCode('Ngay')->getValue();
            $iMaVatTu  = (int)$this->_object->getFieldByCode('MaVatTu')->intRefIOID;
            $iViTri    = (int)$this->_object->getFieldByCode('ViTri')->intRefIOID;
            $phieuSql  = sprintf('SELECT * FROM OPhieuBaoTri WHERE IFID_M759 = %1$d', (int)$this->_object->i_IFID);
            $phieu     = $this->_db->fetchOne($phieuSql);

            if(!$iMaVatTu)
            {
                $line     = $this->_db->fetchOne(sprintf('SELECT * FROM OVatTuPBT WHERE IOID = %1$d', $this->_object->i_IOID));
                $ngay     = $line?$line->Ngay:'';
                $iMaVatTu = (int)($line?$line->Ref_MaVatTu:0);
                $iViTri   = (int)($line?$line->Ref_ViTri:0);
            }

            // Neu khong co ngay tren dong vat tu lay ngay tren phieu bao tri
            if(!$ngay && $phieu)
            {
                $ngay = $phieu->NgayBatDau;
            }

            // Chi lay lan cuoi neu co ma vat tu va ngay
            if($phieu && $ngay && $iMaVatTu)
            {
                $sql = sprintf('
                SELECT
                    OVatTuPBT.*
                    , IF( IFNULL(OVatTuPBT.Ngay, \'\') != \'\', OVatTuPBT.Ngay, OPhieuBaoTri.NgayBatDau) AS NgayCuoi
                FROM OPhieuBaoTri
                INNER JOIN OVatTuPBT ON OPhieuBaoTri.IFID_M759 = OVatTuPBT.IFID_M759
                WHERE
                    OPhieuBaoTri.IFID_M759 != %1$d
                    AND IF( IFNULL(OVatTuPBT.Ngay, \'\') != \'\', OVatTuPBT.Ngay, OPhieuBaoTri.NgayBatDau) <= %2$s
                    AND OPhieuBaoTri.IOID < %3$d
                    AND IFNULL(OVatTuPBT.Ref_MaVatTu, 0) = %4$d
                    AND IFNULL(OVatTuPBT.HinhThuc, 0) IN (0, 1)
                    AND IFNULL(OVatTuPBT.Ref_ViTri, 0) = %5$d
                ORDER BY IF( IFNULL(OVatTuPBT.Ngay, \'\') != \'\', OVatTuPBT.Ngay, OPhieuBaoTri.NgayBatDau) DESC
                    , OPhieuBaoTri.IOID DESC
                LIMIT 1'
                    , (int)$this->_object->i_IFID
                    , $this->_db->quote(Qss_Lib_Date::displaytomysql($ngay))
                    , $phieu->IOID
                    , $iMaVatTu
                    , $iViTri);

                //echo '<pre>'; print_r($sql); die;

                $dataSql = $this->_db->fetchOne($sql);


                if($dataSql)
                {
                    $compare = Qss_Lib_Date::compareTwoDate($dataSql->NgayCuoi, date('Y-m-d'));
                    $diff    = Qss_Lib_Date::diffTime($dataSql->NgayCuoi, date('Y-m-d'), 'D');
                    $class   = '';

                    if($compare == -1)
                    {
                        if($diff > 15)
                        {
                            $class = 'bgpink';
                        }
                        elseif($diff > 7)
                        {
                            $class = 'bgyellow';
                        }
                    }

                    $return  = '<span class="left '.$class.'" style="" title="'.$dataSql->SoLuong.' '.$dataSql->DonViTinh.'">';
                    $return .= Qss_Lib_Date::mysqltodisplay($dataSql->NgayCuoi);
                    $return .= '</span>';
                }
            }

            return $return;
        }
        else
        {
            if(isset(self::$_arrLanCuoi[$this->_object->i_IOID]))
            {
                return self::$_arrLanCuoi[$this->_object->i_IOID];
            }

        }
    }
}