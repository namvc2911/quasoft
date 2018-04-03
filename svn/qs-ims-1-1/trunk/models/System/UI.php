<?php
/**
 * System form model
 *
 * @author HuyBD
 *
 */
class Qss_Model_System_UI extends Qss_Model_Abstract
{
	public function __construct ()
	{
		parent::__construct();
	}
	public function getFormUIConfig($objid)
	{
		$sql = sprintf('select qsobjects.*,qsfields.*,t.*
					from qsfields
					inner join qsobjects on qsobjects.ObjectCode = qsfields.ObjectCode 
					left join 
					(select qsuigroups.GroupNo,qsuigroups.Name,qsuigroups.ObjectCode as ocode,qsuiboxs.*,qsuiboxfields.FieldCode as code
					from qsuiboxfields 
					inner join qsuiboxs on qsuiboxs.UIBID = qsuiboxfields.UIBID
					inner join qsuigroups on qsuiboxs.UIGID = qsuigroups.UIGID
					) as t on t.ocode = qsfields.ObjectCode and t.code = qsfields.FieldCode
					where qsfields.ObjectCode = "%1$s" and Effect=1
					order by COALESCE(GroupNo,999999),BoxNo,BoxType,FieldNo',
			$objid);
		return $this->_o_DB->fetchAll($sql);
	}
	public function getGroupByObjID($objid)
	{
		$sql = sprintf('select * from qsuigroups
					where ObjectCode = "%1$s"
					order by GroupNo',
			$objid);
		return $this->_o_DB->fetchAll($sql);
	}
	public function getGroupByID($gid)
	{
		$sql = sprintf('select * from qsuigroups
					where UIGID = %1$d',
		$gid);
		return $this->_o_DB->fetchOne($sql);
	}
	public function saveGroup($params)
	{
		$id = (int) $params['uigid'];
		$data = array('GroupNo'=>$params['intGroupNo'],
				'Name'=>$params['szName'],
				'ObjectCode'=>$params['objid']);
		$lang = new Qss_Model_System_Language();
		$languages = $lang->getAll(1,false);
		foreach ($languages as $item)
		{
			$data['Name_'.$item->Code] = @$params['szName_'.$item->Code];
		}
		if($id)
		{
			$sql = sprintf('update qsuigroups set %1$s where UIGID = %2$d',
			$this->arrayToUpdate($data),$id);
		}
		else
		{
			$sql = sprintf('insert into qsuigroups%1$s',
			$this->arrayToInsert($data));
		}
		return $this->_o_DB->execute($sql);
	}
	public function getBoxByGID($gid)
	{
		$sql = sprintf('select * from qsuiboxs
					where UIGID = %1$d',
		$gid);
		return $this->_o_DB->fetchAll($sql);
	}
	public function getBoxByID($bid)
	{
		$sql = sprintf('select * from qsuiboxs
					where UIBID = %1$d',
		$bid);
		return $this->_o_DB->fetchOne($sql);
	}
	public function saveBox($params)
	{
		$id = (int) $params['uibid'];
		$data = array('BoxNo'=>$params['intBoxNo'],
				'Title'=>$params['szTitle'],
				'UIGID'=>$params['gid'],
				'BoxType'=>$params['intBoxType'],
				'DisplayBorder'=>@$params['bBorder'],
				'Hidden'=>@$params['bHidden'],
				'DisplayTitle'=>@$params['bTitle']);
		$lang = new Qss_Model_System_Language();
		$languages = $lang->getAll(1,false);
		foreach ($languages as $item)
		{
			$data['Title_'.$item->Code] = @$params['szTitle_'.$item->Code];
		}
		if($id)
		{
			$sql = sprintf('update qsuiboxs set %1$s where UIBID = %2$d',
			$this->arrayToUpdate($data),$id);
			$this->_o_DB->execute($sql);
		}
		else
		{
			$sql = sprintf('insert into qsuiboxs%1$s',
			$this->arrayToInsert($data));
			$id = $this->_o_DB->execute($sql);
		}
		$sql = sprintf('delete from qsuiboxfields where UIBID = %1$d',
			$id);
		$this->_o_DB->execute($sql);
		$fields = @$params['aFields'];
		if(is_array($fields))
		{
			foreach ($fields as $item)
			{
				$sql = sprintf('insert into qsuiboxfields(UIBID,FieldCode) values(%1$d,"%2$s")',
				$id,$item);
				$this->_o_DB->execute($sql);
			}
		}
		return $id;
	}
	public function getFieldByBox($objid,$bid)
	{
		$sql = sprintf('select qsfields.*,qsuiboxfields.UIBID from qsfields
					left join qsuiboxfields on qsuiboxfields.FieldCode = qsfields.FieldCode and qsuiboxfields.UIBID=%2$d
					where qsfields.ObjectCode = "%1$s" and Effect=1
					and qsfields.FieldCode not in(select FieldCode from qsuiboxfields 
					inner join qsuiboxs on qsuiboxs.UIBID = qsuiboxfields.UIBID
					inner join qsuigroups on qsuigroups.UIGID = qsuiboxs.UIGID
					where qsuiboxfields.UIBID != %2$d and ObjectCode = "%1$s")
					order by FieldNo',
			$objid,$bid);
		return $this->_o_DB->fetchAll($sql);
	}
}
?>