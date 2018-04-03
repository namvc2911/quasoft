<?php
class Qss_Model_Maintenance_Operation extends Qss_Model_Abstract
{
    public function __construct()
    {
        parent::__construct();
    }

    public static function createInstance()
    {
        return new self();
    }

    public function countOperations($date, $shift = 0)
    {
        $where = '';
        $join  = '';
        $lang  = $this->_user->user_lang == 'vn'?'':'_'.$this->_user->user_lang;

        if($shift)
        {
            $oShift = $this->_o_DB->fetchOne(sprintf('SELECT * FROM OCa WHERE IOID = %1$d', $shift));
            $join  .= sprintf(' AND (nt.ThoiGianBatDau between %1$s AND %2$s )'
                , $this->_o_DB->quote($oShift->GioBatDau)
                , $this->_o_DB->quote($oShift->GioKetThuc));
        }

        $sql = sprintf('     
            SELECT count(1) AS Total
            FROM
            (
                SELECT
                    nt.IOID, DanhSach.IOID AS DanhSachIOID
                FROM OThaoTacVanHanh AS DanhSach
                INNER JOIN ODanhSachThietBi AS ThietBi ON DanhSach.IFID_M705 = ThietBi.IFID_M705
                INNER JOIN OKhuVuc AS khuvuc ON ifnull(ThietBi.Ref_MaKhuVuc, 0) = khuvuc.IOID
                LEFT JOIN OCongViecNhanVien as nt on 
                    ThietBi.IOID = IFNULL(nt.Ref_MaThietBi, 0)
                    AND DanhSach.CongViec = nt.CongViec
                    AND nt.Ngay = %3$s %1$s
                LEFT JOIN qsiforms as NhatTrinhIForm ON nt.IFID_M840 = NhatTrinhIForm.IFID
                LEFT JOIN qsworkflows AS qsw ON qsw.FormCode = NhatTrinhIForm.FormCode
                LEFT JOIN qsworkflowsteps AS qsws ON qsw.WFID = qsws.WFID AND NhatTrinhIForm.Status = qsws.StepNo                                
                WHERE
                    (
                        IFNULL(ThietBi.Ref_MaKhuVuc, 0) in (
                            SELECT IOID FROM OKhuVuc
                            inner join qsrecordrights on OKhuVuc.IFID_M720 = qsrecordrights.IFID
                            WHERE UID = %3$d)
                    )
                    AND ThietBi.DeptID in (%4$s)          
                
                UNION
                        
                SELECT
                    nt.IOID, DanhSach.IOID AS DanhSachIOID
                FROM OCongViecNhanVien AS nt
                LEFT JOIN ODanhSachThietBi AS ThietBi ON nt.Ref_MaThietBi = ThietBi.IOID
                    AND (
                        IFNULL(ThietBi.Ref_MaKhuVuc, 0) in (
                            SELECT IOID FROM OKhuVuc
                            inner join qsrecordrights on OKhuVuc.IFID_M720 = qsrecordrights.IFID
                            WHERE UID = %3$d)
                    )
                    AND ThietBi.DeptID in (%4$s)
                LEFT JOIN OKhuVuc AS khuvuc ON ifnull(ThietBi.Ref_MaKhuVuc, 0) = khuvuc.IOID
                LEFT JOIN OThaoTacVanHanh AS DanhSach ON DanhSach.IFID_M705 = ThietBi.IFID_M705
                    AND DanhSach.CongViec = nt.CongViec
                     %1$s
                LEFT JOIN qsiforms as NhatTrinhIForm ON nt.IFID_M840 = NhatTrinhIForm.IFID
                LEFT JOIN qsworkflows AS qsw ON qsw.FormCode = NhatTrinhIForm.FormCode
                LEFT JOIN qsworkflowsteps AS qsws ON qsw.WFID = qsws.WFID AND NhatTrinhIForm.Status = qsws.StepNo     
                WHERE IFNULL(DanhSach.IOID, 0) = 0   AND nt.Ngay = %2$s         
            ) AS Table1          
        ', $join, $this->_o_DB->quote($date), $this->_user->user_id, $this->_user->user_dept_list);
        //echo '<Pre>'; print_r($sql); die;
        $dat = $this->_o_DB->fetchOne($sql);

        return $dat?$dat->Total:0;
    }

    public function getOperations($date, $shift = 0, $page = 0, $perpage = 0)
    {
        $where = '';
        $join  = '';
        $lang  = $this->_user->user_lang == 'vn'?'':'_'.$this->_user->user_lang;

        if($shift)
        {
            $oShift = $this->_o_DB->fetchOne(sprintf('SELECT * FROM OCa WHERE IOID = %1$d', $shift));
            $join  .= sprintf(' AND (nt.ThoiGianBatDau between %1$s AND %2$s )'
                , $this->_o_DB->quote($oShift->GioBatDau)
                , $this->_o_DB->quote($oShift->GioKetThuc));
        }

        $sPage   = ($page && $perpage)?($page - 1)*$perpage:0;
        $limit   = ($page && $perpage)?sprintf(' LIMIT %1$d, %2$d', $sPage, $perpage):'';

        $sql = sprintf('     
            SELECT *
            FROM
            (
                SELECT
                    nt.*
                    , DanhSach.CongViec AS CaiDatCongViec
                    , DanhSach.ThoiGianBatDau AS CaiDatThoiGianBatDau
                    , DanhSach.ThoiGianKetThuc AS CaiDatThoiGianKetThuc
                    , DanhSach.IOID AS ThaoTacIOID
                    , from_unixtime(NhatTrinhIForm.SDate, "%%d-%%m-%%Y") AS NgayNhap
                    , from_unixtime(NhatTrinhIForm.SDate, "%%H:%%i") AS GioNhap
                    , nt.IOID AS NhatTrinhIOID
                    , nt.IFID_M840 AS NhatTrinhIFID
                    , NhatTrinhIForm.Status  AS Status 
                    , qsws.Name%2$s  AS StatusName
                    , qsws.Color 
                    , qsusers.UserName
                FROM OThaoTacVanHanh AS DanhSach
                INNER JOIN ODanhSachThietBi AS ThietBi ON DanhSach.IFID_M705 = ThietBi.IFID_M705
                INNER JOIN OKhuVuc AS khuvuc ON ifnull(ThietBi.Ref_MaKhuVuc, 0) = khuvuc.IOID
                LEFT JOIN OCongViecNhanVien as nt on 
                    ThietBi.IOID = IFNULL(nt.Ref_MaThietBi, 0)
                    AND DanhSach.CongViec = nt.CongViec
                    AND nt.Ngay = %3$s %1$s
                LEFT JOIN qsiforms as NhatTrinhIForm ON nt.IFID_M840 = NhatTrinhIForm.IFID
                LEFT JOIN qsusers ON NhatTrinhIForm.UID = qsusers.UID
                LEFT JOIN qsworkflows AS qsw ON qsw.FormCode = NhatTrinhIForm.FormCode
                LEFT JOIN qsworkflowsteps AS qsws ON qsw.WFID = qsws.WFID AND NhatTrinhIForm.Status = qsws.StepNo                                
                WHERE
                    (
                        IFNULL(ThietBi.Ref_MaKhuVuc, 0) in (
                            SELECT IOID FROM OKhuVuc
                            inner join qsrecordrights on OKhuVuc.IFID_M720 = qsrecordrights.IFID
                            WHERE UID = %4$d)
                    )
                    AND ThietBi.DeptID in (%5$s)          
                
                UNION
                        
                SELECT
                    nt.*
                    , DanhSach.CongViec AS CaiDatCongViec
                    , DanhSach.ThoiGianBatDau AS CaiDatThoiGianBatDau
                    , DanhSach.ThoiGianKetThuc AS CaiDatThoiGianKetThuc
                    , DanhSach.IOID AS ThaoTacIOID
                    , from_unixtime(NhatTrinhIForm.SDate, "%%d-%%m-%%Y") AS NgayNhap
                    , from_unixtime(NhatTrinhIForm.SDate, "%%H:%%i") AS GioNhap                    
                    , nt.IOID AS NhatTrinhIOID
                    , nt.IFID_M840 AS NhatTrinhIFID
                    , NhatTrinhIForm.Status  AS Status 
                    , qsws.Name%2$s  AS StatusName
                    , qsws.Color 
                    , qsusers.UserName                     
                FROM OCongViecNhanVien AS nt
                LEFT JOIN ODanhSachThietBi AS ThietBi ON nt.Ref_MaThietBi = ThietBi.IOID
                    AND (
                        IFNULL(ThietBi.Ref_MaKhuVuc, 0) in (
                            SELECT IOID FROM OKhuVuc
                            inner join qsrecordrights on OKhuVuc.IFID_M720 = qsrecordrights.IFID
                            WHERE UID = %4$d)
                    )
                    AND ThietBi.DeptID in (%5$s)
                LEFT JOIN OKhuVuc AS khuvuc ON ifnull(ThietBi.Ref_MaKhuVuc, 0) = khuvuc.IOID
                LEFT JOIN OThaoTacVanHanh AS DanhSach ON DanhSach.IFID_M705 = ThietBi.IFID_M705
                    AND DanhSach.CongViec = nt.CongViec
                     %1$s
                LEFT JOIN qsiforms as NhatTrinhIForm ON nt.IFID_M840 = NhatTrinhIForm.IFID
                LEFT JOIN qsusers ON NhatTrinhIForm.UID = qsusers.UID
                LEFT JOIN qsworkflows AS qsw ON qsw.FormCode = NhatTrinhIForm.FormCode
                LEFT JOIN qsworkflowsteps AS qsws ON qsw.WFID = qsws.WFID AND NhatTrinhIForm.Status = qsws.StepNo     
                WHERE IFNULL(DanhSach.IOID, 0) = 0   AND nt.Ngay = %3$s         
            ) AS Table1          
            ORDER BY IFNULL(NhatTrinhIOID, 0) DESC
            %6$s
        ', $join, $lang, $this->_o_DB->quote($date), $this->_user->user_id, $this->_user->user_dept_list, $limit);

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getOperationByIOID($ioid)
    {
        $sql = sprintf('
            SELECT
                DiemDo.*
                , ThietBi.MaThietBi
                , ThietBi.TenThietBi
            FROM OThaoTacVanHanh AS DiemDo
            INNER JOIN ODanhSachThietBi AS ThietBi ON DiemDo.IFID_M705 = ThietBi.IFID_M705
            WHERE DiemDo.IOID = %1$d
        ', $ioid);

        // echo '<Pre>'; print_r($sql); die;
        return $this->_o_DB->fetchOne($sql);
    }

    public function getDailyRecordByIFID($ifid)
    {
        $sql = sprintf('
            SELECT qsws.*, pbt.*, iform.Status, qsusers.UserName, ThietBi.MaThietBi, ThietBi.TenThietBi
			FROM OCongViecNhanVien AS pbt
			INNER JOIN qsiforms AS iform ON pbt.IFID_M840 = iform.IFID
			INNER JOIN qsusers ON iform.UID = qsusers.UID
			INNER JOIN qsworkflows AS qsw ON qsw.FormCode = iform.FormCode
			INNER JOIN qsworkflowsteps AS qsws ON qsw.WFID = qsws.WFID AND iform.Status = qsws.StepNo
			LEFT JOIN ODanhSachThietBi As ThietBi On IFNULL(pbt.Ref_MaThietBi, 0) = ThietBi.IOID
			WHERE pbt.IFID_M840 = %1$d
		', $ifid);

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchOne($sql);
    }
}