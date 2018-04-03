<?php
class Qss_Model_System_Object extends Qss_Model_Abstract
{

	public $ObjectCode;

	public $sz_Name;

	public $sz_Msg;
	
	public $b_Tree;
	
	public $orderField;
	
	public $orderType;

	//public $data;
	
	public $jsonData = false;

	//-----------------------------------------------------------------------
	/**
	* Build constructor
	*'
	* @return void
	*/
	public function __construct ()
	{
		parent::__construct();
	}

	//-----------------------------------------------------------------------
	/**
	* Get all system object
	*
	* @return void
	*/
	public function a_fGetAll ($sz_Search = '')
	{
		// ,concat(ObjectName,' - ',ObjectCode) as ObjectName
		$sql = 'select 
					Object.*
					, concat(Object.ObjectName,\' - \',Object.ObjectCode) as ObjectName
					, Object.ObjectName AS SimpleObjectName   
					, (
						SELECT GROUP_CONCAT(DISTINCT CONCAT(Form.FormCode, " - ", Form.Name) SEPARATOR "<br/>")
						FROM qsfobjects AS FObject
						INNER JOIN qsforms AS Form ON FObject.FormCode = Form.FormCode
						WHERE FObject.ObjectCode = Object.ObjectCode
						GROUP BY FObject.ObjectCode
					) AS FormCodes
				from qsobjects AS Object';
		if ( $sz_Search )
		{
			$sql .= sprintf(' where (ObjectName like %1$s or ObjectCode like %1$s)' 
					, $this->_o_DB->quote("%{$sz_Search}%"));
		}
		$sql .= ' order by ObjectName';
		return $this->_o_DB->fetchAll($sql);
	}

	//-----------------------------------------------------------------------
	/**
	* Init the system object
	*
	* @return void
	*/
	public function v_fInit ($ObjectCode)
	{
		$lang = Qss_Translation::getInstance()->getLanguage();
		$lang = ($lang=='vn')?'':'_'.$lang;
		$dataSQL = $this->getByCode($ObjectCode);
		if ( $dataSQL )
		{
			$this->ObjectCode = $dataSQL->ObjectCode;
			$this->sz_Name = $dataSQL->{"ObjectName$lang"}?$dataSQL->{"ObjectName$lang"}:$dataSQL->ObjectName;
			$this->b_Tree = @$dataSQL->NoTree?0:$dataSQL->tree;
			$this->orderField = $dataSQL->OrderField;
			$this->orderType = $dataSQL->OrderType;
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Save system object
	 *
	 * @return boolean
	 */
	public function b_fSave ($params)
	{
		$sql = sprintf('select * from qsobjects where ObjectCode="%1$s" and ObjectCode != "%2$s"',/* */
		//$this->_o_DB->quote($params['szName']),(ObjectName = %1$s or 
		$this->_o_DB->quote($params['szObjID']),/* */
		$params['ID']);
		$dataSQL = $this->_o_DB->fetchOne($sql);
		if($dataSQL)
		{
			return false;
		}
		$data = array(
					'ObjectName'=>$params['szName'],
					'ObjectCode'=>$params['szObjID'],
					'OrderField'=>$params['intOrderField'],
					'OrderType'=>$params['intOrderType']
		);
		$lang = new Qss_Model_System_Language();
		$languages = $lang->getAll(1,false);
		foreach ($languages as $item)
		{
			$data['ObjectName_'.$item->Code] = @$params['szName_'.$item->Code];
		}
		if($params['ID'])
		{
			$sql=sprintf('update qsobjects set %1$s
					where ObjectCode = "%2$s"',/* */
			$this->arrayToUpdate($data),$params['ID']);
			if($params['ID'] != $params['szObjID'])
			{
				$sqlUpdateFields = sprintf('update qsfields set ObjectCode=%1$s where ObjectCode=%2$s'
					,$this->_o_DB->quote($params['szObjID'])
					,$this->_o_DB->quote($params['ID']));
				$this->_o_DB->execute($sqlUpdateFields);
			}
		}
		else
		{
			$sql=sprintf('insert into qsobjects %1$s',/* */
			$this->arrayToInsert($data));
		}
		$this->_o_DB->execute($sql);
		
		return true;
	}
	public function a_fGetByForm($fid)
	{
		$sql = sprintf('select * from qsobjects where ObjectCode in (select ObjectCode from qsfobjects where FormCode = "%1$s")', $fid);
		return $this->_o_DB->fetchAll($sql);
	}
	public function a_fGetObjectByRef($objid)
	{
		$sql = sprintf('select * from qsobjects where ObjectCode in (select ObjectCode from qsfields where RefObjectCode="%1$s")
		union all 
		select * from qsobjects where ObjectCode in (select RefObjectCode from qsfields where ObjectCode="%1$s")', $objid);
		return $this->_o_DB->fetchAll($sql);
	}
	/**
	 * Delete system object
	 *
	 * @return boolean
	 */
	public function b_fDelete ()
	{
		
	}
	function createView()
	{
		
		$sql=sprintf('DROP VIEW IF EXISTS viewz_%1$s_1',$this->ObjectCode);
		$this->_o_DB->execute($sql);
		$sql=sprintf('DROP VIEW IF EXISTS viewz_%1$s_2',$this->ObjectCode);
		$this->_o_DB->execute($sql);
		//delete table
		$oldtable = sprintf('view_%1$s',$this->ObjectCode);
		if($this->_o_DB->tableExists($oldtable))
		{
			$sql=sprintf('RENAME TABLE %1$s TO %2$s',$oldtable,$this->ObjectCode);
			$this->_o_DB->execute($sql);
			$sql=sprintf('DROP TABLE IF EXISTS %1$s',$oldtable);
			$this->_o_DB->execute($sql);
		}
		$backuptable = $this->ObjectCode . '_Old';
		$backupoldtable = $this->ObjectCode . '_' .time();
		if($this->_o_DB->tableExists($backuptable))
		{
			$sql=sprintf('RENAME TABLE %1$s TO %2$s',$backuptable,$backupoldtable);
			$this->_o_DB->execute($sql);
		}
		if($this->_o_DB->tableExists($this->ObjectCode))
		{
			$sql=sprintf('RENAME TABLE %1$s TO %2$s',$this->ObjectCode,$backuptable);
			$this->_o_DB->execute($sql);
		}
		$sql=sprintf('select * from qsfields where ObjectCode="%1$s" and FieldType != 17 order by FieldNo',$this->ObjectCode);
		$dataSQLField = $this->_o_DB->fetchAll($sql);
		 
		
		$viewCreate = '';
		$viewInsert = '';
		$index = '';
		foreach($dataSQLField as $data)
		{
			switch($data->FieldType)
			{
				case 3:
					$viewCreate.=sprintf(', %1$s TEXT',$data->FieldCode);
					break;
				case 5:
					$viewCreate.=sprintf(', %1$s INT',$data->FieldCode);
					break;
				case 6:
					$precision = (int) $data->TValue;
					$precision = $precision?$precision:2;
					$viewCreate.=sprintf(', %1$s DECIMAL(24,%2$d)',$data->FieldCode,$precision);
					break;
				case 7:
					$viewCreate.=sprintf(', %1$s TINYINT',$data->FieldCode);
					break;
				case 10:
					$viewCreate.=sprintf(', %1$s DATE',$data->FieldCode);
					break;
				case 4:
					$viewCreate.=sprintf(', %1$s TIME',$data->FieldCode);
					break;
				case 11:
					$viewCreate.=sprintf(', %1$s BIGINT',$data->FieldCode);
					if(!$data->RefFieldCode)
					{
						$viewCreate.=sprintf(', Ref_%1$s varchar(10)',$data->FieldCode);
					}
					break;	
				case 18:
					$viewCreate.=sprintf(', %1$s DATETIME',$data->FieldCode);
					break;	
				default:
					$viewCreate.=sprintf(', %1$s varchar(255)',$data->FieldCode);
					break;
			}
			if($data->RefFieldCode || $data->FieldType == 14 || $data->FieldType == 16)
			{
				$viewCreate.=sprintf(', Ref_%1$s int',$data->FieldCode);
			}
			elseif(($data->InputType == 5 || $data->InputType == 3) && Qss_Json::decode($data->Regx) !== null)
			{
				$viewCreate.=sprintf(', Ref_%1$s varchar(100)',$data->FieldCode);
			}
		}

		/*Add more IFID for each FID*/
		$sql=sprintf('select * from qsfobjects
						inner join qsforms on qsforms.FormCode = qsfobjects.FormCode 
						where ObjectCode = "%1$s"',$this->ObjectCode);
		$dataSQL = $this->_o_DB->fetchAll($sql);
		$indexno = 0;
		$main = true; //for tree
		$fidname = ''; //for sub object tree   
		foreach ($dataSQL as $data)
		{
			$viewInsert.=sprintf('IFID_%1$s int,',$data->FormCode);
			if($indexno < 15)
			{
				$index .= sprintf(', KEY IFID_%1$s (IFID_%1$s)',$data->FormCode);
			}
			$indexno++;
			$main = $data->Main;
			$fidname = 'IFID_' . $data->FormCode;
			@unlink(QSS_DATA_DIR . '/views/edit/'.$data->FormCode.'.phtml');
			@unlink(QSS_DATA_DIR . '/views/detail/'.$data->FormCode.'.phtml');
			@unlink(QSS_DATA_DIR . '/views/search/'.$data->FormCode.'.phtml');
		}
		
		foreach ($dataSQLField as $data)
		{
			if($data->RefFieldCode && $data->RefObjectCode)
			{
				if($indexno < 15)
				{
					$index .= sprintf(', KEY Ref_%1$s (Ref_%1$s)',$data->FieldCode);
				}
				$indexno++;
			}
			if($data->Unique && $data->FieldType != 3)
			{
				if($indexno < 15)
				{
					$index .= sprintf(', KEY %1$s (%1$s)',$data->FieldCode);
				}
				$indexno++;
			}
		}
		$sql = sprintf('CREATE TABLE %1$s(%2$s IOID int NOT NULL AUTO_INCREMENT,DeptID int %3$s %5$s , PRIMARY KEY (IOID) %4$s) ENGINE=InnoDB',
				$this->ObjectCode,
				$viewInsert,
				$viewCreate,
				$index,
				($this->b_Tree || $this->b_Tree === '0')?',lft int null, rgt int null':'');//neu khong hoat dong thi cung generate lft va rgt 
		$this->_o_DB->execute($sql);
		@unlink(QSS_DATA_DIR . '/views/edit/object/'.$this->ObjectCode.'.phtml');
		$restore = array();
		if($this->_o_DB->tableExists($backuptable))
		{
			if($this->_o_DB->fieldExists($backuptable,'IOID'))
			{
				$restore['IOID'] = 'IOID';
			}
			foreach ($dataSQL as $data)
			{
				$fieldname = sprintf('IFID_%1$s',$data->FormCode);
				if($this->_o_DB->fieldExists($backuptable,$fieldname))
				{
					$restore[$fieldname] = $fieldname;
				}
			}
			if($this->_o_DB->fieldExists($backuptable,'DeptID'))
			{
				$restore['DeptID'] = 'DeptID';
			}
			foreach ($dataSQLField as $field)
			{
				if($this->_o_DB->fieldExists($backuptable,$field->FieldCode))
				{
					if($field->FieldType == 10)
					{
						$restore[$field->FieldCode] = sprintf('if(TIMESTAMP(%1$s)=0,null,%1$s)',$field->FieldCode);  	
					}
					else
					{
						$restore[$field->FieldCode] = $field->FieldCode;
					}
				}
				if($this->_o_DB->fieldExists($backuptable,'Ref_'.$field->FieldCode) 
					&& $this->_o_DB->fieldExists($this->ObjectCode,'Ref_'.$field->FieldCode))
				{
					if($field->FieldType == 8 || $field->FieldType == 9)
					{
						$restore[$field->FieldCode] = sprintf('concat(Ref_%1$s,%1$s)',$field->FieldCode);
					}
					else 
					{
						$restore['Ref_'.$field->FieldCode] = 'Ref_'.$field->FieldCode;
					}
				}
			}
			if($this->b_Tree && $this->_o_DB->fieldExists($backuptable,'lft'))
			{
				$restore['lft'] = 'lft';
			}
			if($this->b_Tree && $this->_o_DB->fieldExists($backuptable,'rgt'))
			{
				$restore['rgt'] = 'rgt';
			}
		}
		if(count($restore))
		{
			$sql = sprintf('insert into %1$s(%2$s) select %3$s from %4$s',
						$this->ObjectCode,
						implode(',', array_keys($restore)),
						implode(',', $restore),
						$backuptable);
			$this->_o_DB->execute($sql);
		}
		if($this->b_Tree)
		{
			$this->createTree($main,$fidname);
		}
		return true;
	}
	//-----------------------------------------------------------------------
	function getObjIDByName ($code)
	{
		$sql = "select * from qsobjects
                where ObjectCode= '{$code}'";
		$dataSQL = $this->_o_DB->fetchOne($sql);
		if ( $dataSQL )
		{
			return $dataSQL->ObjectCode;
		}
		return 0;
	}
	public function lock($userid)
	{
		$sql = sprintf('insert ignore qsolocks(ObjectCode,UID) values("%1$s",%2$d)',
		$this->ObjectCode,$userid);
		$this->_o_DB->execute($sql);
	}
	public function unLock($userid)
	{
		$sql = sprintf('delete from qsolocks where ObjectCode = "%1$s" and UID = %2$d',
		$this->ObjectCode,$userid);
		$this->_o_DB->execute($sql);
	}
	public function getLock($userid)
	{
		$sql = sprintf('select * from qsolocks
					inner join qsusers on qsusers.UID = qsolocks.UID
					where ObjectCode = "%1$s" and qsolocks.UID != %2$d',
		$this->ObjectCode,$userid);
		return $this->_o_DB->fetchOne($sql);
	}
	public function getByCode($code)
	{
		$sql = sprintf('select qsobjects.*,
				(select case when Effect = 1 then concat("Ref_",FieldCode) else 0 end from qsfields where qsfields.ObjectCode = qsobjects.ObjectCode and 
						qsfields.RefObjectCode = qsobjects.ObjectCode limit 1) as tree
				 from qsobjects where ObjectCode = %1$s', 
				$this->_o_DB->quote($code));
		return $this->_o_DB->fetchOne($sql);
	}
	function createTree($main,$fidname)
	{
		$sql=sprintf('select t1.*,t2.FieldCode as oFieldID 
						from qsfields as t1
						inner join qsfields as t2 on t1.RefFieldCode = t2.FieldCode
						where t1.ObjectCode="%1$s" and t1.RefObjectCode="%1$s"',
					$this->ObjectCode);
		$dataSQL = $this->_o_DB->fetchOne($sql);
		if($dataSQL)
		{
			$sql = sprintf('CREATE TEMPORARY TABLE %1$s_tmp 
					(IOID int NOT NULL, 
        			lft int NOT NULL,
        			rgt int NOT NULL,
        			PRIMARY KEY (IOID)) ENGINE = MEMORY', 
				$this->ObjectCode);
			$this->_o_DB->execute($sql);
			if($main)
			{
				if($this->orderField && $this->orderType)
				{
					$sql = sprintf('select IOID, %1$s as ParentID from %2$s order by %3$s %4$s', 
							'Ref_' . $dataSQL->FieldCode,
							$this->ObjectCode,
							$this->orderField,
							$this->orderType);
				}
				else
				{
					$sql = sprintf('select IOID, %1$s as ParentID from %2$s order by IOID DESC', 
							'Ref_' . $dataSQL->FieldCode,
							$this->ObjectCode);
				}
				$data = $this->_o_DB->fetchAll($sql);
					$aLink = array();
					foreach ($data as $link)
					{
						$i_father_id = (int)$link->ParentID;
				        $i_child_id = $link->IOID;
				        if (!array_key_exists($i_father_id,$aLink)) 
				        {
				            $aLink[$i_father_id]=array();
				        }
				        $aLink[$i_father_id][]=$i_child_id;
				    }
				    $this->transformTree($aLink);
			}
			else 
			{
				$ifidSql = sprintf('select distinct %2$s as IFID from %1$s order by lft', 
							$this->ObjectCode,
							$fidname);
				$ifids = $this->_o_DB->fetchAll($ifidSql);
				foreach($ifids as $item)
				{
					$sql = sprintf('select IOID, %1$s as ParentID from %2$s
							where %3$s = %4$d', 
							'Ref_' . $dataSQL->FieldCode,
							$this->ObjectCode,
							$fidname,
							$item->IFID);
					$data = $this->_o_DB->fetchAll($sql);
					$aLink = array();
					foreach ($data as $link)
					{
						$i_father_id = (int)$link->ParentID;
				        $i_child_id = $link->IOID;
				        if (!array_key_exists($i_father_id,$aLink)) 
				        {
				            $aLink[$i_father_id]=array();
				        }
				        $aLink[$i_father_id][]=$i_child_id;
				    }
				    $this->transformTree($aLink);
				}
				
			}
			$sql = sprintf('update %1$s as u 
							inner join %1$s_tmp as v on u.IOID = v.IOID
							set u.lft = v.lft, u.rgt = v.rgt
					',$this->ObjectCode);
			$this->_o_DB->execute($sql);
			$sql = sprintf('DROP TABLE IF EXISTS %1$s_tmp',$this->ObjectCode);
			$this->_o_DB->execute($sql);
		}
	}
	public function transformTree($aLink) 
	{
		$count = 0;
		$this->traverse(0, $count, $aLink);
	}
	public function traverse($i_id,&$count,&$aLink) 
	{
		$i_lft = $count;
		$count++;

		if(isset($aLink[$i_id]))
		{
	        $a_kid = $aLink[$i_id];
	        if ($a_kid) 
	        {
	        	foreach($a_kid as $a_child) 
	            {
	            	$this->traverse($a_child,$count,$aLink);
				}
			}
		}
        $i_rgt=$count;
        $count++;
        $this->updateTree($i_lft,$i_rgt,$i_id);
	}   
	public function updateTree($lft,$rgt,$id)
	{
		if(!$id)
		{
			return;
		}
		$sql = sprintf('insert into %1$s_tmp(IOID,lft,rgt) values(%2$d, %3$d,%4$d)',
					$this->ObjectCode,
					$id,
					$lft,
					$rgt);
		$this->_o_DB->execute($sql);
	}
	public function getJsonData()
	{
		if($this->orderType != 'ASC' && $this->orderType != 'DESC' && $this->jsonData === false)
		{
			$this->jsonData = (array)Qss_Json::decode($this->orderType);
		}
		return $this->jsonData;
	}
	public function deleteTempTable($objcode)
	{
		$where = " LIKE 'O%\_%' ";
		if($objcode)
		{
			$where = " LIKE '{$objcode}'";
		}
		$sql = "SHOW TABLES " . $where;
		$dataSQL = $this->_o_DB->fetchAll($sql);
		foreach ($dataSQL as $item)
		{
			$item = array_values((array) $item);
			$tbl =  $item[0];
			$deleteSQL = sprintf('DROP TABLE IF EXISTS %1$s',$tbl);
			$this->_o_DB->execute($deleteSQL);
		}
		$where = " where Table_Type = 'VIEW' ";
		$sql = "SHOW FULL TABLES " . $where;
		$dataSQL = $this->_o_DB->fetchAll($sql);
		foreach ($dataSQL as $item)
		{
			$item = array_values((array) $item);
			$tbl =  $item[0];
			$deleteSQL = sprintf('DROP VIEW IF EXISTS %1$s',$tbl); 
			$this->_o_DB->execute($deleteSQL);
		}
	} 
}
?>
