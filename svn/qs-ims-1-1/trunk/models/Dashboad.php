<?php
/**
 * Model for instanse form
 *
 */
class Qss_Model_Dashboad extends Qss_Model_Abstract
{


	/**
	 * Working with all design of form that user acess via module management
	 *'
	 * @access  public
	 */
	public function __construct ()
	{
		parent::__construct();

	}
	public function getBlockByUID($uid)
	{
		$sql = sprintf('select B.*, case when A.BlockID is not null then 1 else 0 end as checked
									from qsblocks as B
									left join qsuserblocks as A on A.BlockID = B.BlockID
									and A.UID = %1$d
									order by Possition',$uid);
		return $this->_o_DB->fetchAll($sql);
	}
	
	public function getQuickAccess($user)
	{
		$lang = ($user->user_lang == 'vn')?'':'_'.$user->user_lang;
		$sql = sprintf('select A.FormCode,ifnull(A.Name%2$s,A.Name) as Name, A.Type, A.class ,B.* ,
                                        A.FormCode as FCode
									from qsforms as A
									inner join qsuserquick as B
									on A.FormCode = B.FormCode
									WHERE B.UID = %1$d  
									ORDER BY Position DESC
									',$user->user_id,$lang);
		return $this->_o_DB->fetchAll($sql);
	}

	public function  saveQuickAccess($fid, $uid, $check)
	{
		if($check)
		{
			$data = array('UID'=>$uid,'FormCode'=>$fid);
			$sql = sprintf('replace into qsuserquick%1$s',$this->arrayToInsert($data));
		}
		else
		{
			$sql = sprintf('delete from qsuserquick where UID = %1$d and FormCode = "%2$s"
									',$uid,$fid);
		}
		return $this->_o_DB->execute($sql);
	}
	public function saveDashboad($BlockID, $uid, $check)
	{
		if($check)
		{
			$data = array('UID'=>$uid,'BlockID'=>$BlockID);
			$sql = sprintf('replace into qsuserblocks%1$s',$this->arrayToInsert($data));
		}
		else
		{
			$sql = sprintf('delete from qsuserblocks where UID = %1$d and BlockID = %2$d
									',$uid,$BlockID);
		}
		return $this->_o_DB->execute($sql);
	}

	public function delQuickAccess($fid, $uid)
	{
		$sql = sprintf('delete from qsuserquick where UID = %1$d and FormCode = "%2$s"
									',$uid,$fid);

		return $this->_o_DB->execute($sql);
	}
	public function saveAllQuickAccess($uid, $check)
	{
		if($check)
		{
			$sql = sprintf('replace into qsuserquick(FormCode,UID) select FormCode, %1$d from qsforms',
				$uid);
		}
		else
		{
			$sql = sprintf('delete from qsuserquick where UID = %1$d
									',$uid);
		}
		return $this->_o_DB->execute($sql);
	}
	public function getAllModule(Qss_Model_UserInfo $user)
	{
		$lang = ($user->user_lang == 'vn')?'':'_'.$user->user_lang;
		$sz_SQL = sprintf('select distinct qsforms.FormCode,ifnull(Name%2$s,Name) as Name, Type, class,
						case when qsuserquick.FormCode is null then 0 else 1 end as checked
						from qsforms
						inner join qsuserforms on qsuserforms.FormCode=qsforms.FormCode
						left join qsuserquick on qsuserquick.FormCode = qsforms.FormCode and qsuserquick.UID = %3$d
                        where GroupID in(%1$s) and Rights&63<>0 and qsforms.Effected = 1', $user->user_group_list,$lang,$user->user_id);
                return $this->_o_DB->fetchAll($sz_SQL);
	}
	public function getReportModule(Qss_Model_UserInfo $user)
	{
		$lang = ($user->user_lang == 'vn')?'':'_'.$user->user_lang;
		$sz_SQL = sprintf('select distinct qsuserreports.URID,qsuserreports.Mobile,
							ifnull(qsforms.Name%1$s,qsforms.Name) as FName,
							ifnull(qsuserreports.Name%1$s,qsuserreports.Name) as Name,
							case when qsuserreport.UID is null then 0 else 1 end as checked,
							Params
							from qsuserreports
							inner join qsforms on qsforms.FormCode = qsuserreports.FormCode
							left join qsuserreport on qsuserreports.URID=qsuserreport.URID and qsuserreport.UID = %2$d
							inner join qsuserforms on qsuserforms.FormCode=qsforms.FormCode
							where GroupID in(%3$s) and Rights&63<>0
							order by qsuserreports.FormCode'
							,$lang,$user->user_id,$user->user_group_list);
		return $this->_o_DB->fetchAll($sz_SQL);
	}
	public function getReportsByForm($user,$fid)
	{
		$lang = ($user->user_lang == 'vn')?'':'_'.$user->user_lang;
		$sz_SQL = sprintf('select qsuserreports.*,
							ifnull(qsforms.Name%1$s,qsforms.Name) as FName,
							ifnull(qsuserreports.Name%1$s,qsuserreports.Name) as Name
							from qsuserreports
							inner join qsforms on qsforms.FormCode = qsuserreports.FormCode
							where qsuserreports.FormCode="%2$s"'
							,$lang,$fid);
		return $this->_o_DB->fetchAll($sz_SQL);
	}
	
	public function  saveQuickReport($urid, $uid, $check)
	{
		if($check)
		{
			$data = array('UID'=>$uid,'URID'=>$urid);
			$sql = sprintf('replace into qsuserreport%1$s',$this->arrayToInsert($data));
		}
		else
		{
			$sql = sprintf('delete from qsuserreport where UID = %1$d and URID = %2$d
									',$uid,$urid);
		}
		return $this->_o_DB->execute($sql);
	}
	
	public function getByPath($path)
	{
		$sql = sprintf('select * from qsuserreports where Params = %1$s;', $this->_o_DB->quote(trim($path)));
		return $this->_o_DB->fetchOne($sql);
	}
}
?>
