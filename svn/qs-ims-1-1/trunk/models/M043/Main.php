<?php
class Qss_Model_M043_Main extends Qss_Model_Abstract
{
    /**
     * Lấy nhân viên theo user truyền vào, lưu ý chỉ lấy duy nhất một nhân viên cho 1 tài khoản
     */
    public function getEmployeeByUser($uid) {
        $sql = sprintf('
			SELECT NhanVien.*
			FROM qsusers AS users
			INNER JOIN ODanhSachNhanVien AS NhanVien ON users.UID = NhanVien.Ref_TenTruyCap
			WHERE users.UID = %1$d
		', $uid);
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchOne($sql);
    }

    /**
     * Lấy danh sách đăng ký nghỉ theo user
     */
    public function getDangKyNghiByUser($m316IOID, $year = '') {
        $sql = sprintf('
            SELECT ODangKyNghi.*, qsworkflowsteps.color, qsworkflowsteps.StepNo, qsworkflowsteps.Name
            FROM ODangKyNghi
            INNER JOIN qsiforms ON ODangKyNghi.IFID_M077 = qsiforms.IFID
            left join qsworkflows on qsworkflows.FormCode = qsiforms.FormCode and Actived=1
            left join qsworkflowsteps on qsworkflowsteps.StepNo = qsiforms.Status and qsworkflowsteps.WFID = qsworkflows.WFID        
        ');
        $sql.= sprintf(' WHERE IFNULL(ODangKyNghi.Ref_MaNhanVien, 0) =  %1$d   ', $m316IOID);
        $sql.= $year?sprintf(' AND YEAR(ODangKyNghi.NgayBatDau) =  %1$d   ', $year):'';
        $sql.= sprintf(' ORDER BY ODangKyNghi.NgayBatDau, ODangKyNghi.BatDauNghi');

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    /**
     * Lấy danh làm thêm theo user
     */
    public function getDangKyLamThemByUser($m316IOID, $year = '') {
        $sql = sprintf('
            SELECT ODangKyLamThem.*, qsworkflowsteps.color, qsworkflowsteps.StepNo, qsworkflowsteps.Name
            FROM ODangKyLamThem     
            INNER JOIN qsiforms ON ODangKyLamThem.IFID_M078 = qsiforms.IFID
            left join qsworkflows on qsworkflows.FormCode = qsiforms.FormCode and Actived=1
            left join qsworkflowsteps on qsworkflowsteps.StepNo = qsiforms.Status and qsworkflowsteps.WFID = qsworkflows.WFID
        ');

        $sql.= sprintf(' WHERE IFNULL(ODangKyLamThem.Ref_MaNhanVien, 0) = %1$d   ', $m316IOID);
        $sql.= $year?sprintf(' AND YEAR(ODangKyLamThem.NgayDangKy) =  %1$d   ', $year):'';
        $sql.= sprintf(' ORDER BY ODangKyLamThem.NgayDangKy, ODangKyLamThem.GioDangKy');

        return $this->_o_DB->fetchAll($sql);
    }

    /**
     * Lấy danh sách công ngày theo user
     */
    public function getCongNgayByUser($m316IOID, $kyCong = 0) {
        $sql = sprintf('
            SELECT ODuLieuChamCongHangNgay.*, qsworkflowsteps.color, qsworkflowsteps.StepNo, qsworkflowsteps.Name
            FROM ODuLieuChamCongHangNgay     
            INNER JOIN qsiforms ON ODuLieuChamCongHangNgay.IFID_M026 = qsiforms.IFID
            left join qsworkflows on qsworkflows.FormCode = qsiforms.FormCode and Actived=1
            left join qsworkflowsteps on qsworkflowsteps.StepNo = qsiforms.Status and qsworkflowsteps.WFID = qsworkflows.WFID
        ');

        $sql .= sprintf(' WHERE IFNULL(ODuLieuChamCongHangNgay.Ref_MaNhanVien, 0) = %1$d', $m316IOID);
        if($kyCong) {
            $sql .= sprintf(' AND ( ');
            $sql .= sprintf(' ODuLieuChamCongHangNgay.NgayVao >= (SELECT ThoiGianBatDau FROM OKyCong WHERE IOID = %1$d) ', $kyCong);
            $sql .= sprintf(' AND ODuLieuChamCongHangNgay.NgayVao <= (SELECT ThoiGianKetThuc FROM OKyCong WHERE IOID = %1$d) ', $kyCong);
            $sql .= sprintf(' ) ');
        }
        $sql .= sprintf(' ORDER BY ODuLieuChamCongHangNgay.NgayVao');

        return $this->_o_DB->fetchAll($sql);
    }

    /**
     * Lấy kỳ công mới nhất của nhân viên
     */
    public function getKyCongMoiNhatByUser($m316IOID) {
        $sql = sprintf('
            SELECT OKyCong.*
            FROM OQuanLyChamCong  
            INNER JOIN OKyCong ON IFNULL(OQuanLyChamCong.Ref_KyCong, 0) = OKyCong.IOID
            WHERE IFNULL(OQuanLyChamCong.Ref_MaNhanVien, 0) = %1$d
            ORDER BY OQuanLyChamCong.ThoiGianBatDau DESC, OQuanLyChamCong.IOID DESC
            LIMIT 1
        ', $m316IOID);

        return $this->_o_DB->fetchOne($sql);
    }

    /**
     * Lấy quỹ nghỉ số ngày được nghỉ của nhân viên, Số ngày được duyệt của nhân viên, số ngày đã nghỉ, số ngày còn lại
     */
    public function getNghiCuaNhanVien($m316IOID, $year) {
        $retval = array();

        $retval['SoNgayPhepNam']      = 0;
        $retval['SoNgayPhepOm']       = 0;
        $retval['SoNgayDuyetPhepNam'] = 0;
        $retval['SoNgayDuyetPhepOm']  = 0;

        $retval['SoGioPhepNam']      = 0;
        $retval['SoGioPhepOm']       = 0;
        $retval['SoGioDuyetPhepNam'] = 0;
        $retval['SoGioDuyetPhepOm']  = 0;

        $retval['SoGioPhepNamConLai'] = 0;
        $retval['SoGioPhepOmConLai']  = 0;

        // Lay tong so nghi tu quy nghi
        $sql = sprintf('
                SELECT
                    OQuyNghiNhanVien.Ref_MaNhanVien
                    , SUM(CASE WHEN LoaiNghi = 1 THEN IFNULL(SoNgayDuocNghi, 0) ELSE 0 END) AS PhepNam
                    , SUM(CASE WHEN LoaiNghi = 2 THEN IFNULL(SoNgayDuocNghi, 0) ELSE 0 END) AS PhepOm
                FROM OQuyNghiNhanVien
                WHERE Ref_MaNhanVien = %1$d AND Nam = %2$d
                GROUP BY LoaiNghi
        ', $m316IOID, $year);
        $datQuyNghi = $this->_o_DB->fetchOne($sql);

        if($datQuyNghi) {
            $datQuyNghi->PhepNam     = $datQuyNghi->PhepNam?$datQuyNghi->PhepNam:0;
            $datQuyNghi->PhepNam     = $datQuyNghi->PhepOm?$datQuyNghi->PhepOm:0;
            $retval['SoNgayPhepNam'] = $datQuyNghi->PhepNam;
            $retval['SoNgayPhepOm']  = $datQuyNghi->PhepOm;
            $retval['SoGioPhepNam']  = $datQuyNghi->PhepNam * 8;
            $retval['SoGioPhepOm']   = $datQuyNghi->PhepOm * 8;
        }

        $sql = sprintf('
            SELECT 
                SUM(CASE WHEN OPhanLoaiNghi.LoaiNghi = 1 THEN IFNULL(SoGioNghi, 0) ELSE 0 END) AS SoGioPhepNam
                , SUM(CASE WHEN OPhanLoaiNghi.LoaiNghi = 2 THEN IFNULL(SoGioNghi, 0) ELSE 0 END) AS SoGioPhepOm
                , SUM(CASE WHEN OPhanLoaiNghi.LoaiNghi = 1 
                    THEN (DATEDIFF(ODangKyNghi.NgayKetThuc, ODangKyNghi.NgayBatDau) + 1) ELSE 0 END) AS PhepNam
                , SUM(CASE WHEN OPhanLoaiNghi.LoaiNghi = 2 
                    THEN (DATEDIFF(ODangKyNghi.NgayKetThuc, ODangKyNghi.NgayBatDau) + 1) ELSE 0 END) AS PhepOm                              
            FROM ODangKyNghi
            INNER JOIN qsiforms ON ODangKyNghi.IFID_M077 = qsiforms.IFID
            LEFT JOIN OPhanLoaiNghi ON ODangKyNghi.Ref_LoaiNgayNghi = OPhanLoaiNghi.IOID
            WHERE ODangKyNghi.Ref_MaNhanVien = %1$d 
                AND YEAR(ODangKyNghi.NgayBatDau) = %2$d 
                AND qsiforms.Status = 2 -- da duyet
            GROUP BY OPhanLoaiNghi.LoaiNghi
        ', $m316IOID, $year);

        $datDangKyNghi = $this->_o_DB->fetchOne($sql);

        if($datDangKyNghi) {
            $datDangKyNghi->PhepNam          = $datDangKyNghi->PhepNam?$datDangKyNghi->PhepNam:0;
            $datDangKyNghi->PhepNam          = $datDangKyNghi->PhepOm?$datDangKyNghi->PhepOm:0;
            $datDangKyNghi->SoGioPhepNam     = $datDangKyNghi->SoGioPhepNam?$datDangKyNghi->SoGioPhepNam:0;
            $datDangKyNghi->SoGioPhepOm      = $datDangKyNghi->SoGioPhepOm?$datDangKyNghi->SoGioPhepOm:0;
            $retval['SoNgayDuyetPhepNam'] = $datDangKyNghi->PhepNam;
            $retval['SoNgayDuyetPhepOm']  = $datDangKyNghi->PhepOm;
            $retval['SoGioDuyetPhepNam']  = $datDangKyNghi->SoGioPhepNam;
            $retval['SoGioDuyetPhepOm']   = $datDangKyNghi->SoGioPhepOm;
        }

        $retval['SoGioPhepNamConLai'] = $retval['SoGioPhepNam'] - $retval['SoGioDuyetPhepNam'];
        $retval['SoGioPhepOmConLai']  = $retval['SoGioPhepOm'] - $retval['SoGioDuyetPhepOm'];

        // echo '<pre>'; print_r($retval); die;

        return $retval;
    }

    public function getNghiCuaNhanVien2($m316IOID, $year, $loaiNghiIOID = 0) {
        $retval = array();

        // Lay tong so nghi tu quy nghi
        $sql = sprintf('
                SELECT
                    OQuyNghiNhanVien.Ref_MaNhanVien
                    , OQuyNghiNhanVien.Ref_LoaiNghi
                    , OPhanLoaiNghi.MaLoaiNghi
                    , OPhanLoaiNghi.TenLoaiNghi
                    , SUM(IFNULL(OQuyNghiNhanVien.SoNgayDuocNghi, 0)) AS TongSo
                FROM OQuyNghiNhanVien
                INNER JOIN OPhanLoaiNghi ON OQuyNghiNhanVien.Ref_LoaiNghi = OPhanLoaiNghi.IOID
                WHERE OQuyNghiNhanVien.Ref_MaNhanVien = %1$d AND OQuyNghiNhanVien.Nam = %2$d
                
        ', $m316IOID, $year);
        $sql .= $loaiNghiIOID?sprintf(' AND OQuyNghiNhanVien.Ref_LoaiNghi = %1$d', $loaiNghiIOID):'';
        $sql .= sprintf(' GROUP BY OQuyNghiNhanVien.LoaiNghi');

        $datQuyNghi = $this->_o_DB->fetchAll($sql);

        foreach($datQuyNghi as $item) {
            $item->TongSo                                   = $item->TongSo?$item->TongSo:0;
            $retval[$item->Ref_LoaiNghi]['IOID']            = $item->Ref_LoaiNghi;
            $retval[$item->Ref_LoaiNghi]['MaLoaiNghi']      = $item->MaLoaiNghi;
            $retval[$item->Ref_LoaiNghi]['TenLoaiNghi']     = $item->TenLoaiNghi;
            $retval[$item->Ref_LoaiNghi]['QuyNghiTheoNgay'] = $item->TongSo;
            $retval[$item->Ref_LoaiNghi]['QuyNghiTheoGio']  = $item->TongSo * 8;
        }

        $sql = sprintf('
            SELECT 
                SUM(IFNULL(ODangKyNghi.SoGioNghi, 0)) AS TongSoTheoGio
                , ODangKyNghi.Ref_LoaiNgayNghi
                , OPhanLoaiNghi.MaLoaiNghi
                , OPhanLoaiNghi.TenLoaiNghi                                           
            FROM ODangKyNghi
            INNER JOIN qsiforms ON ODangKyNghi.IFID_M077 = qsiforms.IFID
            LEFT JOIN OPhanLoaiNghi ON ODangKyNghi.Ref_LoaiNgayNghi = OPhanLoaiNghi.IOID
            WHERE ODangKyNghi.Ref_MaNhanVien = %1$d 
                AND YEAR(ODangKyNghi.NgayBatDau) = %2$d 
                AND qsiforms.Status = 2 -- da duyet
        ', $m316IOID, $year);
        $sql .= $loaiNghiIOID?sprintf(' AND ODangKyNghi.Ref_LoaiNgayNghi = %1$d', $loaiNghiIOID):'';
        $sql .= sprintf(' GROUP BY ODangKyNghi.Ref_LoaiNgayNghi');

        $datDangKyNghi = $this->_o_DB->fetchAll($sql);

        foreach($datDangKyNghi as $item) {
            $item->TongSoTheoGio                          = $item->TongSoTheoGio?$item->TongSoTheoGio:0;
            $retval[$item->Ref_LoaiNghi]['IOID']          = $item->Ref_LoaiNgayNghi;
            $retval[$item->Ref_LoaiNghi]['MaLoaiNghi']    = $item->MaLoaiNghi;
            $retval[$item->Ref_LoaiNghi]['TenLoaiNghi']   = $item->TenLoaiNghi;
            $retval[$item->Ref_LoaiNghi]['DuyetTheoGio']  = $item->TongSoTheoGio;
            $retval[$item->Ref_LoaiNghi]['DuyetTheoNgay'] = ceil($item->TongSoTheoGio / 8);
        }

        foreach($retval as $key=>$item) {
            if(!isset($retval[$key]['QuyNghiTheoNgay'] )) {
                $retval[$key]['QuyNghiTheoNgay'] = 0;
            }

            if(!isset($retval[$key]['QuyNghiTheoGio'] )) {
                $retval[$key]['QuyNghiTheoGio'] = 0;
            }

            if(!isset($retval[$key]['DuyetTheoGio'] )) {
                $retval[$key]['DuyetTheoGio'] = 0;
            }

            if(!isset($retval[$key]['DuyetTheoNgay'] )) {
                $retval[$key]['DuyetTheoNgay'] = 0;
            }

            $retval[$key]['ConLaiTheoGio'] = $retval[$key]['QuyNghiTheoGio'] - $retval[$key]['DuyetTheoGio'];
        }

        // echo '<pre>'; print_r($retval); die;
        return $retval;
    }

    /**
     * Lấy lịch làm việc của nhân viên
     * @param $m316IOID
     * @param $date
     * @return mixed
     */
    public function getLichNhanVien($m316IOID, $date) {
        $sql = sprintf('
            SELECT OLichLamViecNhanVien.*
            FROM OLichLamViecNhanVien
            WHERE Ref_MaNhanVien = %1$d AND ( %2$s BETWEEN OLichLamViecNhanVien.TuNgay AND OLichLamViecNhanVien.DenNgay)
        ', $m316IOID, $date);

        return $this->_o_DB->fetchAll($sql);
    }

    /**
     * Sẽ lấy ra chi tiết theo phân loại nghỉ của nhân viên (Không theo radio Loại nghỉ: Nghỉ phép năm, phép ốm)
     */
    public function getChiTietNghiCuaNhanVien($m316IOID, $kyCong = 0) {
        $sql = sprintf('
            SELECT *                                             
            FROM ODangKyNghi
            INNER JOIN qsiforms ON ODangKyNghi.IFID_M077 = qsiforms.IFID
            LEFT JOIN OPhanLoaiNghi ON ODangKyNghi.Ref_LoaiNgayNghi = OPhanLoaiNghi.IOID            
        ');

        $sql .= sprintf(' WHERE IFNULL(ODangKyNghi.Ref_MaNhanVien, 0) = %1$d  ', $m316IOID);
        $sql .= sprintf(' AND qsiforms.Status = 2 ');

        if($kyCong) {
            $sql .= sprintf(' AND ( ');
            $sql .= sprintf(' ODangKyNghi.NgayBatDau >= (SELECT ThoiGianBatDau FROM OKyCong WHERE IOID = %1$d) ', $kyCong);
            $sql .= sprintf(' AND ODangKyNghi.NgayBatDau <= (SELECT ThoiGianKetThuc FROM OKyCong WHERE IOID = %1$d) ', $kyCong);
            $sql .= sprintf(' ) ');
        }
        $sql .= sprintf(' GROUP BY OPhanLoaiNghi.IOID ');

        return $this->_o_DB->fetchAll($sql);
    }

    public function consumeNghiTheoNhanVien($m316IOID, $year, $all = false) {
        $sql = sprintf('
            SELECT 
              ODangKyNghi.*
              , SUM( 
                  CASE WHEN WEEK(ODangKyNghi.NgayBatDau) = WEEK(%1$s) AND YEAR(ODangKyNghi.NgayBatDau) = %2$d
                  THEN IFNULL(ODangKyNghi.SoGioNghi, 0) ELSE 0 END
              ) AS SoGioTrongTuan
              , SUM( 
                  CASE WHEN MONTH(ODangKyNghi.NgayBatDau) = MONTH(%1$s) AND YEAR(ODangKyNghi.NgayBatDau) = %2$d 
                  THEN IFNULL(ODangKyNghi.SoGioNghi, 0) ELSE 0 END
              ) AS SoGioTrongThang
              , SUM( 
                  CASE WHEN YEAR(ODangKyNghi.NgayBatDau) = %2$d 
                  THEN IFNULL(ODangKyNghi.SoGioNghi, 0) ELSE 0 END
              ) AS SoGioTrongNam
            FROM ODangKyNghi
            INNER JOIN qsiforms ON ODangKyNghi.IFID_M077 = qsiforms.IFID
        ', $this->_o_DB->quote(date('Y-m-d')), $year);

        $sql.= sprintf(' WHERE IFNULL(ODangKyNghi.Ref_MaNhanVien, 0) =  %1$d   ', $m316IOID);
        $sql.= sprintf(' AND qsiforms.Status =  2   ');
        $sql.= $year?sprintf(' AND YEAR(ODangKyNghi.NgayBatDau) =  %1$d   ', $year):'';

        if($all) {
            $sql.= sprintf(' GROUP BY ODangKyNghi.Ref_MaNhanVien ');
        }
        else {
            $sql.= sprintf(' GROUP BY ODangKyNghi.Ref_MaNhanVien, ODangKyNghi.Ref_LoaiNgayNghi ');
        }


        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function consumeLamThemTheoNhanVien($m316IOID, $year, $all = false) {
        $sql = sprintf('
            SELECT 
              ODangKyLamThem.*
              , SUM( 
                  CASE WHEN WEEK(ODangKyLamThem.NgayDangKy) = WEEK(%1$s) AND YEAR(ODangKyLamThem.NgayDangKy) = %2$d
                  THEN IFNULL(ODangKyLamThem.GioDangKy, 0) ELSE 0 END
              ) AS SoGioTrongTuan
              , SUM( 
                  CASE WHEN MONTH(ODangKyLamThem.NgayDangKy) = MONTH(%1$s) AND YEAR(ODangKyLamThem.NgayDangKy) = %2$d 
                  THEN IFNULL(ODangKyLamThem.GioDangKy, 0) ELSE 0 END
              ) AS SoGioTrongThang
              , SUM( 
                  CASE WHEN YEAR(ODangKyLamThem.NgayDangKy) = %2$d 
                  THEN IFNULL(ODangKyLamThem.GioDangKy, 0) ELSE 0 END
              ) AS SoGioTrongNam
            FROM ODangKyLamThem
            INNER JOIN qsiforms ON ODangKyLamThem.IFID_M078 = qsiforms.IFID
        ', $this->_o_DB->quote(date('Y-m-d')), $year);

        $sql.= sprintf(' WHERE IFNULL(ODangKyLamThem.Ref_MaNhanVien, 0) =  %1$d   ', $m316IOID);
        $sql.= sprintf(' AND qsiforms.Status =  2   ');
        $sql.= $year?sprintf(' AND YEAR(ODangKyLamThem.NgayDangKy) =  %1$d   ', $year):'';


        if($all) {
            $sql.= sprintf(' GROUP BY ODangKyLamThem.Ref_MaNhanVien ');
        }
        else {
            $sql.= sprintf(' GROUP BY ODangKyLamThem.Ref_MaNhanVien, ODangKyLamThem.Ref_LoaiTangCa ');
        }

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }
}