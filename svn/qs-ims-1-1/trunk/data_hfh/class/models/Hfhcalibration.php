<?php
class Qss_Model_Hfhcalibration extends Qss_Model_Abstract
{
    public function __construct()
    {
        parent::__construct();
    }
    public function getCalibrations(
        $mStart
        , $mEnd
        , $locationIOID = 0
        , $equipTypeIOID = 0
        , $equipGroupIOID = 0
        , $equipIOID = 0
    )
    {
        $filter = '';
        $filter.= $equipGroupIOID?sprintf(' AND ThietBi.Ref_NhomThietBi = %1$d ', $equipGroupIOID):'';
        $filter.= $equipIOID?sprintf(' AND ThietBi.IOID = %1$d ', $equipIOID):'';

        if(Qss_Lib_System::fieldActive('OKhuVuc', 'TrucThuoc'))
        {
            $loc    = ($locationIOID != 0)?$this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locationIOID)):array();
            $filter .= count((array)$loc)?sprintf('and IFNULL(ThietBi.Ref_MaKhuVuc, 0) in (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d)', $loc->lft, $loc->rgt):'';
        }
        else
        {
            $filter .= ($locationIOID != 0)?sprintf(' and IFNULL(ThietBi.Ref_MaKhuVuc, 0) = %1$d ', $locationIOID):'';
        }

        if(Qss_Lib_System::fieldActive('OLoaiThietBi', 'TrucThuoc'))
        {
            $eqType = ($equipTypeIOID != 0)?$this->_o_DB->fetchOne(sprintf('SELECT * FROM OLoaiThietBi WHERE IOID = %1$d', $equipTypeIOID)):array();
            $filter .= count((array)$eqType)?sprintf('and (IFNULL(ThietBi.Ref_LoaiThietBi, 0) IN  (select IOID from OLoaiThietBi where lft>= %1$d and  rgt <= %2$d)) ',$eqType->lft, $eqType->rgt):'';
        }
        else
        {
            $filter .= ($equipTypeIOID != 0)?sprintf(' and IFNULL(ThietBi.Ref_LoaiThietBi, 0) = %1$d ', $equipTypeIOID):'';
        }

        $sql = sprintf('
            SELECT 
                HieuChuan.*
                , ThietBi.KiemDinh
                , ThietBi.NgayDuaVaoSuDung
                , ThietBi.IOID AS Ref_MaThietBi
                , ThietBi.MaThietBi
                , ThietBi.TenThietBi
                , KhuVuc.MaKhuVuc
                , KhuVuc.Ten AS TenKhuVuc
                , DoiTac.MaDoiTac
                , DoiTac.TenDoiTac
            FROM      ODanhSachThietBi   AS ThietBi
            LEFT JOIN OHieuChuanKiemDinh AS HieuChuan ON ThietBi.IOID                    = HieuChuan.Ref_MaThietBi 
            LEFT JOIN OKhuVuc            AS KhuVuc    ON IFNULL(ThietBi.Ref_MaKhuVuc, 0) =  KhuVuc.IOID
            LEFT JOIN ODoiTac            AS DoiTac    ON IFNULL(HieuChuan.Ref_DonVi, 0)  = DoiTac.IOID
            WHERE 
                IF(
                  IFNULL(HieuChuan.IOID, 0) = 0
                  , IFNULL(ThietBi.KiemDinh,0) = 1 AND IFNULL(ThietBi.NgayDuaVaoSuDung, "") != "" 
                  , (
                      (
                          IFNULL(ThietBi.KiemDinh,0) = 1
                          AND HieuChuan.NgayKiemDinhTiepTheo >= %1$s
                          AND HieuChuan.Ngay < %1$s
                      )
                     OR   
                     (
                        (IFNULL(ThietBi.KiemDinh,0) = 0)
                        AND HieuChuan.Ngay > %1$s
                        AND HieuChuan.Ngay IN
                        (
                            SELECT MAX(HieuChuan.Ngay) FROM OHieuChuanKiemDinh AS HieuChuan
                            INNER JOIN ODanhSachThietBi AS ThietBi ON HieuChuan.Ref_MaThietBi = ThietBi.IOID
                            WHERE YEAR(HieuChuan.Ngay) = YEAR(%1$s) AND (IFNULL(ThietBi.KiemDinh,0) = 0)
                            GROUP BY HieuChuan.MaThietBi
                        )
                      )
                   )
                )
                %3$s
                ORDER BY ThietBi.LoaiThietBi, HieuChuan.TenThietBi, HieuChuan.Ngay            
            ', $this->_o_DB->quote($mStart)
            , $this->_o_DB->quote($mEnd)
            , $filter
        );
        // echo '<pre>'; print_r($sql); die;

        $dat = $this->_o_DB->fetchAll($sql);

        // echo '<pre>'; print_r($dat); die;

        foreach ($dat as $key=>$item)
        {
            if((int)$item->IOID == 0 && (int)$item->KiemDinh == 1 && $item->NgayDuaVaoSuDung)
            {
                $matches = array();
                preg_match('/[0-9]{4}/', $item->NgayDuaVaoSuDung, $matches);

//                echo '<pre>FIND: '; print_r((int)$matches[0]);
//                echo '<pre>START: '; print_r((int)date('Y', strtotime($mStart)));
//
//                2015
//                2017
                if(
                    (!isset($matches[0]) || !$matches[0])
                    ||
                    (isset($matches[0]) && $matches[0] && ((int)$matches[0]  > (int)date('Y', strtotime($mStart))))
                )
                {
//                    echo '<pre>OK: y';
                    unset($dat[$key]);
                }
//                else
//                {
//                    echo '<pre>OK: n';
//                }
            }
        }
//        die;
        //echo '<pre>'; print_r($matches); die;

        return $dat;
    }
}