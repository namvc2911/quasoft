<?php
/*
 * search key
 * m1 : getDailyTimesheet($startDate, $endDate, $department = 0, $employee = '')// Lấy bảng chấm công theo ngày
 * 	(+) @todo : Làm nốt lọc theo phòng ban khi có phòng ban id
 * 	(+) @todo : Nên có giới hạn thời gian
 * m2 : getMontlyTimesheet($month, $year, $department = 0) // Lấy bảng tổng hợp chấm công theo tháng
 * 	(+) @todo : Làm nốt lọc theo phòng ban khi có phòng ban id
 * m3 : countByLeaveTypes($month, $year, $department = 0); // Đếm số ngày nghỉ theo phân loại nghỉ
 * 	(+) @todo : Khi có department id phải lọc theo department
 * m5 : getTimesheetChartData($startDate, $endDate, $period, $department, $employee) // Lấy dữ liệu cho timesheet chart
 * 	(+) @todo : Khi có department id phải lọc theo department
 * 	(+) @todo : Limit theo kỳ ( Giải pháp t
 * m6 : getPayroll($ifid, $department = 0, $employee = '') Lấy dữ liệu in bảng lương
 */
class Qss_Model_Extra_Hrm extends Qss_Model_Abstract
{
	/**
	* Build constructor
	*'
	* @return void
	*/
	public function __construct ()
	{
		parent::__construct();
	}
	
	/*
	 * m1 Lấy bảng chấm công theo ngày
	 */
	public function getDailyTimesheet($startDate, $endDate, $department = '', $employee = '')
	{
		$sql = sprintf('select 
						qlcc.*, 
						dsnv.MaPhongBan,dsnv.TenPhongBan,
						min(qlcc.GioVao) as GioBatDau,
						max(qlcc.GioRa) as GioKetThuc,
						GROUP_CONCAT(qlcc.GhiChu) as GhiChuChamCong,
						sum(ifnull(qlcc.SoGio, 0)) as SoGio,
						sum(ifnull(qlcc.DiMuon,0)) as DiMuon,
						sum(ifnull(qlcc.VeSom,0)) as VeSom,
						sum(ifnull(TIMEDIFF(qlcc.KetThuc,qlcc.BatDau),0)) as SoGioYeuCau,
						dsnv.Ref_LichLamViec
						from OQuanLyChamCong as qlcc
						inner join ODanhSachNhanVien as dsnv
						on dsnv.IOID = qlcc.Ref_MaNhanVien
						where (qlcc.Ngay between %1$s and %2$s)
						', $this->_o_DB->quote($startDate), 
						$this->_o_DB->quote($endDate) );
		
		$sql .= $employee?' and qlcc.MaNhanVien like  \'%'.trim($employee).'%\' ':'';
		$sql .= $department?" and dsnv.MaPhongBan = '{$department}'":''; 
		$sql .= ' group by qlcc.Ngay,  dsnv.IOID ';
		$sql .= ' order by dsnv.MaPhongBan ';
		//echo $sql; die;
		return $this->_o_DB->fetchAll($sql);
	}
	
