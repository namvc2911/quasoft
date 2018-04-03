<?php
class Qss_Bin_Process_Salary extends Qss_Lib_Bin
{
	public function __doExecute()
	{
		
		$salary             = array();
		$numOfWorkingDay    = 22; // Số ngày tính lương
		$glue               = '-'; // Dấu phân cách ngày tháng
		$paidExtraPercel    = 100;// Phần trăm hưởng lương ngoài giờ
		$insert             = array();
		$huongPhuCapKhiNghi = 0;
		$soGioYeuCau        = 8; // @todo : Cái này cần tính cho từng ngày làm việc.
		/*
		 * Xác định tháng tính lương
		 */
		$month  = $this->_params->Thang;
		$month  = ($month < 10)?'0'.$month:''.$month; // convert to string
		$year   = $this->_params->Nam;
		
		// Tìm ngày bắt đầu, kết thúc của tháng
		$solar        = new Qss_Model_Calendar_Solar();
		$daysInMonth  = $solar->getDaysInMonth($month, $year);// Lấy ngày trong tháng
		$startOfMonth = $year.$glue.$month.$glue.'01'; // Y-m-d (mysql date format)
		$endOfMonth   = $year.$glue.$month.$glue.$daysInMonth; // Y-m-d (mysql date format)
		
		
		if($this->onValidate($month, $year))
		{
			
			/*
			 * Lấy tham số hệ thống
			 */
			$sysParamsModel = new Qss_Model_System_Param;
			$allSysParams   = $sysParamsModel->getAllParams(); // Lấy toàn bộ tham số hệ thống
			$sysParams      = array(); // Mảng chứa tham số hệ thống
			
			foreach ($allSysParams as $sysPara)
			{
				$sysParams[$sysPara->PID] = $sysPara->Value;
			}
			
			/* 
			 * Lương chính + Phụ cấp + Bảo hiểm (Nên tách bảo hiểm ra )
			 */
			$sql = sprintf('select 
							max(hdld.NgayBatDau) as NgayBatDau, 
							dsnv.IOID ,
							dsnv.MaNhanVien,
							hdld.Ref_LichLamViec,
							(hdld.HeSoLuong * %2$d) as LuongChinh,
							CAST((ifnull(npc.TongTienPhuCap,0)/1000) as UNSIGNED) as PhuCap ,
							CAST((ifnull(npc.TongTienPCChiuThue,0)/1000) as UNSIGNED) as PhuCapChiuThue,
							CAST(bh.BHYT/1000 as UNSIGNED) as BHYT,
							CAST(bh.BHXH/1000 as UNSIGNED) as BHXH,
							CAST(bh.BHTN/1000 as UNSIGNED) as BHTN
							from ODanhSachNhanVien as dsnv
							inner join qsiforms as qsi
							on qsi.IFID = dsnv.IFID_M316
						    inner join OHopDongLaoDong as hdld
						    on dsnv.IOID = hdld.Ref_MaNhanVien
						    left join ONhomPhuCap as npc 
						    on npc.IOID = hdld.Ref_NhomPhuCap
						    left join OBaoHiem as bh
						    on bh.Ref_MaNhanVien = dsnv.IOID
						    and bh.NgayBatDau >= %1$s
						    and bh.NgayKetThuc <= %3$s
						    where qsi.Status = 2
						    group by hdld.Ref_MaNhanVien
						    having NgayBatDau <= %1$s
						    ', $this->_db->quote($startOfMonth)
							 , $sysParams['LCB']
							 , $this->_db->quote($endOfMonth));
							 
			$params  = $this->_db->fetchAll($sql);
			foreach ($params as $p)
			{
				$salary[$p->IOID]['LuongChinh']     = $p->LuongChinh;
				$salary[$p->IOID]['PhuCap']         = $p->PhuCap;
				$salary[$p->IOID]['PhuCapChiuThue'] = $p->PhuCapChiuThue;
				$salary[$p->IOID]['BHYT']           = $p->BHYT;
				$salary[$p->IOID]['BHXH']           = $p->BHXH;
				$salary[$p->IOID]['BHTN']           = $p->BHTN;
				$salary[$p->IOID]['MaNhanVien']     = $p->MaNhanVien;
			}
				
			/*
			 * Tính thuế TNCN
			 */
			$sql = sprintf('select 
							dsnv.IOID,
							(sum(case when qhgd.GiamTruPhuThuoc = 1 then 1 else 0 end) * %1$d) 
							as GiamTruPhuThuoc
							from ODanhSachNhanVien as dsnv
							inner join qsiforms as qsi
							on qsi.IFID = dsnv.IFID_M316
							left join OQuanHeGiaDinh as qhgd
							on dsnv.IFID_M316 = qhgd.IFID_M316
							where qsi.Status = 2
							group by dsnv.IOID
							', $sysParams['GTPT']);
			$params  = $this->_db->fetchAll($sql);
			foreach ($params as $p)
			{
				$salary[$p->IOID]['GiamTruPhuThuoc']     = $p->GiamTruPhuThuoc;
			}
			
			$sql                   = 'SELECT 
									 *, CAST(SoTienKhoiDiem/1000 as UNSIGNED) as SoTienKhoiDiem,
									 CAST(SoTienKetThuc/1000 as UNSIGNED) as SoTienKetThuc
									 FROM OMucThueTNCN order by Muc ASC';
			$personalTaxLevel      = $this->_db->fetchAll($sql);
			$countPersonalTaxLevel = count((array)$personalTaxLevel);
			
			
			/*
			 * Khen thưởng kỷ luật
			 */
			$sql = sprintf('select 
							dsnv.IOID,
							ifnull(ktkl.KhenThuong,0) as KhenThuong,
							ifnull(ktkl.KyLuat,0) as KyLuat
							from ODanhSachNhanVien as dsnv
							inner join qsiforms as qsi
							on qsi.IFID = dsnv.IFID_M316
							left join 
							(
								select 
								Ref_MaNhanVien,
								CAST((sum(case when KhenThuong = 1 then ifnull(SoTien,0) else 0 end)/1000) as UNSIGNED) as KhenThuong,
								CAST((sum(case when KyLuat = 1 then ifnull(SoTien,0) else 0 end)/1000) as UNSIGNED) as KyLuat,
								Ngay
								From OKhenThuongKyLuat
								where Ngay between %1$s and %2$s
								group by Ref_MaNhanVien
							) as ktkl
							on ktkl.Ref_MaNhanVien = dsnv.IOID
							where qsi.Status = 2
							
							'
							, $this->_db->quote($startOfMonth)
							, $this->_db->quote($endOfMonth));
						
			$params  = $this->_db->fetchAll($sql);
			foreach ($params as $p)
			{
				$salary[$p->IOID]['KhenThuong'] = $p->KhenThuong;
				$salary[$p->IOID]['KyLuat']     = $p->KyLuat;
			}
				
			/*
			 * Công
			 * 							inner join ODanhSachNhanVien as dsnv
							on dsnv.IOID = qlcc.Ref_MaNhanVien
							inner join qsiforms as qsi
							on qsi.IFID = dsnv.IFID_M316
							
							qsi.Status = 2 and 
			 */
			
			// Số ngày công thực làm
			// @todo : Xem xét lại số giờ làm thêm
			$sql = sprintf('
							select
							ifnull(qlcc2.Cong,0) as Cong,
							ifnull(qlcc2.SoGioLamThem,0) as SoGioLamThem,
							dsnv.IOID
							from ODanhSachNhanVien as dsnv
							inner join qsiforms as qsi
							on qsi.IFID = dsnv.IFID_M316
							left join
							(select 
							qlcc.Ref_MaNhanVien,
							((FLOOR(sum(ifnull(qlcc.SoGio, 0)) / sum(ifnull(TIMEDIFF(qlcc.KetThuc,qlcc.BatDau),0)) * 10) /10 ) ) as Cong,
							sum( case when qlcc.SoGio > ifnull(TIMEDIFF(qlcc.KetThuc,qlcc.BatDau),0)
							then (qlcc.SoGio - ifnull(TIMEDIFF(qlcc.KetThuc,qlcc.BatDau),0)) else 0 end) as SoGioLamThem
							from OQuanLyChamCong as qlcc
							where (qlcc.Ngay between %1$s and %2$s)
							group by qlcc.Ngay, qlcc.Ref_MaNhanVien) as qlcc2
							on dsnv.IOID = qlcc2.Ref_MaNhanVien
							where qsi.Status = 2'
							, $this->_db->quote($startOfMonth)
							, $this->_db->quote($endOfMonth));
			$params  = $this->_db->fetchAll($sql);
			
			foreach ($params as $p)
			{
				$salary[$p->IOID]['Cong']         = $p->Cong;
				$salary[$p->IOID]['SoGioLamThem'] = $p->SoGioLamThem;
			}
			
			/*
			 * Nghỉ
			 * @todo: chưa trừ được ngày không làm việc
			 */
			$sql = sprintf('select 
							dsnv.IOID,
							case when qln.NgayBatDau = qln.NgayKetThuc  
									and  month(qln.NgayBatDau) = %3$d and year(qln.NgayBatDau) = %4$d then 1 
							else
								case when qln.NgayBatDau <  %1$s 
									and qln.NgayKetThuc > %2$s then ifnull((DATEDIFF(%2$d, %1$s) ),0)
								when qln.NgayBatDau <  %1$s then ifnull((DATEDIFF(qln.NgayKetThuc, %1$s)),0)
								when qln.NgayKetThuc > %2$s then ifnull((DATEDIFF(%2$s, qln.NgayBatDau) ),0)
								else ifnull((DATEDIFF(qln.NgayKetThuc,qln.NgayBatDau) ),0)
								end 
							end as SoNgayNghi,
							ifnull(pln.PhanTramHuongLuong,0) as PhanTramHuongLuong,
							qln.NgayKetThuc,
							qln.NgayBatDau
							from ODanhSachNhanVien as dsnv
							inner join qsiforms as qsi
							on qsi.IFID = dsnv.IFID_M316
							left join OQuanLyNghi as qln
							on dsnv.IOID = qln.Ref_MaNhanVien
							left join OPhanLoaiNghi as pln
							on qln.Ref_PhanLoaiNghi = pln.IOID
							and (qln.NgayBatDau >= %1$s 
							or qln.NgayKetThuc <= %2$s)
							where qsi.Status = 2
							order by dsnv.IOID
							'						
							, $this->_db->quote($startOfMonth)
							, $this->_db->quote($endOfMonth)
							, $month 
							, $year);
			
			$params         = $this->_db->fetchAll($sql);
			$i              = 0;
			foreach ($params as $p)
			{
				$salary[$p->IOID]['TruNghi'][$i]['SoNgayNghi']         = $p->SoNgayNghi;
				$salary[$p->IOID]['TruNghi'][$i]['PhanTramHuongLuong'] = $p->PhanTramHuongLuong;
				$salary[$p->IOID]['TruNghi'][$i]['PhanTramTruLuong']   = 100 - $p->PhanTramHuongLuong;
				$i++;
			}
			
			/*
			 * Tạm ứng
			 */
			$sql = sprintf('select
							ifnull(dstu.TamUng,0) as SoTienTamUng,
							dsnv.IOID 
							from ODanhSachNhanVien as dsnv
							inner join qsiforms as qsi
							on qsi.IFID = dsnv.IFID_M316
							left join 
							(
							 select *,
							 CAST(sum(ifnull(SoTienTamUng,0))/1000 as UNSIGNED) as TamUng 
							 from ODanhSachTamUng 
							 where ThangTamUng = %1$d and Nam = %2$d
							 group by Ref_MaNhanVien
							) as dstu
							on  dsnv.IOID = dstu.Ref_MaNhanVien
							where 
							qsi.Status = 2 

							 ', $month, $year);
				
			$params  = $this->_db->fetchAll($sql);
			foreach ($params as $p)
			{
				$salary[$p->IOID]['SoTienTamUng']         = $p->SoTienTamUng;
			}
	
			
			/*
			 * Tạo mảng cập nhật bảng lương
			 */
			$countEmployee = 0;
			foreach ($salary as $employee)
			{
				
				// Hưởng phụ cấp khi không làm việc hay không?
				if(!$employee['Cong'])
				{
					if(!$huongPhuCapKhiNghi)
					{
						$employee['PhuCap']         = 0;
						$employee['PhuCapChiuThue'] = 0;
					}
				}
				
				$leaveSalary = 0; // Lương ngày nghỉ
				$truNghi     = 0; // Trừ tiền ngày nghỉ
				$luongNgay   = (int) ($employee['LuongChinh'] / $numOfWorkingDay);// Lương ngày
				// Tính lương ngày nghỉ , số tiền trừ đi của ngày nghỉ
				foreach ($employee['TruNghi'] as $ls)
				{
					$leaveSalary += (int) (($luongNgay * $ls['SoNgayNghi']) * $ls['PhanTramHuongLuong']/100);
					$truNghi     += (int) (($luongNgay * $ls['SoNgayNghi']) * $ls['PhanTramTruLuong']/100);
				}
				
				// Lương ngoài giờ = Số giờ nhân cho lương trên một giờ nhân phần trăm hưởng lương
				// @todo : Cần tính lương làm việc trên giờ					
				$luongNgoaiGio = (int) (($employee['SoGioLamThem'] * ($luongNgay/$soGioYeuCau)  * $paidExtraPercel)/100);
				
				// Tông lương chưa có phụ cấp
				$luong          =  ($luongNgay * $employee['Cong']) + $leaveSalary + $luongNgoaiGio; 
				
				// Số tiền chịu thuế thu nhập cá nhân, 
				$soTienChiuThue = ($luong + $employee['PhuCapChiuThue']) -
									($employee['BHXH'] +$employee['BHYT'] +$employee['BHTN'] 
									+ $employee['GiamTruPhuThuoc'] +$sysParams['GTGC']) ;
									
				$countLevel     = 0; // Đếm level của personal tax
				$personalTax    = 0; // Giá trị của personal tax
				// Tính thuế thu nhập cho từng nhân viên
				if($soTienChiuThue > 0)
				{
					foreach ($personalTaxLevel as $level)
					{
						// Nếu số tiền chịu thuế nằm trong khoảng chịu thuế này
						if( ($soTienChiuThue >= $level->SoTienKhoiDiem && $soTienChiuThue <= $level->SoTienKetThuc )
							|| ($soTienChiuThue > $level->SoTienKetThuc && $countPersonalTaxLevel == $countLevel) )
						{
							$personalTax = (int) (($soTienChiuThue * $level->PhanTramChiuThue) / 100);
							break;	
						}
						$countLevel++;			
					}
				}
				
				// luong net, gross
				$luongGross    = $luong;
				$luongNet      = ($luong + $employee['PhuCap']) -
					($employee['BHXH'] + $employee['BHYT'] +$employee['BHTN'] + $personalTax) ;
				
				$luongCL = 0;
				$luongCL = ($luongNet + $employee['KhenThuong']) - ($employee['SoTienTamUng'] + $employee['KyLuat']); 
				
				// Chỉ nghỉ được hưởng lương hoặc có ngày công thì mới cập nhật vào bảng lương
				if($leaveSalary || $employee['Cong'])
				{
					$insert[$countEmployee]['MaNhanVien']       = $employee['MaNhanVien']; 
					$insert[$countEmployee]['SoTienPhuCap']     = $employee['PhuCap'];
					$insert[$countEmployee]['SoTienPCChiuThue'] = $employee['PhuCapChiuThue'];
					$insert[$countEmployee]['SoTienDongBHYT']   = $employee['BHYT'];
					$insert[$countEmployee]['SoTienDongBHXH']   = $employee['BHXH'];
					$insert[$countEmployee]['SoTienDongBHTN']   = $employee['BHTN'];
					$insert[$countEmployee]['CacKhoanGiamTru']  = $employee['KyLuat'];
					$insert[$countEmployee]['CacKhoanCongThem'] = $employee['KhenThuong'];
					$insert[$countEmployee]['NgayNghi']         = $truNghi; // Trừ ngày nghỉ
					$insert[$countEmployee]['NgayCong']         = $employee['Cong'];
					$insert[$countEmployee]['LuongNgoaiGio']    = $luongNgoaiGio;
					$insert[$countEmployee]['LuongGross']       = $luongGross;
					$insert[$countEmployee]['LuongNet']         = $luongNet;
					$insert[$countEmployee]['TamUng']           = $employee['SoTienTamUng'];
					$insert[$countEmployee]['ThueTNCN']         = $personalTax;
					$insert[$countEmployee]['LuongCL']          = $luongCL;
					$countEmployee++;
				}
			}
			
			/*
			 * Cập nhật vào bảng
			 */
			if(count($insert))
			{
				$insert = array('OBangLuong'=>array(array('Thang'=>(int)$month,'Nam'=>$year)),
								'OCTBangLuong'=>$insert);
				$service = $this->services->Form->Manual('M327', 0, $insert,false);			
				
				if($service->isError())
				{
					$this->setError();
					$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
				}
				
				if(!$this->isError())
				{
					$this->setMessage('Tạo bảng lương thành công!');
				}
			}
		}
		else 
		{
			$this->setError();
		}
	}
	
	private function onValidate($month, $year)
	{
		$retval = true;
		$sql    = sprintf('select 1 
		                   from OBangLuong as bl
		                   inner join qsiforms as qsi
		                   on bl.IFID_M327 = qsi.IFID
						   where
						   qsi.Status = 2
						   and bl.Nam = %2$d 
						   and bl.Thang = %1$d', $month, $year);
		$dataSql = $this->_db->fetchOne($sql);
		$retval  = $dataSql?false:true;
		if(!$retval)
		{
			$this->setMessage('Bảng lương tháng '.$month.' năm '.$year.' đã được tính và phê duyệt trước đó' );
		}
		return $retval;
	}
}
?>