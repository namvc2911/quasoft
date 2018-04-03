<?php
/**
 * 
 * @author HuyBD
 *
 */
class Qss_Lib_Bin
{

	protected $_error;
	
	protected $_messages;
	
	protected $_form;
	
	protected $_db;
	
	protected $_params;
	
	protected $_status;

	protected $_language;
	
	protected $_mainParams;
	
	protected $_subParams;
	
	protected $_user;
	
	/**
	 *
	 * @return void
	 */
	public function __construct($form)
	{ 
		$this->_error = false;
		$this->_messages = array();
		$this->_form = $form;
		$this->_db = Qss_Db::getAdapter('main');
		$this->_params = $this;
		$this->_mainParams = null;
		$this->_subParams = array();
		//load translation
		$path = '';
		$lang = Qss_Translation::getInstance()->getLanguage();
		$reflector = new ReflectionClass($this);
		$filename = $reflector->getFileName();
		$path = dirname($filename);
		$file = basename($filename, ".php"); 
		$path =  $path.'/'.$file . '_' . $lang.  '.ini';
		$this->_language = Qss_Translation::getInstance()->getTranslation($path);
		$this->_user = Qss_Register::get('userinfo');
	}
	public function init()
	{
		return;
		if($this->_form->i_IFID)
		{
			foreach($this->_form->o_fGetMainObjects() as $object)
			{
				$sql = sprintf('select * from %1$s where IFID_%2$s = %3$d',
					$object->ObjectCode,
					$this->_form->FormCode,
					$this->_form->i_IFID);
				$dataSQL = $this->_db->fetchOne($sql);
				foreach($dataSQL as $key=>$val)
				{
					$this->_params->$key = $val;
				}
			}
		}
		foreach($this->_form->a_fGetSubObjects() as $object)
		{
			if($this->_form->i_IFID)
			{
				$param = array();
				$sql = sprintf('select * from %1$s where IFID_%2$s = %3$d',
					$object->ObjectCode,
					$this->_form->FormCode,
					$this->_form->i_IFID);
				$dataSQL = $this->_db->fetchAll($sql);
				foreach($dataSQL as $item)
				{
					$newobject = new stdClass();
					foreach($item as $key=>$val)
					{
						$newobject->$key = $val;
					}
					$param[] = $newobject;
				}
				$this->_params->{$object->ObjectCode} = $param;
			}
		}
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
	public function getMessage ($type = Qss_Service_Abstract::TYPE_HTML)
	{
		if ( $type == Qss_Service_Abstract::TYPE_TEXT)
		{
			$retval = implode("\n", $this->_messages);
		}
		else
		{
			$retval = implode("<br/>", $this->_messages);
		}
		return $retval;
	}
	public function setMessage ($message)
	{
		$this->_messages[] = $message;
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
			case 'requests':
				return new Qss_Request();
				break;
			default:
				//check if is objectcode
				$theobject = $this->_form->o_fGetObjectByCode($name);
				if($theobject)
				{
					if(!isset($this->_subParams[$name]))
					{
						foreach($this->_form->a_fGetSubObjects() as $object)
						{
							if($this->_form->i_IFID)
							{
								$param = array();
								$sql = sprintf('select * from %1$s where IFID_%2$s = %3$d',
									$object->ObjectCode,
									$this->_form->FormCode,
									$this->_form->i_IFID);
								$dataSQL = $this->_db->fetchAll($sql);
								foreach($dataSQL as $item)
								{
									$newobject = new stdClass();
									foreach($item as $key=>$val)
									{
										$newobject->$key = $val;
									}
									$param[] = $newobject;
								}
								$this->_subParams[$object->ObjectCode] = $param;
							}
						}
					}
					if(isset($this->_subParams[$name]))
					{
						return $this->_subParams[$name];
					}
				}
				else
				{
					if($this->_mainParams === null)
					{
						foreach($this->_form->o_fGetMainObjects() as $object)
						{
							$sql = sprintf('select * from %1$s where IFID_%2$s = %3$d',
								$object->ObjectCode,
								$this->_form->FormCode,
								$this->_form->i_IFID);
							$dataSQL = $this->_db->fetchOne($sql);
							$this->_mainParams = $dataSQL;
						}
					}
					if(property_exists($this->_mainParams,$name))
					{
						return $this->_mainParams->{$name};
					}
				}
				break;
		}
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
	public function __call($name, $agrs)
	{
		if($name == '_translate')
		{
			return isset($this->_language[$agrs[0]])?$this->_language[$agrs[0]]:"UNKOWN_".$agrs[0];
		}
	}
	
	
	public function _removeData($IOIDFieldAlias, $dataRemove, $removeObject, $module, $ifid)
	{
		$ifid = @(int)$ifid;
		$removeIndex = 0;
		$remove = array();
		$keepIOIDRemove = array(); // Mang chua ioid xoa co the lap lai
		
		foreach ($dataRemove as $item)
		{
			if(!in_array($item->$IOIDFieldAlias, $keepIOIDRemove))
			{
				$remove[$removeObject][$removeIndex] = $item->$IOIDFieldAlias;
				$keepIOIDRemove[] = $item->$IOIDFieldAlias;
				$removeIndex++;
			}
		}
		
		// Xoa du lieu chi tiet da duoc cap nhat truoc do
		if(count($remove))
		{
			$service = $this->services->Form->Remove($module , $ifid, $remove);
			if($service->isError())
			{
				$this->setError();
				$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
			}
		}
	}
	// End removeData
	protected function _sendMail($subject,$address,$body,$ccadress = array(),$attachement = array())
	{
		if(is_array($address) && count($address))
		{
			$to = '';
			$cc = '';
			$mail = new Qss_Model_Mail();
			foreach ($address as $email=>$name)
			{
				//$mail->addTo($email, $name);
				if($to)
				{
					$to .= ',';
				}
				$to .= $email;
			}
			foreach ($ccadress as $email=>$name)
			{
				//$mail->addCc($email, $name);
				if($cc)
				{
					$cc .= ',';
				}
				$cc .= $email;
			}
			/*$mail->setSubject($subject);
			$mail->setBody($body);
			@$mail->send();*/
			$mail->logMail($subject, $body, $to, $cc, null,implode(',',$attachement));
		}
	}
}
?>