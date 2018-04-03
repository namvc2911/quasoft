<?php
/**
 * Model for instanse form
 *
 */
class Qss_Model_Document extends Qss_Model_Abstract
{

	protected $_defaulFolder = 'uploads';
	public static $arrField = array(1=>'Name',2=>'UID',3=>'CDate',4=>'Modify',5=>'Folder',6=>'Ext',7=>'Size');
	public static $arrGroup = array(1=>'Name',2=>'UserName',3=>'CDate',4=>'Modify',5=>'Folder');
	/**
	 * Working with all design of form that user acess via module management
	 *'
	 * @access  public
	 */
	public function __construct ()
	{
		parent::__construct();

	}
    
    public function getDocumentByIFID($ifid)
    {
        $sql = sprintf('
            SELECT fdoc.*, doctype.Code, doctype.Type
            FROM qsfdocuments AS fdoc
            INNER JOIN qsdocumenttype AS doctype ON fdoc.DTID = doctype.DTID
            WHERE fdoc.IFID = %1$d
        ', $ifid);
        return $this->_o_DB->fetchAll($sql);
    }
    
	public function getAll($uid,$currentpage = 1 , $limit = 20, $fieldorder, $ordertype='ASC',$groupby,$a_Filter)
	{
		$sqllimit = sprintf(' LIMIT %1$d,%2$d', (($currentpage?$currentpage:1) - 1) * $limit, $limit);
		$orderfields = array();
		if(isset(self::$arrField[$groupby]))
		{
			$orderfields[] = self::$arrField[$groupby] . ' ' . $ordertype;
		}
		if(isset(self::$arrField[$fieldorder]))
		{
			$orderfields[] = self::$arrField[$fieldorder] . ' ' . $ordertype;
		}
		else
		{
			$orderfields[] = 'case when Modify is not null then Modify else CDate end DESC';
		}
		$sqlorder = sprintf('order by %1$s',implode(',', $orderfields));
		$sqlwhere = '';
		if(is_array($a_Filter))
		{
			foreach ($a_Filter as $key=>$val)
			{
				if(isset(self::$arrField[$key]))
				{
					if($key == 3 || $key == 4)
					{
						$sqlwhere .= sprintf(' and cast(%1$s as date) = str_to_date(%2$s,\'%%d-%%m-%%Y\') ',self::$arrField[$key],$this->_o_DB->quote($val));
					}
					else
					{
						$sqlwhere .= sprintf(' and qsdocuments.%1$s like %2$s',self::$arrField[$key],$this->_o_DB->quote('%'.$val.'%'));
					}
				}
			}
		}
		$sql = sprintf('select qsdocuments.*,qsusers.UserName from qsdocuments
				inner join qsusers on qsusers.UID = qsdocuments.UID   
				where (qsdocuments.Public = 1 or qsdocuments.UID = %1$d or 
				DID in(select DID from qsdocmembers where UID = %1$d)) %2$s %3$s %4$s',
			$uid,
			$sqlwhere,
			$sqlorder,
			$sqllimit);
		return $this->_o_DB->fetchAll($sql);
	}
	public function getFolders($uid)
	{
		$sql = sprintf('select distinct qsdocuments.Folder from qsdocuments
				inner join qsusers on qsusers.UID = qsdocuments.UID   
				where (qsdocuments.Public = 1 or qsdocuments.UID = %1$d or DID in(select DID from qsdocmembers where UID = %1$d))',
				$uid);
		return $this->_o_DB->fetchAll($sql);
	}
	public function countAll($uid,$currentpage = 1 , $limit = 20, $fieldorder, $ordertype='ASC',$groupby,$a_Filter)
	{
		$sqlwhere = '';
		if(is_array($a_Filter))
		{
			foreach ($a_Filter as $key=>$val)
			{
				if(isset(self::$arrField[$key]))
				{
					if($key == 3 || $key == 4)
					{
						$sqlwhere .= sprintf(' and %1$s = str_to_date(%2$s,\'%%d-%%m-%%Y\') ',self::$arrField[$key],$this->_o_DB->quote($val));
					}
					else
					{
						$sqlwhere .= sprintf(' and qsdocuments.%1$s like %2$s',self::$arrField[$key],$this->_o_DB->quote('%'.$val.'%'));
					}
				}
			}
		}
		$sql = sprintf('select count(*) as count from qsdocuments
				inner join qsusers on qsusers.UID = qsdocuments.UID   
				where (qsdocuments.Public = 1 or qsdocuments.UID = %1$d or DID in(select DID from qsdocmembers where UID = %1$d)) %2$s',$uid,$sqlwhere);
		$dataSQL = $this->_o_DB->fetchOne($sql);
		return $dataSQL?$dataSQL->count:0;
	}
	public function getById ($id,$uid)
	{
		$sql = sprintf('select * from qsdocuments where DID = %1$d and
				(qsdocuments.Public <> 100 or UID = %2$d or DID in(select DID from qsdocmembers where UID = %2$d))',
		$id,$uid);
		return $this->_o_DB->fetchOne($sql);
	}
	public function getMembers($id)
	{
		$sql = sprintf('select * from qsdocmembers
					inner join qsusers on qsusers.UID = qsdocmembers.UID 
					where DID = %1$d ',
		$id);
		return $this->_o_DB->fetchAll($sql);
	}
	public function getCheckRights($uid,$docid)
	{
		$sql = sprintf('select case when qsdocuments.UID = %2$d then 1 else 2 end as Rights
					from qsdocuments 
					left join qsdocmembers on qsdocmembers.DID = qsdocuments.DID 
					where (qsdocuments.Public = 1 or qsdocuments.UID = %2$d or qsdocmembers.UID = %2$d) and qsdocuments.DID = %1$d',$docid,$uid);
		$dataSQL =  $this->_o_DB->fetchOne($sql);
		return $dataSQL?$dataSQL->Rights:0;
	}
	public function save($params)
	{
		$data = array('DocNo'=>$params['docno'],
						'Name'=>$params['name'],
						'Folder'=>$params['folder']?$params['folder']:$this->_defaulFolder,
						'UID'=>$params['uid'],
						'Size'=>$params['size'],
						'Public'=>(int)@$params['public']);
		$id = $params['id'];
		if($params['ext'])
		{
			$data['Ext']=$params['ext'];
		}
		if($id)
		{
			$data['Modify'] = date('Y-m-d H:i:s');
			$sql = sprintf('update qsdocuments set %1$s where DID = %2$d',
			$this->arrayToUpdate($data),$params['id']);
			$this->_o_DB->execute($sql);
		}
		else
		{
			$sql = sprintf('insert into qsdocuments%1$s',$this->arrayToInsert($data));
			$id = $this->_o_DB->execute($sql);
		}
		return $id;
	}
	public function emptyMember($id)
	{
		$sql = sprintf('delete from qsdocmembers where DID=%1$d',
				$id);
		$this->_o_DB->execute($sql);
	}
	public function saveMember($id,$uid)
	{
		$sql = sprintf('insert into qsdocmembers(DID,UID) values(%1$d,%2$d)',
		$id,$uid);
		return $this->_o_DB->execute($sql);
	}
	public function delete($id)
	{
		$sql = sprintf('delete from qsdocuments where DID = %1$d',
		$id);
		return $this->_o_DB->execute($sql);
	}
	public function searchTag($uid,$tag,$ifid = 0)
	{
		$where = '';
		if($ifid)
		{
			$where = sprintf(' and DID not in (select DID from qsfdocuments where IFID = %1$d) ',$ifid);
		}
		$sql = sprintf('select qsdocuments.*,qsusers.UserName from qsdocuments
				inner join qsusers on qsusers.UID = qsdocuments.UID   
				where (qsdocuments.Public = 1 or qsdocuments.UID = %1$d or DID in(select DID from qsdocmembers where UID = %1$d))
				and (Name like %2$s or Folder like %2$s) %3$s
				',$uid,$this->_o_DB->quote('%'.$tag.'%'),$where);
		return $this->_o_DB->fetchAll($sql);
	}
	public function attachForm($id,$ifid)
	{
		$sql = sprintf('insert into qsfdocuments(IFID,DID) values(%1$d,%2$d)',
		$ifid,$id);
		return $this->_o_DB->execute($sql);
	}
	public function getAttach($ifid,$currentpage = 1 , $limit = 20, $fieldorder, $ordertype='ASC',$groupby,$a_Filter)
	{
		$sqllimit = sprintf(' LIMIT %1$d,%2$d', (($currentpage?$currentpage:1) - 1) * $limit, $limit);
		$orderfields = array();
		if(isset(self::$arrField[$groupby]))
		{
			$orderfields[] = self::$arrField[$groupby] . ' ' . $ordertype;
		}
		if(isset(self::$arrField[$fieldorder]))
		{
			$orderfields[] = self::$arrField[$fieldorder] . ' ' . $ordertype;
		}
		else
		{
			$orderfields[] = 'Modify DESC';
		}
		$sqlorder = sprintf('order by %1$s',implode(',', $orderfields));
		$sqlwhere = '';
		if(is_array($a_Filter))
		{
			foreach ($a_Filter as $key=>$val)
			{
				if(isset(self::$arrField[$key]))
				{
					if($key == 3 || $key == 4)
					{
						$sqlwhere .= sprintf(' and cast(%1$s as date) = str_to_date(%2$s,\'%%d-%%m-%%Y\') ',self::$arrField[$key],$this->_o_DB->quote($val));
					}
					else
					{
						$sqlwhere .= sprintf(' and qsdocuments.%1$s like %2$s',self::$arrField[$key],$this->_o_DB->quote('%'.$val.'%'));
					}
				}
			}
		}
		$sql = sprintf('select qsdocuments.*,qsusers.UserName,IFID from qsdocuments
				inner join qsusers on qsusers.UID = qsdocuments.UID   
				inner join qsfdocuments on qsfdocuments.DID = qsdocuments.DID
				where qsfdocuments.IFID = %1$d %2$s %3$s %4$s',$ifid,$sqlwhere,$sqlorder,$sqllimit);
		return $this->_o_DB->fetchAll($sql);
	}
	public function countAttach($uid,$currentpage = 1 , $limit = 20, $fieldorder, $ordertype='ASC',$groupby,$a_Filter)
	{
		$sqlwhere = '';
		if(is_array($a_Filter))
		{
			foreach ($a_Filter as $key=>$val)
			{
				if(isset(self::$arrField[$key]))
				{
					if($key == 3 || $key == 4)
					{
						$sqlwhere .= sprintf(' and %1$s = str_to_date(%2$s,\'%%d-%%m-%%Y\') ',self::$arrField[$key],$this->_o_DB->quote($val));
					}
					else
					{
						$sqlwhere .= sprintf(' and qsdocuments.%1$s like %2$s',self::$arrField[$key],$this->_o_DB->quote('%'.$val.'%'));
					}
				}
			}
		}
		$sql = sprintf('select count(*) as count from qsdocuments
				inner join qsusers on qsusers.UID = qsdocuments.UID   
				inner join qsfdocuments on qsfdocuments.DID = qsdocuments.DID
				where qsfdocuments.IFID = %1$d %2$s',$uid,$sqlwhere);
		$dataSQL = $this->_o_DB->fetchOne($sql);
		return $dataSQL?$dataSQL->count:0;
	}
	public function deleteAttach($id,$ifid)
	{
		$sql = sprintf('delete from qsfdocuments where DID = %1$d and IFID = %2$d',
		$id,$ifid);
		return $this->_o_DB->execute($sql);
	}
	public function getFormRefer($id)
	{
		$sql = sprintf('select distinct qsforms.*
					from qsfdocuments
					inner join qsiforms on qsiforms.IFID =  qsfdocuments.IFID
					inner join qsforms on qsforms.FormCode= qsiforms.FormCode
					where DID = "%1$s"',$id);
		return $this->_o_DB->fetchAll($sql);
	}
	public function getByFID($id,$fid,$objid)
	{
		$sql = sprintf('select qsiforms.DepartmentID,v.IOID,qsfdocuments.*,qsusers.UserName
						from qsfdocuments 
						inner join qsiforms on qsiforms.IFID = qsfdocuments.IFID
						inner join qsusers on qsiforms.UID = qsusers.UID
						inner join %3$s as v on v.IFID_%1$s = qsiforms.IFID
						where DID = %2$d',$fid,$id,$objid);
		return $this->_o_DB->fetchAll($sql);
	}
}
?>