<?php
/*
 * Model xử lý m817, m818, m819: xử lý tiêu thụ nước
 */
class Qss_Model_Maintenance_Water extends Qss_Model_Abstract{
	public function __construct(){
		parent::__construct();
	}
		
	/* M819(ngày 22/01/2016):Lấy danh sách các đơn vị*/
	public function getListMember(){
		$sql = sprintf('
				Select DanhMuc.IOID, DanhMuc.CapDen,DanhMuc.Ref_TrucThuoc,
					  (Select count(*) From ODanhMucCongToNuoc as u Where  u.lft <= DanhMuc.lft and u.rgt >= DanhMuc.rgt) as level
				From  ODanhMucCongToNuoc as DanhMuc
				Group  by DanhMuc.IOID
				Order by DanhMuc.lft, DanhMuc.CapDen
				');
		//echo "<pre >";
		//print ($sql);
		//die;
		return $this->_o_DB->fetchAll($sql);
	}
	/* M819(ngày 22/01/2016):Lấy dữ liệu*/
	public function getDataMember($monthStart, $monthEnd, $year){
		$sql = sprintf('
				Select 
					DanhMuc.IOID,
					sum(ChiSo.ChiSoCuoi -ChiSo.ChiSoDau) as ChiSoCongToNuoc, 
					(sum((ChiSo.ChiSoCuoi - ChiSo.ChiSoDau)*(ChiSo.DonGia/1000)) / sum(ChiSo.ChiSoCuoi - ChiSo.ChiSoDau)) as TrungBinhDonGia,
					sum((ChiSo.ChiSoCuoi-ChiSo.ChiSoDau )*(ChiSo.DonGia/1000)) as TongTienCongToNuoc,
					group_concat(SoHoaDon SEPARATOR ",") as SoHoaDon,
					DanhMuc.Ref_TrucThuoc
				From ODanhMucCongToNuoc as DanhMuc
				Left join OChiSoCongToNuoc as ChiSo
				On ChiSo.Ref_CongTo = DanhMuc.IOID
				Where ChiSo.Thang >= %1$d
				And ChiSo.Thang <= %2$d
				And ChiSo.Nam = %3$d
				Group by DanhMuc.IOID
				Order by DanhMuc.lft, CapDen
				', $monthStart, $monthEnd, $year);
		return $this->_o_DB->fetchAll($sql);
	}
	/*--------------------------------------------------------------------------------------------------------------*/

	/*M817: Model Show() in dữ liệu cho M817*/
	public function getDataMetter($monthStart, $monthEnd, $year){
		$sql = sprintf('
				select DanhMuc.*,
					   Sum(ChiSo.ChiSoCuoi - ChiSo.ChiSoDau) as TongKhoiLuong, Sum((ChiSo.ChiSoCuoi - ChiSo.ChiSoDau)*(ChiSo.DonGia/1000)) as TongThanhTien,
					   ( Sum((ChiSo.ChiSoCuoi - ChiSo.ChiSoDau)*(ChiSo.DonGia/1000)) / Sum(ChiSo.ChiSoCuoi - ChiSo.ChiSoDau)) as TrungBinhDonGia
				from OChiSoCongToNuoc as ChiSo
				left join ODanhMucCongToNuoc as DanhMuc
				on ChiSo.Ref_CongTo = DanhMuc.IOID
				where ChiSo.Thang >= %1$d
				and ChiSo.Thang <= %2$d
				and ChiSo.Nam = %3$d
				group by DanhMuc.IOID
				',$monthStart, $monthEnd, $year);
		return $this->_o_DB->fetchAll($sql);
	}
	
	
	/*M817: Model Show() in dữ liệu cho công tơ tổng */
	public function getDataSumMetter($monthStart, $monthEnd, $year){
		$sql = sprintf('
				select DanhMuc.MaCongTo, DanhMuc.CapDen, DanhMuc.DonVi,
					(sum(ChiSo.ChiSoCuoi - ChiSo.ChiSoDau) - ifnull((select sum(ChiSoCuoi-ChiSoDau) 
															from  OChiSoCongToNuoc as ChiSo1
															inner join ODanhMucCongToNuoc as DanhMuc1
															on DanhMuc1.IOID = ChiSo1.Ref_CongTo
															where DanhMuc1.lft > DanhMuc.lft and DanhMuc1.rgt  < DanhMuc.rgt
															and	ChiSo1.Thang >= %1$d
															and ChiSo1.Thang <= %2$d
															and ChiSo1.Nam = %3$d
															),0)
				    ) as SoLuong  , 
					
					sum((ChiSoCuoi-ChiSoDau - ifnull((select sum(ChiSoCuoi-ChiSoDau) 
												from  OChiSoCongToNuoc as ChiSo1
												inner join ODanhMucCongToNuoc as DanhMuc1
												on DanhMuc1.IOID = ChiSo1.Ref_CongTo
												where DanhMuc1.lft > DanhMuc.lft and DanhMuc1.rgt  < DanhMuc.rgt
												and	ChiSo1.Thang >= %1$d
												and ChiSo1.Thang <= %2$d
												and ChiSo1.Nam = %3$d
												),0))*(ChiSo.DonGia/1000)) as ThanhTien,
				(Select count(*) From ODanhMucCongToNuoc as u Where  u.lft <= DanhMuc.lft and u.rgt >= DanhMuc.rgt) as level
				from OChiSoCongToNuoc as ChiSo
				inner join ODanhMucCongToNuoc as DanhMuc
				on DanhMuc.IOID = ChiSo.Ref_CongTo
				where ChiSo.Thang >= %1$d
				and ChiSo.Thang <= %2$d
				and ChiSo.Nam = %3$d
				group by DanhMuc.IOID
				order by DanhMuc.lft
				',$monthStart, $monthEnd, $year);
		return $this->_o_DB->fetchAll($sql);
	}
	
	/*M818: Lấy ra danh sách công tơ*/
	public function getMeters()
	{
		$sql = sprintf('
				select* from ODanhMucCongToNuoc order by Ref_DonVi, IOID
				');
		return $this->_o_DB->fetchAll($sql);
	}
	
	/*M818: dữ liệu in ra*/
	public function getInfomationMetter ($monthStart, $monthEnd, $year){
		$sql = sprintf ('
				select DanhMuc.IOID, DanhMuc.MaCongTo, DanhMuc.CapDen, DanhMuc.DonVi, DanhMuc.Ref_DonVi,ChiSo.Thang,
					(ChiSoCuoi-ChiSoDau - ifnull((select sum(ChiSoCuoi-ChiSoDau) from  OChiSoCongToNuoc as ChiSo1
												inner join ODanhMucCongToNuoc as DanhMuc1
												on DanhMuc1.IOID = ChiSo1.Ref_CongTo
												where DanhMuc1.lft > DanhMuc.lft and DanhMuc1.rgt  < DanhMuc.rgt
												and	ChiSo1.Thang = ChiSo.Thang
												and ChiSo1.Nam = ChiSo.Nam
												),0)

				    ) as SoLuong  , ChiSo.DonGia, (sum((ChiSoCuoi-ChiSoDau - ifnull((select sum(ChiSoCuoi-ChiSoDau) from  OChiSoCongToNuoc as ChiSo1
												inner join ODanhMucCongToNuoc as DanhMuc1
												on DanhMuc1.IOID = ChiSo1.Ref_CongTo
												where DanhMuc1.lft > DanhMuc.lft and DanhMuc1.rgt  < DanhMuc.rgt
												and	ChiSo1.Thang = ChiSo.Thang
												and ChiSo1.Nam = ChiSo.Nam
												),0)))*((ChiSo.DonGia/1000)))as ThanhTien,
				(Select count(*) From ODanhMucCongToNuoc as u Where  u.lft <= DanhMuc.lft and u.rgt >= DanhMuc.rgt) as level
				from OChiSoCongToNuoc as ChiSo
				inner join ODanhMucCongToNuoc as DanhMuc
				on DanhMuc.IOID = ChiSo.Ref_CongTo
				where ChiSo.Thang >= %1$d
				and ChiSo.Thang <= %2$d
				and ChiSo.Nam = %3$d
				group by DanhMuc.IOID, ChiSo.Thang
				order by DanhMuc.Ref_DonVi, DanhMuc.IOID
				',$monthStart, $monthEnd, $year);
		//echo '<pre>';echo $sql;die;
		return  $this->_o_DB->fetchAll($sql);
	}
	
	/*M823: danh sach các công tơ */
	public function listMetterFollow(){
		$sql = sprintf('
				select *
				from OTheoDoiVaBoSungNuoc
				group by MaCongTo
				order by Ref_NhomCongTo, Ref_MaCongTo
				');
		//echo '<pre>';echo $sql;
		return $this->_o_DB->fetchAll($sql);
	}
	/*M823: dữ liệu*/
	public function followAndAddMetter( $dayStartToMysql,  $dayEndToMysql){
		$sql = sprintf('
				select ChiSo.Ngay as Ngay, ChiSo.ChiSo as ChiSo, ChiSo.Ref_MaCongTo,
					   DanhSach.*
				from OChiSoCongToTuDong as ChiSo
				left join OTheoDoiVaBoSungNuoc as DanhSach
				on ChiSo.MaCongTo = DanhSach.MaCongTo
				where ChiSo.Ngay >= \'%1$s\'
				and ChiSo.Ngay <= \'%2$s\'
				order by ChiSo.Ngay, DanhSach.Ref_NhomCongTo
				', $dayStartToMysql,  $dayEndToMysql);
		//echo '<pre>';echo $sql; die;
		return $this->_o_DB->fetchAll($sql);
	}
	
	/*M821: Model Show() in dữ liệu cho M821*/
	public function getListToBuyInsurrance($startDayToMysql, $endDayToMysql, $IOID){
		$sqlAdd = '';
		/*Th1: Nếu IOID không rỗng (Người dùng chọn thiết bị ở đầu vào)*/
		if ($IOID){
			$sqlAdd = 'AND ThietBi.IOID = '.$IOID;
		}
		/*Th2: Nếu IOID rỗng thì in hết thiết bị ra*/
		else {
			$sqlAdd = 'AND 1=1 ';
		}
		$sql = sprintf('
				SELECT
					BaoHiem.DonViBaoHiem, BaoHiem.SoHopDong, BaoHiem.SoTienDong, BaoHiem.PhiBaoHiem, BaoHiem.NgayDongBaoHiem, BaoHiem.NgayHetHan, BaoHiem.NoiDung,
					ThietBi.TenThietBi, ThietBi.XuatXu, ThietBi.NgayDuaVaoSuDung, ThietBi.NguyenGia, ThietBi.NamSanXuat,
					Nhom.MoTa
				FROM OBaoHiemThietBi as BaoHiem
				INNER JOIN ODanhSachThietBi as ThietBi
				ON BaoHiem.Ref_MaThietBi = ThietBi.IOID
				INNER JOIN ONhomThietBi as Nhom
				ON ThietBi.Ref_NhomThietBi = Nhom.IOID
				WHERE BaoHiem.NgayDongBaoHiem >= %1$s
				AND BaoHiem.NgayHetHan <=%2$s
				%3$s
				ORDER BY Nhom.MoTa, ThietBi.TenThietBi, BaoHiem.NgayDongBaoHiem
				',$this->_o_DB->quote($startDayToMysql), $this->_o_DB->quote($endDayToMysql), $sqlAdd);
		//echo "<pre>";
		//echo $sql;
		//die;
		return $this->_o_DB->fetchAll($sql);
	}
	
	/*M822: Model Show() in dữ liệu cho M822*/
	public function getListToInsurrance($startDayToMysql, $endDayToMysql, $IOID){
		$sqlAdd = '';
		/*Nếu IOID không rỗng (Người dùng chọn thiết bị ở đầu vào)*/
		if ($IOID){
			$sqlAdd = 'AND ThietBi.IOID = '.$IOID;
		}
		/*Nếu IOID rỗng thì in hết thiết bị ra*/
		else {
			$sqlAdd = 'AND 1=1 ';
		}
		$sql = sprintf('
				SELECT
					HieuChuan.TenThietBi as TenThietBi, HieuChuan.DonVi, HieuChuan.Ngay, HieuChuan.NgayKiemDinhTiepTheo,
					ChiTiet.BoPhan, ChiTiet.HanKiemDinh, ChiTiet.GhiChu, ChiTiet.Serial,
					ThietBi.Kieu, ThietBi.LoaiThietBi, ThietBi.NamSanXuat, ThietBi.Model
				FROM OHieuChuanKiemDinh as HieuChuan
				LEFT JOIN OKiemDinhChiTiet as ChiTiet
				ON HieuChuan.IFID_M753= ChiTiet.IFID_M753
				LEFT JOIN ODanhSachThietBi as ThietBi
				ON HieuChuan.Ref_MaThietBi= ThietBi.IOID
				WHERE HieuChuan.Ngay >= \'%1$s\'
				%2$s
				ORDER BY HieuChuan.TenThietBi ASC,HieuChuan.DonVi ASC, ChiTiet.BoPhan ASC
				',$startDayToMysql, $sqlAdd);
		//echo "<pre>";
		//echo $sql;
		//die;
		return $this->_o_DB->fetchAll($sql);
	}
	
	
	/*M822: Tính số max của thiết bị con trong báo cáo m822*/
	public  function  getCountMaxDevice($startDayToMysql, $endDayToMysql, $IOID){
		$sqlAdd = '';
		/*Nếu IOID không rỗng (Người dùng chọn thiết bị ở đầu vào)*/
		if ($IOID){
			$sqlAdd = 'AND ThietBi.IOID = '.$IOID;
		}
		/*Nếu IOID rỗng thì in hết thiết bị ra*/
		else {
			$sqlAdd = 'AND 1=1 ';
		}
		$sql = sprintf('
				select
					max(BienDem) as Max
				from (
					Select count(*) as BienDem
					From OHieuChuanKiemDinh as HieuChuan
					LEFT JOIN OKiemDinhChiTiet as ChiTiet
					ON HieuChuan.IFID_M753= ChiTiet.IFID_M753
					LEFT JOIN ODanhSachThietBi as ThietBi
					ON HieuChuan.Ref_MaThietBi= ThietBi.IOID
					WHERE HieuChuan.Ngay >= %1$s
					%2$s
					group by HieuChuan.IFID_M753) as tb
				',$startDayToMysql, $sqlAdd);
		return $this->_o_DB->fetchAll($sql);
		echo "<pre>";
		echo $sql;
		die;
	}
}
?>