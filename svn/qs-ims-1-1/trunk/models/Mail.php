<?php
/**
 * Model for instanse form
 *
 */
class Qss_Model_Mail extends Qss_Model_Abstract
{

	public static $arrType = array(1=>'GMail',2=>'Yahoo Mail',3=>'Khác');
	/**
	 * Working with all design of form that user acess via module management
	 *'
	 * @access  public
	 */
	public function __construct ()
	{
		parent::__construct();

	}
	public function getAllAccount($uid)
	{
		$sql = sprintf('select *,case when UID = %1$d then 1 else 2 end as Rights 
			from qsmailaccounts
			where UID = %1$d or UID = -1',$uid);
		return $this->_o_DB->fetchAll($sql);
	}
	public function getAccountById ($id)
	{
		$sql = sprintf('select * from qsmailaccounts where MAID = %1$d',
		$id);
		return $this->_o_DB->fetchOne($sql);
	}

	public function getCheckRights($uid,$maid)
	{
		$sql = sprintf('select case when UID = %2$d then 1 else 2 end as Rights
					from qsmailaccounts 
					where (UID = %2$d or UID = -1) and MAID = %1$d',$maid,$uid);
		$dataSQL =  $this->_o_DB->fetchOne($sql);
		return $dataSQL?$dataSQL->Rights:0;
	}
	public function saveAccount($params)
	{
		$data = array('MAID'=>$params['id'],
						'Type'=>$params['type'],
						'Server'=>$params['server'],
						'Name'=>$params['name'],
						'UID'=>$params['uid'],
						'Port'=>$params['port'],
						'Signature'=>$params['signature'],
						'Account'=>$params['account'],
						'Password'=>base64_encode($params['password']),
						'SSLOption'=>$params['ssl']);
		if($params['id'])
		{
			$sql = sprintf('update qsmailaccounts set %1$s where MAID = %2$d',
			$this->arrayToUpdate($data),$params['id']);
		}
		else
		{
			$sql = sprintf('insert into qsmailaccounts%1$s',$this->arrayToInsert($data));
		}
		return $this->_o_DB->execute($sql);
	}
	public function deleteAccount($id)
	{
		$sql = sprintf('delete from qsmailaccounts where MAID = %1$d',$id);
		return $this->_o_DB->execute($sql);
	}
	public function getAllList($uid)
	{
		$sql = sprintf('select *,case when UID = %1$d then 1 else 2 end as Rights from qsmaillists
			where UID = %1$d or UID = -1',$uid);
		return $this->_o_DB->fetchAll($sql);
	}
	public function getListById ($id)
	{
		$sql = sprintf('select * from qsmaillists where MLID = %1$d',
		$id);
		return $this->_o_DB->fetchOne($sql);
	}

	public function getCheckListRights($uid,$maid)
	{
		$sql = sprintf('select case when UID = %2$d then 1 else 2 end as Rights
					from qsmaillists
					where (UID = %2$d or UID = -1) and MLID = %1$d',$maid,$uid);
		$dataSQL =  $this->_o_DB->fetchOne($sql);
		return $dataSQL?$dataSQL->Rights:0;
	}
	public function saveList($params)
	{
		$data = array('MLID'=>$params['id'],
						'Name'=>$params['name'],
						'Description'=>$params['desc'],
						'UID'=>$params['uid']);
		if($params['id'])
		{
			$sql = sprintf('update qsmaillists set %1$s where MLID = %2$d',
			$this->arrayToUpdate($data),$params['id']);
		}
		else
		{
			$sql = sprintf('insert into qsmaillists%1$s',$this->arrayToInsert($data));
		}
		return $this->_o_DB->execute($sql);
	}
	public function deleteList($id)
	{
		$sql = sprintf('delete from qsmaillists where MLID = %1$d',$id);
		return $this->_o_DB->execute($sql);
	}
	public function getFormRefer($id)
	{
		$sql = sprintf('select distinct qsforms.v,qsobjects.ObjID,Name,ObjectName from qsmaillistrefer
					inner join qsiforms on qsiforms.IFID =  qsmaillistrefer.IFID
					inner join qsforms on qsforms.FormCode = qsiforms.FormCode
					inner join qsiobjects on qsiobjects.IOID =  qsmaillistrefer.IOID
					inner join qsobjects on qsobjects.ObjectCode = qsiobjects.ObjectCode
					where MLID = %1$d',$id);
		return $this->_o_DB->fetchAll($sql);
	}
	public function getReferByFID($id,$fid)
	{
		$sql = sprintf('select qsmaillistrefer.*,qsiforms.DepartmentID from qsmaillistrefer
					inner join qsiforms on qsiforms.IFID =  qsmaillistrefer.IFID
					where MLID = %1$d and FormCode = "%2$s"',$id,$fid);
		return $this->_o_DB->fetchAll($sql);
	}
	public function saveRefer($mlid,$ifid,$ioid)
	{
		$dataRefer = array(
					'MLID'=>$mlid,
					'IFID'=>$ifid,
					'IOID'=>$ioid
		);
		$sql = sprintf('replace into qsmaillistrefer%1$s',$this->arrayToInsert($dataRefer));
		$this->_o_DB->execute($sql);
	}
	public function deleteRefer($mlid,$ifid,$ioid)
	{
		$sql = sprintf('delete from qsmaillistrefer where MLID = %1$d and IFID = %2$d and IOID = %3$d',
		$mlid,$ifid,$ioid);
		$this->_o_DB->execute($sql);
	}
	function getMailListById($id)
	{
		$sql = sprintf('select qsmaillistrefer.*, datmail.* from datmail
					inner join qsrecobjects on datmail.ID =  qsrecobjects.RecordID
					inner join qsrecforms on qsrecforms.IOID = qsrecobjects.IOID
					inner join qsmaillistrefer on qsmaillistrefer.IFID = qsrecforms.IFID and qsmaillistrefer.IOID = qsrecforms.IOID
					where DataFieldType = 12 and qsmaillistrefer.MLID = %1$d',$id);
		return $this->_o_DB->fetchAll($sql);
	}
	function getReferChoise($user)
	{
		$sql = sprintf('select distinct qsforms.*,qsobjects.* from qsfobjects
						inner join qsobjects on qsobjects.ObjID = qsfobjects.ObjID
						inner join qsforms on qsforms.FormCode = qsfobjects.FormCode
						inner join qsfields on qsfields.ObjID = qsobjects.ObjID
						inner join qsuserforms on qsuserforms.FormCode=qsforms.FormCode
						where qsuserforms.GroupID in(%1$s) and qsuserforms.Rights&63<>0 and qsfields.FieldType = 12',
						$user->user_group_list);
		return $this->_o_DB->fetchAll($sql);
	}
	public function logMail($subject,$body,$to,$cc,$bcc,$attachments)
	{
		$data = array('Subject'=>$subject,
					'Body'=>$body,
					'To'=>$to,
					'Cc'=>$cc,
					'Bcc'=>$bcc,
					'Attachments'=>$attachments,
					'Status'=>0);
		$sql = sprintf('insert into qsmaillogs%1$s',$this->arrayToInsert($data));
		$this->_o_DB->execute($sql);
	}
	public function logSend($id,$status,$error = null)
	{
		$data = array(
					'Status'=>$status,
					'ErrorMessage'=>$error,
					'SendDate'=>date('Y-m-d H:i:s'));
		$sql = sprintf('update qsmaillogs set %1$s where MLID = %2$d',
				$this->arrayToUpdate($data),$id);
		$this->_o_DB->execute($sql);
	}
	
	public function getLogs($pageno = 1)
	{
	    $sql = sprintf('select * from qsmaillogs order by MLID desc limit %1$d,20',($pageno-1) * 20);
	    return $this->_o_DB->fetchAll($sql);
	}	
	
	public function getLogByID($logID)
	{
	    $sql = sprintf('select * from qsmaillogs where MLID = %1$d', $logID);
	    return $this->_o_DB->fetchOne($sql);	    
	}
	
	public function countPage()
	{
	    $sql = sprintf('select count(*)/20 as count from qsmaillogs');
	    return $this->_o_DB->fetchOne($sql);
	}
	
	public function deleteLog($arr)
	{
	    $sql = sprintf('delete from qsmaillogs where MLID in (%1$s)',implode(',',$arr));
	    $this->_o_DB->execute($sql);
	}
	
	public function deleteAllLog()
	{
	    $sql = sprintf('delete from qsmaillogs');
	    $this->_o_DB->execute($sql);
	}	
}
?>