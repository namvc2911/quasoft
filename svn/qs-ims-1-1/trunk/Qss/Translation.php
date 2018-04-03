<?php

class Qss_Translation
{

	protected static $_instance;

	protected $_translation = array();

	protected $_lang;
	/**
	 * construct
	 *
	 * @access  public
	 */
	function __construct ()
	{
		
	}
	public static function getInstance ()
	{
		if ( null === self::$_instance )
		{
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	public function setLanguage($lang)
	{
		$this->_lang = $lang;
		/*$memcache = Qss_Memcache::getInstance();
		if($memcache->isEnabled())
		{
			$this->_translation = $memcache->get('translation_'.$this->_lang,array());
		}
		if(!count($this->_translation))
		{
			$sql = sprintf('select * from qstranslation where Language = %1$s',$this->_o_DB->quote($this->_lang));
			$dataSQL = $this->_o_DB->fetchAll($sql);
			foreach ($dataSQL as $item)
			{
				$this->_translation[$item->ID] = $item->Text;
			}
			if($memcache->isEnabled() && count($this->_translation))
			{
				$memcache->set('translation_'.$this->_lang, $this->_translation);
			}
		}*/
	}
	public function getTranslation($path)
	{
		if(file_exists($path))
		{
			$memcache = Qss_Memcache::getInstance();
			$data = array();
			if($memcache->isEnabled())
			{
				$data = $memcache->get($path);
			}
			if(!count($data) && file_exists($path))
			{
				$data = Qss_Config::loadIniFile($path);
			}
			if($memcache->isEnabled())
			{
				$memcache->set($path, $data);
			}
			return $data;
		}
	}
	public function getText($id)
	{
		return isset($this->_translation[$id])?$this->_translation[$id]:'UNKOWN_'.$id;
	}
	public function getLanguage()
	{
		return $this->_lang?$this->_lang:'vn';
	}
}
?>