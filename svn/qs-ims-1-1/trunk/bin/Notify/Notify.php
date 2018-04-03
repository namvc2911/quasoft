<?php
class Qss_Bin_Notify_Notify
{
    protected $_db;

    protected $_language;
    
    public function __construct()
	{ 
		$this->_db = Qss_Db::getAdapter('main');
		$path = '';
		$lang = Qss_Translation::getInstance()->getLanguage();
		$reflector = new ReflectionClass($this);
		$filename = $reflector->getFileName();
		$path = dirname($filename);
		$file = basename($filename, ".php"); 
		$path =  $path.'/'.$file . '_' . $lang.  '.ini';
		$this->_language = Qss_Translation::getInstance()->getTranslation($path);
	}
	/*public function count(Qss_Model_UserInfo $user)
	{
		$retval = 0;
		if(Qss_Lib_System::formActive('C005'))
		{
			$sql = sprintf('select count(distinct MNotify.IFID_C005) as TongSo from MNotify
							inner join MNotifyUser on MNotifyUser.IFID_C005 = MNotify.IFID_C005
							inner join ODanhSachNhanVien on ODanhSachNhanVien.IOID =  MNotifyUser.Ref_MaNV
							left join qsfreader on qsfreader.IFID = MNotify.IFID_C005 and UID=%1$d
							where qsfreader.IFID is null and ODanhSachNhanVien.Ref_TenTruyCap=%1$d'
					,$user->user_id);
			$dateSQL = $this->_db->fetchOne($sql);
			if($dateSQL)
			{
				$retval = $dateSQL->TongSo;
			}
		}
		return $retval;
	}*/

