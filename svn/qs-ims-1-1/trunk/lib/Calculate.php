<?php
/**
 * 
 * @author HuyBD
 *
 */

if(!function_exists('get_called_class'))
{
    function get_called_class()
    {
        $objects = array();
        $traces = debug_backtrace();
        foreach ($traces as $trace)
        {
            if (isset($trace['object']))
            {
                if (is_object($trace['object']))
                {
                    $objects[] = $trace['object'];
                }
            }
        }
        if (count($objects))
        {
            return get_class($objects[0]);
        }
    }
}


class Qss_Lib_Calculate
{

	protected $_error;
	
	protected $_messages;
	
	protected $_object;
	
	protected $_db;
	
	protected $_params;
	
	protected $_status;

	protected $_ioid;
	
	protected $_objid;
	
	protected $_language;
	
	/**
	 *
	 * @return void
	 */
	public function __construct(Qss_Model_Object $object)
	{ 
		$this->_error = false;
		$this->_messages = array();
		$this->_object = $object;
		$this->_db = Qss_Db::getAdapter('main');
		$this->_params = new stdClass();
		$this->_objid = $object->ObjectCode;
		$this->_ioid = $object->i_IOID;
	}
	
	public function setError()
	{
		$this->_error = true;
	}
	public function isError()
	{
		return $this->_error;
	}
	public function validate()
	{
	
	}
	public function getMessage ()
	{
		return implode("\n", $this->_messages);
	}
	public function setMessage ($message)
	{
		$this->_messages[] = $message;
	}
	/**
	 * Get status
	 *
	 * @return status
	 */
	public function getStatus ()
	{
		return @$this->_status;
	}

