<?php
class Qss_Model_M316_Main extends Qss_Model_Abstract
{
	public function getEmployee($departmentid = 0)
    {
    	$sqlwhere = '';
    	if($departmentid)//@todo: phải join với OPhongBan để lấy cả con 
    	{
    		$sqlwhere .= sprintf(' and Ref_MaPhongBan = %1$d'
					,$departmentid);
    	}
    	if(Qss_Lib_System::formSecure('M319'))
		{
			$sqlwhere .= sprintf(' and pb.IFID_M319 in (select IFID from qsrecordrights where FormCode = "M319" and UID=%1$d)'
					,$this->_user->user_id);
		}
        
        $sql = sprintf('
			SELECT
				nv.*,pb.TenPhongBan
			FROM ODanhSachNhanVien as nv
			inner join OPhongBan as pb on pb.IOID = nv.Ref_MaPhongBan 
			WHERE 1=1 %1$s
			order by pb.lft, nv.MaNhanVien'
            , $sqlwhere);
        return $this->_o_DB->fetchAll($sql);
    }
   public function getEmployeeByStatus($departmentid = 0,$moiLamViec = 0,$daNghi =0,$dangLamViec =0 )
   {
      $sqlPhongBan = sprintf('
          SELECT * FROM OPhongBan INNER JOIN ODanhSachNhanVien ON OPhongBan.IOID = %1$d 
           ',$departmentid);
   		$phongBan = $this->_o_DB->fetchOne($sqlPhongBan);
      
        $where = '';
      if($daNghi == 1 ){
        $where .= 'AND IFNULL(ODanhSachNhanVien.ThoiViec,0) = 1';
      }
      if($dangLamViec == 1 )
      {
        $where .= 'And IFNULL(ODanhSachNhanVien.ThoiViec,0) = 0';
      }
      if($moiLamViec == 1){
        $where .= 'And IFNULL(ODanhSachNhanVien.ThoiViec,0) = 0  AND TIMESTAMPDIFF(MONTH, odanhsachnhanvien.NgayVaoLam,CURDATE()) <= 2 ' ;
      }
      
   		$sql = sprintf('
        SELECT ODanhSachNhanVien.*,OPhongBan.TenPhongBan 
        FROM ODanhSachNhanVien

        INNER JOIN OPhongBan ON ODanhSachNhanVien.Ref_MaPhongBan = OPhongBan.IOID 
        WHERE
         (OPhongBan.lft >= %1$d) AND (OPhongBan.rgt <= %2$d)  %3$s'

         ,$phongBan->lft,$phongBan->rgt,$where);

      return $this->_o_DB->fetchAll($sql);

   }
   public function countEmployeeByPosition()
   {
      $sql = sprintf('SELECT ODanhSachNhanVien.*,COUNT(*) As SoLuong FROM ODanhSachNhanVien GROUP BY ODanhSachNhanVien.Ref_ChucVu');
      return $this->_o_DB->fetchAll($sql);
   }
   public function countEmployeeByDepartment()
   {

   //    $sqlPhongBan = sprintf('SELECT * 
   //      From OPhongBan   
   // ');
   //    $phongBan = $this->_o_DB->fetchAll($sqlPhongBan);
   //    foreach ($phongBan as $item) {
   //       $sql = sprintf('SELECT ODanhsachnhanvien.Ref_MaPhongBan,OPhongBan.TenPhongBan,COUNT(*) As SoLuong  
   //      FROM ODanhSachNhanVien
   //      INNER JOIN OPhongBan ON ODanhSachNhanVien.Ref_MaPhongBan = OPhongBan.IOID
   //      WHERE
   //       (OPhongBan.lft >= %1$d) AND (OPhongBan.rgt <= %2$d)
   //      GROUP BY ODanhsachnhanvien.Ref_MaPhongBan
   //      ',$item->lft,$item->rgt);
   //    }
    
      $sql = sprintf('SELECT PhongBanHienTai.*, 
    sum(case when ODanhSachNhanVien.IOID is null  then 0 else 1 end) AS SoLuong 
    FROM OPhongBan AS PhongBanHienTai 
    INNER JOIN OPhongBan AS PhongBanBenDuoi ON PhongBanBenDuoi.lft >= PhongBanHienTai.lft AND PhongBanBenDuoi .rgt <= PhongBanHienTai.rgt 
    LEFT JOIN ODanhSachNhanVien ON PhongBanBenDuoi.IOID = ODanhSachNhanVien.Ref_MaPhongBan WHERE PhongBanHienTai.Loai = "E_DEPARTMENT" 
    GROUP BY PhongBanHienTai.IOID');



       return $this->_o_DB->fetchAll($sql);
   }
   public function countEmployeeByTitle()
   {
      $sql = sprintf('SELECT ODanhSachNhanVien.ChucDanh,ODanhSachNhanVien.Ref_ChucDanh,COUNT(*) As SoLuong FROM ODanhSachNhanVien GROUP BY ODanhSachNhanVien.Ref_ChucDanh');
      return $this->_o_DB->fetchAll($sql);
   }
}