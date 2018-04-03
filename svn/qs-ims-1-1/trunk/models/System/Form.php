<?php
/**
 * System form model
 *
 * @author HuyBD
 *
 */
class Qss_Model_System_Form extends Qss_Model_Abstract
{
	public $_b_Editting;

	public $sz_Name;

	public $FormCode;

	public $b_Effected;

	public $bExcelImport;

	public $bSecure;

	public $i_Type; //1 list

	public $sz_Class; //1 list
	
	public $sz_ClassMobile; //1 list mobile

	public $document;
	
	public $comment;
	
	public $reader;
	/**
	 * Build constructor
	 *
	 * @return void
	 */
	public function __construct ($type = Qss_Lib_Const::FORM_TYPE_MODULE)
	{
		parent::__construct();
		$this->i_Type = $type;
	}

	/**
	 * Get all system form
	 *
	 * @return array
	 */
	public function b_fInit ($FormCode)
	{
		$lang = Qss_Translation::getInstance()->getLanguage();
		$lang = ($lang=='vn')?'':'_'.$lang;
		$o_Form = $this->getByCode($FormCode);
		if ( $o_Form )
		{
			$this->FormCode = $FormCode;
			$this->i_Type = $o_Form->Type;
			$this->bSecure = $o_Form->Secure;
			$this->sz_Name = $o_Form->{"Name$lang"}?$o_Form->{"Name$lang"}:$o_Form->Name;
			$this->bExcelImport = $o_Form->ExcelImport;
			$this->b_Effected = $o_Form->Effected;
			$this->sz_Class = $o_Form->class;
			$this->sz_ClassMobile = $o_Form->classMobile;
			$this->document = $o_Form->Document&1;
			$this->comment = $o_Form->Document&2;
			$this->reader = $o_Form->Document&4;
		}
	}

