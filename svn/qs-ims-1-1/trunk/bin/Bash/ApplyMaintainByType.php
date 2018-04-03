<?php
/**
 * Class Qss_Bin_Bash_ApplyMaintainByType
 * Cập nhật bảo trì định kỳ, chu kỳ bảo trì (M724) theo loại thiết bị. Do 1 loại thiết bị sẽ có chu kỳ và công việc giống
 * nhau làm riêng cho TTCO
 */
class Qss_Bin_Bash_ApplyMaintainByType extends Qss_Lib_Bin {
	public function __doExecute() {
	    $user        = Qss_Register::get('userinfo'); // Lấy user đăng nhập hệ thống
        $arrIFID     = array();
        $sqlIFID     = '';
        $sqlBTDK     = '';
        $sqlChuKy    = '';
        $sqlCongViec = '';
        $first       = time();

		//Create inique id as IFID and add to array
		foreach ($this->_params->OChuKyLoaiThietBi as $chuky) {
			//update exists
			$sql = sprintf('
                UPDATE OBaoTriDinhKy AS btdktontai
                INNER JOIN OChuKyBaoTri AS chuky ON chuky.IFID_M724 = btdktontai.IFID_M724
                INNER JOIN ODanhSachThietBi AS dstb ON btdktontai.Ref_MaThietBi = dstb.IOID 
                    AND btdktontai.Ref_LoaiBaoTri = %2$d 
                    AND ifnull(btdktontai.Ref_BoPhan,0) = 0
                SET chuky.GiaTri = %3$d
                    , chuky.ChuKy = "%3$d giờ"
                    , btdktontai.MucDoUuTien = %4$s
                    , btdktontai.Ref_MucDoUuTien = %5$d
                WHERE dstb.Ref_LoaiThietBi = %1$d'
                , $this->_params->IOID
                , $chuky->Ref_LoaiBaoTri
                , $chuky->SoGio
                , $this->_db->quote($chuky->MucDoUuTien)
                , $chuky->Ref_MucDoUuTien
			);
			$this->_db->execute($sql);

			$sql = sprintf('
                SELECT 
                    dstb.MaThietBi AS MTB
                    , dstb.IOID AS Ref_MTB
                    , dstb.TenThietBi AS TTB
                    , dstb.IOID AS Ref_TTB
                    , dvbt.IOID AS Ref_DV
                    , dvbt.Ten AS DV
                    , dvbt.DeptID AS newDeptID
                FROM ODanhSachThietBi AS dstb
                INNER JOIN (SELECT * FROM ODonViSanXuat group by DeptID) AS dvbt ON dvbt.DeptID = dstb.DeptID
                LEFT JOIN OBaoTriDinhKy AS btdktontai ON btdktontai.Ref_MaThietBi = dstb.IOID 
                    AND btdktontai.Ref_LoaiBaoTri = %2$d 
                    AND ifnull(btdktontai.Ref_BoPhan,0) = 0
                WHERE dstb.Ref_LoaiThietBi = %1$d AND btdktontai.IOID IS NULL'
                , $this->_params->IOID
                , $chuky->Ref_LoaiBaoTri
			);
			$thietbiData =  $this->_db->fetchAll($sql);

			if(!count($thietbiData)) {
				continue;
			}

			foreach ($thietbiData as $item) {
				$ifid      = 'IFID_' . $first++;
				$arrIFID[] = $ifid;

				if($sqlIFID != '') {
					$sqlIFID .= ',';
				}
				$sqlIFID .= sprintf('(%1$d,%2$d,0,"M724",%3$d,0)', time(), $user->user_id, $item->newDeptID);

				if($sqlBTDK != '') {
					$sqlBTDK .= ',';
				}
				$sqlBTDK .= sprintf(
				    '(%15$s,%16$d,%1$s,%2$d,%3$s,%4$d,%5$s,%6$d,%7$s,%8$d,%9$d
				        ,%10$d,%11$d,%12$s,%13$s,%14$s,%17$s,%18$d)'
				, $this->_db->quote($item->MTB)
				, $item->Ref_MTB
				, $this->_db->quote($item->TTB)
				, $item->Ref_TTB
				, $this->_db->quote($chuky->LoaiBaoTri)
				, $chuky->Ref_LoaiBaoTri
				, $this->_db->quote($chuky->MucDoUuTien)
				, $chuky->Ref_MucDoUuTien
				, 1
				, 60
				, 0
				, date('Y-m-d')//$this->_db->quote($item->NgayBatDau)
				, 'null'//$this->_db->quote($item->NgayKetThuc)
				, 'null'//$this->_db->quote($item->MoTa)
				, $ifid
				, $item->newDeptID
				, $this->_db->quote($item->DV)
				, $item->Ref_DV);
				
				if($sqlChuKy != '') {
					$sqlChuKy .= ',';
				}

				$sqlChuKy .= sprintf(
				    '(%16$s,%17$d,%1$d,%2$s,%3$d,%4$d,%5$s,%6$d,%7$s,%8$d,%9$s,%10$d
				        ,%11$s,%12$d,%13$d,%14$s,%15$d)'
                    , 1//$subitem->CanCu
                    ,'null'// $this->_db->quote($subitem->KyBaoDuong)
                    , 0//$subitem->Ref_KyBaoDuong
                    , 1//$subitem->LapLai
                    , 'null'//$this->_db->quote($subitem->Thu)
                    , 0//$subitem->Ref_Thu
                    , 'null'//$this->_db->quote($subitem->Ngay)
                    , 0//$subitem->Ref_Ngay
                    , 'null'//$this->_db->quote($subitem->Thang)
                    , 0//$subitem->Ref_Thang
                    , $this->_db->quote('Giờ')//$this->_db->quote($subitem->ChiSo)
                    , 16114//$subitem->Ref_ChiSo
                    , $chuky->SoGio//$subitem->GiaTri
                    , $this->_db->quote($chuky->SoGio.' giờ')//$this->_db->quote($subitem->ChuKy)
                    , 1//$subitem->DieuChinhTheoPBT
                    , $ifid
                    , $item->newDeptID);

				/*$sqlCongViec = '';//1.Ten,2.Ref_Ten,3.MoTa,4.ThoiGian,5.NhanCong
				foreach ($this->_params->OCongViecBT as $item)
				{
					if($sqlCongViec != '')
					{
						$sqlCongViec .= ',';
					}
					$sqlCongViec .= sprintf('(%6$s,%7$d,%1$s,%2$d,%3$s,%4$d,%5$d)'
					, $this->_db->quote($subitem->Ten)
					, $subitem->Ref_Ten
					, $this->_db->quote($subitem->MoTa)
					, $subitem->ThoiGian
					, $subitem->NhanCong
					, $ifid
					, $item->newDeptID);
				}*/
			}