	/**
	 *
	 * @param $status
	 * @return void
	 */
	public function setStatus ($status)
	{
		$this->_status = $status;
	}
	public function __get ($name)
	{
		switch ( $name )
		{
			case 'services':
				return new Qss_Service();
				break;
			case 'views':
				return new Qss_View();
				break;
			default:
				//linked object and main object, return ioid and objd
				//check if main 
				$sql  = sprintf('select 1 from qsfobjects
							where FormCode = "%1$s" and ObjectCode = "%2$s"  and Main = 1',
						$this->_object->FormCode,
						$name);
				$dataSQL = $this->_db->fetchOne($sql);
				if($dataSQL)
				{
					$sql  = sprintf('select * from %2$s
							where IFID_%1$s = %3$d',
						$this->_object->FormCode,
						$name,
						$this->_object->i_IFID);
					$dataSQL = $this->_db->fetchOne($sql);
					if($dataSQL)
					{
						$this->_ioid = $dataSQL->IOID; 
						$this->_objid = $name;
					} 
				}
				else//linked object
				{
					// get linked field 
					$sql  = sprintf('select * from qsfields
							inner join qsobjects on qsobjects.ObjectCode = qsfields.RefObjectCode
							where qsfields.ObjectCode = "%2$s" and qsobjects.ObjectCode = "%1$s"
							order by FieldNo',
						$name,
						$this->_objid);
					$dataSQL = $this->_db->fetchOne($sql);
					if($dataSQL)
					{
						$linkedfield = $dataSQL->FieldCode;
						if($this->_objid == $this->_object->ObjectCode)//
						{
							$this->_objid = $dataSQL->ObjectCode;
							$this->_ioid = $this->_object->getFieldByCode($linkedfield)->intRefIOID;
						}
						else
						{
							$sql  = sprintf('select dest.* from %2$s as source
									inner join %3$s as dest on source.Ref_%1$s = dest.IOID
									where source.IOID = %4$d',
								$linkedfield,
								$this->_objid,
								$name,
								$this->_ioid);
							$this->_objid = $name;
							
							$dataSQL = $this->_db->fetchOne($sql);
							if($dataSQL)
							{
								$this->_ioid = $dataSQL->IOID;
							}
						}
					}
				}
				return $this;
				break;
		}
	}
	public function __call($name,$agrs)
	{
		//field or object base on $agrs, 0 = object and 1 = field, check if field then return value, if object return array
        if($name == '_translate')
        {
			if(!count($this->_language))
            {


//                if ( !function_exists( 'get_called_class' ) )
//                {
//                    function get_called_class ()
//                    {
//                        $t = debug_backtrace();
//                        $t = $t[0];
//
//                        if ( isset( $t['object'] ) && $t['object'] instanceof $t['class'] )
//                            return get_class( $t['object'] ); return false;
//                    }
//                }

                $child = get_called_class();

                $reflector = new ReflectionClass($child);
                $fn = $reflector->getFileName();
                $lang = Qss_Translation::getInstance()->getLanguage();
                $path = str_ireplace('.php', '_'.$lang.'.ini', $fn);
                $this->_language = Qss_Translation::getInstance()->getTranslation($path);
            }
			return isset($this->_language[$agrs[0]])?$this->_language[$agrs[0]]:"UNKOWN_".$agrs[0];
		}

		$type = $agrs[0];
		if($type == 0)//array of objects
		{
			//check if main 
			$sql  = sprintf('select 1 from qsfobjects
						inner join qsobjects on qsobjects.ObjectCode = qsfobjects.ObjectCode
						where qsfobjects.ObjectCode = %1$s
						and qsfobjects.FormCode = %2$s',
					$this->_db->quote($name),
					$this->_db->quote($this->_object->FormCode));
			$dataSQL = $this->_db->fetchOne($sql);
			if($dataSQL)
			{
				$sql  = sprintf('select * from %1$s
						where IFID_%3$s = %2$d',
					$name,
					$this->_object->i_IFID,
					$this->_object->FormCode);
				$this->_objid = $this->_object->ObjectCode;
				$this->_ioid = $this->_object->i_IOID;
				return $this->_db->fetchAll($sql);
			}
			else//linked object
			{
				// get linked field 
				$sql  = sprintf('select * from qsfields
						inner join qsobjects on qsobjects.ObjectCode = qsfields.RefObjectCode
						where qsfields.ObjectCode = "%2$s" and qsobjects.ObjectCode = "%1$s"',
					$name,
					$this->_objid);
				$dataSQL = $this->_db->fetchOne($sql);
				$ifid = 0;
				$form_code = '';
				if($dataSQL)
				{
					//get IFID
					$sql  = sprintf('select * from %1$s as v
							inner qsiforms on qsiforms.IFID = v.IFID_%2$s
							inner join qsforms on qsforms.FormCode = qsiforms.FormCode
							where v.IOID=%3$d',
							$this->_objid,
							$dataSQL->RefFormCode,
							$this->_ioid);
					$ifidSQL = $this->_db->fetchOne($sql);
					if($ifidSQL)
					{
						$ifid = $ifidSQL->IFID;
						$form_code = $ifidSQL->FormCode;
					}
				}
				$sql  = sprintf('select * from %1$s
						where IFID_%3$s = %2$d',
					$name,
					$ifid,
					$form_code);
				$this->_objid = $this->_object->ObjectCode;
				$this->_ioid = $this->_object->i_IOID;
				return $this->_db->fetchAll($sql);
			}
			
		}
		elseif($type == 1)//field value
		{
			if($this->_objid == $this->_object->ObjectCode)//
			{
				$this->_objid = $this->_object->ObjectCode;
				$this->_ioid = $this->_object->i_IOID;
				return $this->_object->getFieldByCode($name)->getValue(false);
			}
			else
			{
				$sql  = sprintf('select * from qsfields 
						where FieldCode = "%1$s" and ObjectCode = "%2$s"',
					$name,
					$this->_objid
					);
				$fieldSQL = $this->_db->fetchOne($sql);
				if($fieldSQL)
				{
					$sql = sprintf('select * 
						from %2$s as v
	                    where IOID = %1$d ', 
					$this->_ioid,
					$this->_objid);
					$dataSQL = $this->_db->fetchOne($sql);
					if ( $dataSQL )
					{
						switch($fieldSQL->FieldType)
						{
							case 14:
								$field = new Qss_Model_DField();
							case 15:
								$field = new Qss_Model_CField();
							case 16:
								$field = new Qss_Model_UField();
								$field->b_fInit($fieldSQL->ObjectCode,$fieldSQL->FieldCode);
								$field->setValue($dataSQL->{'Ref_'.$fieldSQL->FieldCode});
								break;
							case 11:
								$field = new Qss_Model_Field();
								$field->b_fInit($fieldSQL->ObjectCode,$fieldSQL->FieldCode);
								$field->setValue($dataSQL->{$fieldSQL->FieldCode}/1000);
								break;
							default:
								$field = new Qss_Model_Field();
								$field->b_fInit($fieldSQL->ObjectCode,$fieldSQL->FieldCode);
								$field->setValue($dataSQL->{$fieldSQL->FieldCode});
								break;
						}
						//$field->intIOID = $ioid;
						$this->_objid = $this->_object->ObjectCode;
						$this->_ioid = $this->_object->i_IOID;
						return $field->getValue(false);
					}
				}
			}
		}
	}
}
?>