	public function a_fGetByType ($i_Type, $sz_Search = '')
	{
		$sz_SQL = sprintf('select *  from qsforms where Type = %1$d', $i_Type);
		if ( $sz_Search )
		{
			$sz_SQL .= sprintf(' and (Name like %1$s or FormCode like %1$s)' ,
					 $this->_o_DB->quote("%{$sz_Search}%"));
		}
		$sz_SQL .= ' order by FormCode';
		return $this->_o_DB->fetchAll($sz_SQL);
	}
	public function a_fGetList ()
	{
		$sz_SQL = sprintf('select *  from qsforms where Type = 1 or Type = 2');
		return $this->_o_DB->fetchAll($sz_SQL);
	}
	/**
	 * Save system form
	 *
	 * @return boolean
	 */
	public function b_fSave ($params)
	{
		$sql = sprintf('select * from qsforms where FormCode=%2$s and FormCode != "%3$s"',/* (Name = %1$s or */
		$this->_o_DB->quote($params['szName']),/* */
		$this->_o_DB->quote($params['szCode']),/* */
		$params['fid']);
		$dataSQL = $this->_o_DB->fetchOne($sql);
		if($dataSQL)
		{
			return false;
		}
		$document = bindec(sprintf('%1$d%2$d%3$d',
				(@$params['Reader'])?1:0,
				(@$params['Comment'])?1:0,
				(@$params['Document'])?1:0)); 
		$data = array(
					'Name'=>$params['szName'],
					'FormCode'=>$params['szCode'],
					'Effected'=>@$params['bEffected'],
					'ExcelImport'=>@$params['bExcelImport'],
					'Document'=>$document,
					'class'=>$params['szClass'],
					'classMobile'=>$params['szClassMobile'],
					'Type'=>(int)@$params['intType'],
					'Secure'=>(int)@$params['bSecure']
		);
		$lang = new Qss_Model_System_Language();
		$languages = $lang->getAll(1,false);
		foreach ($languages as $item)
		{
			$data['Name_'.$item->Code] = @$params['szName_'.$item->Code];
		}
		$szFile = @$params['epFile'];
		if($params['fid'])
		{
			$sql=sprintf('update qsforms set %1$s
					where FormCode = "%2$s"',/* */
					$this->arrayToUpdate($data),
					$params['fid']);
			$this->_o_DB->execute($sql);
			if($params['fid'] != $params['szCode'])
			{
				$sqlUpdateFObject = sprintf('update qsfobjects set FormCode=%1$s where FormCode=%2$s'
					,$this->_o_DB->quote($params['szCode'])
					,$this->_o_DB->quote($params['fid']));
				$this->_o_DB->execute($sqlUpdateFObject);
			}
			$this->FormCode = $this->FormCode?$this->FormCode:$params['fid'];
		}
		else
		{
			$sql=sprintf('insert into qsforms %1$s',/* */
			$this->arrayToInsert($data));
			$this->FormCode = $this->_o_DB->execute($sql);
		}
		if(file_exists($szFile))
		{
			$ext = strtolower(pathinfo($szFile, PATHINFO_EXTENSION));
			$destfile = QSS_DATA_DIR . "/report/" . $this->FormCode . "." . $ext;
			$ret = copy($szFile, $destfile);
			unlink($szFile);
		}
		return true;
	}
	public function a_fGetAll($sz_Search = '')
	{
		$sz_SQL = sprintf('
            select Form.*, (
                SELECT GROUP_CONCAT(DISTINCT CONCAT( " ", IF(FObject.Main = 1, CONCAT("<b>", FObject.ObjectCode,"</b>"), FObject.ObjectCode)) SEPARATOR "<br/>")
                FROM qsfobjects AS FObject
                WHERE FObject.FormCode = Form.FormCode
                GROUP BY FObject.FormCode 
            ) As Objects
            from qsforms AS Form 
            where 1=1 ');
		if ( $sz_Search )
		{
			$sz_SQL .= sprintf(' and (Name like %1$s or FormCode like %1$s)' ,
					 $this->_o_DB->quote("%{$sz_Search}%"));
		}
		$sz_SQL .= ' order by FormCode';
		return $this->_o_DB->fetchAll($sz_SQL);
	}
	public function a_fGetFormObjects()
	{
		$sql=sprintf('select qsobjects.*,qsfobjects.*,T.Objectname as Parent from qsfobjects 
					left join qsobjects on qsobjects.ObjectCode=qsfobjects.ObjectCode
					left join qsobjects as T on T.ObjectCode=qsfobjects.ParentObjectCode
					where FormCode = "%1$s" order by Main desc,ObjNo',
				$this->FormCode);
		return $this->_o_DB->fetchAll($sql);
	}
	public function a_fGetFormObject($ObjectCode)
	{
		if($ObjectCode)
		{
			$sql = sprintf('select * from qsfobjects where FormCode = %2$s and ObjectCode=%1$s'
						,$this->_o_DB->quote($ObjectCode)
						,$this->_o_DB->quote($this->FormCode));
			return $this->_o_DB->fetchOne($sql);
		}
		else
		{
			return (object)array('ObjNo'=>0,'FormCode'=>$this->FormCode,'ObjectCode'=>'','Main'=>0,'ParentObjectCode'=>'','Public'=>0,'Editing'=>0,'Deleting'=>0,'Insert'=>0,'Track'=>0,'RefFormCode'=>'');
		}
	}
	public function b_fSaveFormObject($params)
	{
		$objid=$params['object']; 
		$parentid=$params['parent'];
		$main=@$params['main'];
		$public=@$params['public'];
		$public = bindec(sprintf('%1$d%2$d%3$d',
				(@$params['custom_mobile'])?1:0,
				(@$params['mobile'])?1:0,
				(@$params['public'])?1:0));
		$editing=@$params['editing'];
		$track=@$params['track'];
		$insert=@$params['insert'];
		$deleting=@$params['deleting'];
		$objno=@$params['objno'];
		$reffid=@$params['reffid'];
		$sql=sprintf('replace into qsfobjects(FormCode,ObjectCode,ParentObjectCode,Main,Public,Editing,Track, `Insert`,Deleting,ObjNo,RefFormCode) 
			Values("%1$s","%2$s","%3$s",IFNULL(%4$d,0),IFNULL(%5$d,0),IFNULL(%6$d,0),IFNULL(%7$d,0),IFNULL(%8$d,0),IFNULL(%9$d,0),IFNULL(%10$d,0),"%11$s")',
			$this->FormCode,
			$objid,
			$parentid,
			$main,
			$public,
			$editing,
			$track,
			$insert,
			$deleting,
			$objno,
			$reffid);
		$this->_o_DB->execute($sql);
		return true;
	}
	public function b_fDeteleFormObject($FormCode, $ObjectCode)
	{
		$sql = sprintf('delete from qsfobjects where FormCode=%1$s and ObjectCode=%2$s',
					$this->_o_DB->quote($FormCode),
					$this->_o_DB->quote($ObjectCode));
		$this->_o_DB->execute($sql);
	}
	/**
	 * Delete system form
	 *
	 * @return boolean
	 */
	public function b_fDelete ()
	{}

	public function getByUrl ($url)
	{
		$sz_SQL = sprintf('select *  from qsforms where class = %1$s', $this->_o_DB->quote($url));
		return $this->_o_DB->fetchOne($sz_SQL);
	}
	public function getByCode ($code)
	{
		$sz_SQL = sprintf('select *  from qsforms where FormCode = %1$s/*getformbycode*/', $this->_o_DB->quote($code));
		return $this->_o_DB->fetchOne($sz_SQL);
	}
	public function getObjectByCode ($formcode,$objectcode)
	{
		$sz_SQL = sprintf('select qsobjects.*  
						from qsforms
						inner join qsfobjects on qsfobjects.FormCode = qsforms.FormCode
						inner join qsobjects on qsobjects.ObjectCode = qsfobjects.ObjectCode 
						where !(qsfobjects.Public & 1) and  qsforms.FormCode = %1$s and qsobjects.ObjectCode= %2$s',
				$this->_o_DB->quote($formcode),
				$this->_o_DB->quote($objectcode));
		return $this->_o_DB->fetchOne($sz_SQL);
	}
	public function getSecureForm ()
	{
		$sz_SQL = sprintf('select *,
						(select FieldCode from qsfields where Grid&1 and ObjectCode = qsfobjects.ObjectCode order by FieldNo limit 1) as Field1 ,
						(select FieldCode from qsfields where Grid&1 and ObjectCode = qsfobjects.ObjectCode order by FieldNo limit 1,1) as Field2 ,
						(select 1 from qsfields where ObjectCode = qsfobjects.ObjectCode and RefObjectCode = qsfobjects.ObjectCode and Effect = 1) as Tree
						from qsforms
						inner join qsfobjects on qsfobjects.FormCode = qsforms.FormCode and Main=1 
						where Effected = 1 and ifnull(Secure,0) = 1');
		return $this->_o_DB->fetchAll($sz_SQL);
	}
	public function getRefForms ()
	{
		$sz_SQL = sprintf('select distinct qsforms.*
						from qsforms
						inner join qsfobjects on qsfobjects.FormCode = qsforms.FormCode and Main=1
						inner join qsfields on qsfields.ObjectCode = qsfobjects.ObjectCode  
						where qsforms.Effected = 1 and qsfields.RefFormCode = %1$s and qsfields.Effect = 1'
				,$this->_o_DB->quote($this->FormCode));
		return $this->_o_DB->fetchAll($sz_SQL);
	}
}
?>