			//insert into qsiforms
			$sql = sprintf('
                insert into qsiforms(SDate,UID,Status,FormCode,DepartmentID,deleted) values%1$s'
                ,$sqlIFID
            );

			$lastifid   = $this->_db->execute($sql);
			$arrReplace = array();

			for($i=$lastifid;$i < ($lastifid + count($arrIFID));$i++) {
				$arrReplace[] = $i;
			}

			$sqlBTDK  = str_replace($arrIFID, $arrReplace, $sqlBTDK);
			$sqlChuKy = str_replace($arrIFID, $arrReplace, $sqlChuKy);
			//$sqlCongViec = str_replace($arrIFID, $arrReplace, $sqlCongViec);

			if($sqlBTDK) {
				$sql = sprintf('
                    insert into 
                        OBaoTriDinhKy(
                            IFID_M724
                            , DeptID
                            , MaThietBi
                            , Ref_MaThietBi
                            , TenThietBi
                            , Ref_TenThietBi
                            , LoaiBaoTri
                            , Ref_LoaiBaoTri
                            , MucDoUuTien
                            , Ref_MucDoUuTien
                            , SoNgay
                            , SoPhut
                            , DungMay
                            , NgayBatDau
                            , NgayKetThuc
                            , MoTa
                            , DVBT
                            , Ref_DVBT)
						values%1$s',$sqlBTDK);
				//echo $sql;die;
				$this->_db->execute($sql);
			}

			if($sqlChuKy) {
				$sql = sprintf('
                    insert into OChuKyBaoTri(
                        IFID_M724
                        , DeptID
                        , CanCu
                        , KyBaoDuong
                        , Ref_KyBaoDuong
                        , LapLai
                        , Thu
                        , Ref_Thu
                        , Ngay
                        , Ref_Ngay
                        , Thang
                        , Ref_Thang
                        , ChiSo
                        , Ref_ChiSo
                        , GiaTri
                        , ChuKy
                        , DieuChinhTheoPBT)
						values%1$s',$sqlChuKy);
				//echo $sql;die;
				$this->_db->execute($sql);
			}
		}
		//loop and add insert statment into string
		//generate inert into qsiforms string
		//execute qsiforms insertment and return last id
	}

}