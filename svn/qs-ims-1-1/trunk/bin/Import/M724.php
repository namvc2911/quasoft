<?php
class Qss_Bin_Import_M724 extends Qss_Lib_Bin
{
	public function __doExecute($ioid = 0)
	{
        if($ioid != 0)
        {
            $ioid = (int)$ioid;

            $sql = "update OChuKyBaoTri
				inner join OBaoTriDinhKy on OBaoTriDinhKy.IFID_M724 = OChuKyBaoTri.IFID_M724
				set Thu = case when ifnull(Thu,0) = 0 and KyBaoDuong='W' then WEEKDAY(OBaoTriDinhKy.NgayBatDau) + 1 else Thu end 
				,Ngay = case when ifnull(Ngay,0) = 0 and (KyBaoDuong='M' or KyBaoDuong='Y') then DAY(OBaoTriDinhKy.NgayBatDau) else Ngay end
				,Thang = case when ifnull(Thang,0) = 0 and KyBaoDuong='Y' then MONTH(OBaoTriDinhKy.NgayBatDau) else Thang end";
            $this->_db->execute($sql);

            $sql = "update OChuKyBaoTri
			set ChuKy = case 
			when KyBaoDuong='D' then if(LapLai=1,'Hàng ngày',concat(LapLai,' ngày một lần'))
			when KyBaoDuong='W' then if(LapLai=1,concat(Ref_Thu,' Hàng tuần'),concat(LapLai,' tuần một lần vào ',Ref_Thu))
			when KyBaoDuong='M' then if
			  (
			    LapLai=1
			        ,concat('Ngày ',Ngay,' hàng tháng')
			        ,concat(LapLai,' tháng một lần vào ngày ',IF(Ngay <= 10, concat('mồng ', Ngay), Ngay)
              )
              )
			when KyBaoDuong='Y' then if(LapLai=1,concat('Ngày ',Ngay,'/',Thang,' hàng năm'),concat(LapLai,' năm một lần vào ngày ',Ngay,'/',Thang))
			end
			where ifnull(OChuKyBaoTri.IOID, 0) = {$ioid}";
            $this->_db->execute($sql);
        }
        else
        {
            $sql = "update OChuKyBaoTri
				inner join OBaoTriDinhKy on OBaoTriDinhKy.IFID_M724 = OChuKyBaoTri.IFID_M724
				set Thu = case when ifnull(Thu,0) = 0 and KyBaoDuong='W' then WEEKDAY(OBaoTriDinhKy.NgayBatDau) + 1 else Thu end 
				,Ngay = case when ifnull(Ngay,0) = 0 and (KyBaoDuong='M' or KyBaoDuong='Y') then DAY(OBaoTriDinhKy.NgayBatDau) else Ngay end
				,Thang = case when ifnull(Thang,0) = 0 and KyBaoDuong='Y' then MONTH(OBaoTriDinhKy.NgayBatDau) else Thang end";
            $this->_db->execute($sql);

            $sql = "update OChuKyBaoTri
			set ChuKy = case 
			when KyBaoDuong='D' then if(LapLai=1,'Hàng ngày',concat(LapLai,' ngày một lần'))
			when KyBaoDuong='W' then if(LapLai=1,concat(Ref_Thu,' Hàng tuần'),concat(LapLai,' tuần một lần vào thứ ',Ref_Thu))
			when KyBaoDuong='M' then if(LapLai=1,concat('Ngày ',Ngay,' hàng tháng'),concat(LapLai,' tháng một lần vào ngày ',IF(Ngay <= 10, concat('mồng ', Ngay), Ngay)))
			when KyBaoDuong='Y' then if(LapLai=1,concat('Ngày ',Ngay,'/',Thang,' hàng năm'),concat(LapLai,' năm một lần vào ngày ',Ngay,'/',Thang))
			end
			";
            $this->_db->execute($sql);
        }

        // Cập nhật lại điều chỉnh theo phiếu bảo trì về 0 với trường hợp có nhiều chu kỳ bảo trì
        $sqlDieuChinhTheoPBT = sprintf('
            UPDATE  OChuKyBaoTri
            SET DieuChinhTheoPBT = 0
            WHERE IFID_M724 IN (
                SELECT IFID_M724
                FROM (
                    SELECT IFID_M724, count(1) AS Total 
                    FROM OChuKyBaoTri                    
                    GROUP BY IFID_M724
                    HAVING Total > 1
                ) AS t1
            ) AND IFNULL(DieuChinhTheoPBT, 0) = 1	
        ');
        $this->_db->execute($sqlDieuChinhTheoPBT);
	}
	
}