<?php
/**
 * 
 * @author HuyBD
 *
 */
class Qss_Lib_Onload extends Qss_Lib_Bin
{
	protected $_object;
	
	public function __construct($form,Qss_Model_Object &$object)
	{
		parent::__construct($form);
		$this->_object = $object;
	}
	
	public function __doExecute()
	{
		/*foreach ($this->_object->a_Fields as $field)
		{
			if($field->RefFieldCode && ($field->intInputType == 3 || $field->intInputType == 4))
			{
				if(!count($field->arrFilters))
				{
					$this->_doFilter($field);
				}
			}
		}*/
	}
	function _doFilter($fa)
	{
		if($fa->RefFormCode)
		{
			$required = false;
			foreach($this->_object->a_Fields as $key=>$f)
			{
				if($fa->FieldCode==$f->FieldCode)
				{
					break;
				}
				if(Qss_Lib_System::getFormObject($fa->RefFormCode, $fa->RefObjectCode)->Main)
				{
					break;
				}
				//cùng link tới 1 form
				if($f->szRegx != Qss_Lib_Const::FIELD_REG_AUTO 
					&& ($f->intInputType==3 || $f->intInputType==4 || $f->intInputType==11 || $f->intInputType==12) 
					&& $fa->RefFormCode == $f->RefFormCode 
					&& $fa->RefFieldCode != $f->RefFieldCode
					&& $this->_object->FormCode != $f->RefFormCode)
				{
					//$fa->arrFilters = array();
					//trường hợp cùng đối tượng
					if($f->RefObjectCode == $fa->RefObjectCode)
					{
						$sql1 = sprintf('SELECT IOID FROM %1$s
									WHERE IOID = %3$d'
									,$f->RefObjectCode,$f->RefFieldCode,$f->intRefIOID);
						$fa->arrFilters[] = sprintf('v.IOID in (%1$s)',$sql1);
					}
					else//chỉ cùng form
					{
						//chắc chả lọc mấy lần đâu :(
						$fa->arrFilters = array();
						$fa->arrFilters[] = sprintf('IOID in (SELECT b.IOID FROM %1$s as a
								inner join %2$s as b on a.IFID_%3$s = b.IFID_%3$s
								WHERE a.IOID=%4$d)',
								$f->RefObjectCode,
								$fa->RefObjectCode,
								$f->RefFormCode,
								$f->intRefIOID);
					}
					$fa->intRefIFID = $f->intRefIFID;
					if(!count($fa->arrFilters))
					{
						$required = true;
					}
				}
			}
			//đối tượng cha
			if($this->_object->ParentObjectCode) 
			{
				if($fa->RefFormCode == $this->_object->FormCode || $fa->szRegx == Qss_Lib_Const::FIELD_REG_PARENT)//
				{
					$fa->intRefIFID = $this->_object->i_IFID;	
					$fa->arrFilters[] = sprintf('IFID_%1$s = %2$d'
							,$fa->RefFormCode,
							$this->_object->i_IFID);
				}
				elseif($fa->szRegx == Qss_Lib_Const::FIELD_REG_REFERENCE)
				{
					//Vi du bo phan trong vat tu phan ke hoach
					//truoc het lay doi tuong cha, chi lay cai dau
					$sql = sprintf('select * from qsfields
								inner join qsobjects on qsobjects.ObjectCode = qsfields.ObjectCode
								where qsobjects.ObjectCode = "%1$s" and RefFormCode="%2$s"
								order by FieldNo limit 1',
							$this->_object->ParentObjectCode,
							$fa->RefFormCode);
					$dataSQL = $this->_db->fetchOne($sql);
					if($dataSQL)
					{
						//$fa->intRefIFID = $this->_object->i_IFID;	
						$fa->arrFilters[] = sprintf('IFID_%6$s in (Select IFID_%6$s From %1$s
							inner join %2$s on %1$s.IOID = %2$s.Ref_%3$s
							Where IFID_%4$s = %5$d)',
							$dataSQL->RefObjectCode,
							$this->_object->ParentObjectCode,
							$dataSQL->FieldCode,
							$this->_object->FormCode,
							$this->_object->i_IFID,
							$dataSQL->RefFormCode);
							//echo $sql;
					}
				}
			}
			//Giữa các đối tượng phụ
			if($fa->RefFormCode== $this->_object->FormCode && $fa->RefObjectCode == $this->_object->ObjectCode && !$this->_object->b_Main)
			{
				$fa->intRefIFID = $this->_object->i_IFID;	
					$fa->arrFilters[] = sprintf('IFID_%1$s = %2$d'
						,$this->_object->FormCode,
						$this->_object->i_IFID);
			}
			if($required && !count($fa->arrFilters))
			{
				$fa->arrFilters[] = '1=0';
			}
		}
	}
	public function __call ($name, $agrs)
	{
		$field = $this->_object->getFieldByCode($name);
		if($field)
		{
			$field->arrFilters = array();
			$this->_doFilter($field);
		}	
	}
}
?>