	public function __call($name, $agrs)
	{
		if($name == '_translate')
		{
			return isset($this->_language[$agrs[0]])?$this->_language[$agrs[0]]:"UNKOWN_".$agrs[0];
		}
	}
	public function showNotify(Qss_Model_UserInfo $user)
	{
		$retval = array();

		if(Qss_Lib_System::formActive('M856'))
		{
			$sql = sprintf('
                SELECT 
                    DISTINCT OThongBao.IFID_M856 AS IFID
                    , OThongBao.TieuDe AS Notify
                    , 1 AS DepartmentID
                    , FROM_UNIXTIME(qsiforms.SDate) AS Date
                    ,qsiforms.FormCode
                FROM OThongBao
                INNER JOIN qsiforms ON qsiforms.IFID =  OThongBao.IFID_M856
                INNER JOIN ONhanVienNhanThongBao ON ONhanVienNhanThongBao.IFID_M856 = OThongBao.IFID_M856
                INNER JOIN ODanhSachNhanVien ON ODanhSachNhanVien.IOID =  ONhanVienNhanThongBao.Ref_MaNV
                LEFT  JOIN qsfreader ON qsfreader.IFID = OThongBao.IFID_M856 AND qsfreader.UID=%1$d
                WHERE 
                    qsiforms.deleted <> 1 and qsfreader.IFID IS NULL 
                    AND ODanhSachNhanVien.Ref_TenTruyCap = %1$d
                    AND qsiforms.UID <> %1$d
                LIMIT 20'
            ,$user->user_id);
			$retval =  $this->_db->fetchAll($sql);
		}
		if(Qss_Lib_System::formActive('M747'))
		{
			$sql = sprintf('
                SELECT 
                    DISTINCT OYeuCauBaoTri.IFID_M747 AS IFID
                    , concat(OYeuCauBaoTri.TenThietBi," (",OYeuCauBaoTri.MaThietBi,"): ",OYeuCauBaoTri.MoTa) AS Notify
                    , 1 AS DepartmentID
                    , concat(Ngay," ",ThoiGian) AS Date
                    ,qsiforms.FormCode
                FROM OYeuCauBaoTri
                INNER JOIN qsiforms ON qsiforms.IFID =  OYeuCauBaoTri.IFID_M747
                inner join ODanhSachThietBi on ODanhSachThietBi.IOID = OYeuCauBaoTri.Ref_MaThietBi  
                inner join OKhuVuc on OKhuVuc.IOID = ODanhSachThietBi.Ref_MaKhuVuc 
                where qsiforms.deleted <> 1 and qsiforms.Status = 1 and ODanhSachThietBi.Ref_MaKhuVuc in (SELECT IOID FROM OKhuVuc
						inner join qsrecordrights on OKhuVuc.IFID_M720 = qsrecordrights.IFID 
						WHERE UID = %1$d)
                LIMIT 20'
            ,$user->user_id);
			$retval = array_merge($retval, $this->_db->fetchAll($sql));
		}
		return $retval;
	}
	public function showMessage(Qss_Model_UserInfo $user)
	{
		/*$sql = sprintf('select qsfcomment.IFID,1 as DepartmentID, qsusers.UserName, Comment as Message
						,qsfcomment.Date,qsiforms.FormCode
							from qsfcomment
							inner join qsiforms on qsiforms.IFID = qsfcomment.IFID 
							inner join qsusers on qsusers.UID = qsfcomment.UID
							limit 20'
					,$user->user_id);*/
		$sql = sprintf('select  qsiforms.IFID,1 as DepartmentID, qsusers.UserName,	
				concat(qsstepapprover.Name," - ",qsforms.Name) as Message,
				qsiforms.FormCode,
				FROM_UNIXTIME(ifnull(qsiforms.LastModify,qsiforms.SDate)) as Date
					from qsstepapprover
					inner join qsstepapproverrights on qsstepapproverrights.SAID = qsstepapprover.SAID
					inner join qsworkflowsteps on qsworkflowsteps.SID = qsstepapprover.SID
					inner join qsworkflows on qsworkflows.WFID = qsworkflowsteps.WFID
					inner join qsiforms on qsiforms.FormCode = qsworkflows.FormCode and qsiforms.Status = qsworkflowsteps.StepNo
					inner join qsusers on qsusers.UID = qsiforms.UID 
					inner join qsforms on qsforms.FormCode = qsiforms.FormCode
					left join qsfapprover on qsfapprover.IFID = qsiforms.IFID and qsfapprover.UID = qsstepapproverrights.UID
					 and qsfapprover.StepNo = qsiforms.Status and qsfapprover.SAID = qsstepapprover.SAID
					where qsforms.Effected=1 and qsiforms.deleted <> 1 and qsworkflows.Actived = 1 and (qsfapprover.IFID is null or RDate is not null) and qsstepapproverrights.UID = %1$d
					limit 50
					'
					,$user->user_id);
		return $this->_db->fetchAll($sql);
	}
	public function showEvent(Qss_Model_UserInfo $user)
	{
		$retval = array();
		if(Qss_Lib_System::formActive('M759'))
		{
			$sql = sprintf('select distinct IFID_M759 as IFID, MoTa as Notify, 1 as DepartmentID,
							NgayBatDauDuKien as Date,
							ThoiGianBatDauDuKien as Time,qsiforms.FormCode
							from OPhieuBaoTri
							inner join qsiforms on qsiforms.IFID = OPhieuBaoTri.IFID_M759
							inner join ODanhSachNhanVien on ODanhSachNhanVien.IOID =  OPhieuBaoTri.Ref_NguoiThucHien
							where qsiforms.deleted <> 1 and ODanhSachNhanVien.Ref_TenTruyCap=%1$d and qsiforms.Status in (1,2)
							limit 20'
					,$user->user_id);
			$retval = $this->_db->fetchAll($sql);
		}
		if(Qss_Lib_System::formActive('M852'))
		{
			$sql = sprintf('select distinct IFID_M852 as IFID, TieuDe as Notify,qsiforms.DepartmentID,
							NgayKetThucKeHoach as Date,
							0 as Time,qsiforms.FormCode
							from ODanhSachCongViec
							inner join qsiforms on qsiforms.IFID = ODanhSachCongViec.IFID_M852  
							inner join ODanhSachNhanVien on ODanhSachNhanVien.IOID =  ODanhSachCongViec.Ref_GiaoCho
							where qsiforms.deleted <> 1 and ODanhSachNhanVien.Ref_TenTruyCap=%1$d and qsiforms.Status in (1,2)
							limit 20'
					,$user->user_id);
			$retval = array_merge($this->_db->fetchAll($sql),$retval);
		}
		return $retval;
	}
}
?>