	/*
	 * m2 Lấy bảng tổng hợp chấm công theo tháng
	 */
	public function getMontlyTimesheet($month, $year, $department = 0)
	{
		$sql = sprintf('select qlcc.*,
						dsnv.MaPhongBan as PhongBan,
						(SUM(CASE WHEN (qlcc.DiMuon <> 0) THEN 1 ELSE 0 END)) as SoLanDiMuon,
						(SUM(CASE WHEN (qlcc.VeSom <> 0) THEN 1 ELSE 0 END)) as SoLanVeSom,
						sum(ifnull(qlcc.SoGio, 0)) as SoGio,
						sum(ifnull(TIMEDIFF(qlcc.KetThuc,qlcc.BatDau),0)) as SoGioYeuCau,
						day(qlcc.Ngay) as NgayTrongThang
						from OQuanLyChamCong as qlcc
						inner join ODanhSachNhanVien as dsnv
						on dsnv.IOID = qlcc.Ref_MaNhanVien
						where month(qlcc.Ngay) = %1$d
						and year(qlcc.Ngay) = %2$d
						', $month, $year);
		$sql .= $department?" and dsnv.Ref_MaPhongBan = {$department}":''; 
		$sql .= ' group by qlcc.Ngay,  dsnv.IOID ';
		$sql .= ' order by dsnv.MaPhongBan , dsnv.IOID ';
		//echo $sql; die;
		return $this->_o_DB->fetchAll($sql);	
	}
	
	/*
	 * m3 Đếm số ngày nghỉ theo phân loại nghỉ
	 */
	public function countByLeaveTypes($month, $year, $department = 0)
	{
		$sql = sprintf('select 
						qln.*,
						count(qln.MaNghi) as DemNghi																	
						from ODieuChinhLichNV as qln
						inner join ODanhSachNhanVien as dsnv
						on qln.Ref_MaNV = dsnv.IOID
						where 
						qln.MaNghi is not null
						and month(qln.Ngay) = %1$d
						and year(qln.Ngay) = %2$d
						',$month, $year );
		$sql .= $department?" and dsnv.Ref_MaPhongBan = {$department}":''; 
		$sql .= ' group by qln.Ref_MaNV, qln.MaNghi ';
		//echo $sql; die;
		return $this->_o_DB->fetchAll($sql);
		
	}
	
	/*
	 * m5 Lấy dữ liệu cho timesheet chart
	 */
	public function getTimesheetChartData($startDate, $endDate, $period
                                            , $department = '', $employee = 0 )
	{
		
		$tmp = $employee?' and qlcc.Ref_MaNhanVien  = '.$employee:'';
		$tmp.= (!$employee && $department)?" and dsnv.Ref_MaPhongBan = '{$department}'":''; 
				
		$sql = sprintf('
				select
				Ngay, TuanThu, ThangThu,NamThu, QuyThu,
				sum(SoLanDiMuon) as TongSoDiMuon,
				sum(SoLanVeSom) as TongSoVeSom,
				sum(SubTable.Cong) as TongCong,
				sum(SubTable.SoGio) as TongGio
				from
                (
					select 
					dsnv.MaPhongBan as PhongBan,
					sum(ifnull(qlcc.SoGio, 0)) as SoGio,
					sum(ifnull(TIMEDIFF(qlcc.KetThuc,qlcc.BatDau),0)) as SoGioYeuCau,
					(SUM(CASE WHEN (qlcc.DiMuon <> 0) THEN 1 ELSE 0 END)) as SoLanDiMuon,
					(SUM(CASE WHEN (qlcc.VeSom <> 0) THEN 1 ELSE 0 END)) as SoLanVeSom,
					((FLOOR(sum(ifnull(qlcc.SoGio, 0)) / sum(ifnull(TIMEDIFF(qlcc.KetThuc,qlcc.BatDau),0)) * 10) /10 ) ) as Cong,
					qlcc.Ngay,
					week(qlcc.Ngay) as TuanThu,
					month(qlcc.Ngay) as ThangThu,
					year(qlcc.Ngay) as NamThu,
					quarter(qlcc.Ngay) as QuyThu
					
					from OQuanLyChamCong as qlcc
					inner join ODanhSachNhanVien as dsnv
					on dsnv.IOID = qlcc.Ref_MaNhanVien
					where (qlcc.Ngay between %1$s and %2$s) %3$s
					group by qlcc.Ngay, qlcc.Ref_MaNhanVien
				) as SubTable
						', $this->_o_DB->quote($startDate), 
						$this->_o_DB->quote($endDate), $tmp );
						
		switch ($period)
		{
			case 'D':
				$sql     .= ' group by SubTable.Ngay ';
			break;
			case 'W':
				$sql .= ' group by week(SubTable.Ngay) ';
			break;
			case 'M':
				$sql .= ' group by month(SubTable.Ngay) ';
			break;
			case 'Q':
				$sql .= ' group by quarter(SubTable.Ngay) ';
			break;
			case 'Y':
				$sql .= ' group by year(SubTable.Ngay)';
			break;
			
		}
		return $this->_o_DB->fetchAll($sql);
	}
	
	/** 
	 * Function: Lấy dữ liệu bảng lương theo ifid
	 * Description: Lấy thông tin bảng lương của một tháng theo ifid thêm hai điều kiện lọc là nhân viên và
	 * phòng ban.
	 * Search key: m6  	 
	 */
	public function getPayroll($ifid, $department = 0, $employee = 0)
	{
		$sql = sprintf('select ctbl.*, dsnv.NgayGiaNhap
						from OBangLuong as bl
						inner join OCTBangLuong as ctbl
						on ctbl.IFID_M327 = bl.IFID_M327
						inner join ODanhSachNhanVien as dsnv
						on ctbl.Ref_MaNhanVien = dsnv.IOID
						where bl.IFID_M327 = %1$d 
						and (dsnv.NgayThoiViec is null 
							or (year(dsnv.NgayThoiViec) >= bl.Nam and month(dsnv.NgayThoiViec) >= bl.Thang ) )
						',$ifid);
		$sql .= $employee?" and ctbl.Ref_MaNhanVien =  {$employee} ":'';
		$sql .= (!$employee && $department)?sprintf(' and ctbl.Ref_PhongBan = %1$d',$department):'';
		$sql .= ' order by ctbl.Ref_PhongBan, ctbl.Ref_MaNhanVien ';
		//echo $sql; die;
		return $this->_o_DB->fetchAll($sql);
	}
	/* End Function: Lấy dữ liệu bảng lương theo ifid*/
        
}