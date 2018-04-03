<?php
/**
 * Just for http response
 * 
 * @author HuyBD
 *
 */
class Qss_Response
{

	/**
	 * 
	 * @var array of header
	 */
	protected $_headers = array();

	/**
	 * 
	 * @var array of contents
	 */
	protected $_contents = array();

	/**
	 * Set header
	 * 
	 * @param $key
	 * @param $value
	 * @return void
	 */
	public function setHeader ($key, $value)
	{
		$this->_headers[$key] = $value;
	}

	/**
	 * Get header by key
	 * 
	 * @param $key
	 * @return header
	 */
	public function getHeader ($key)
	{
		return isset($this->_headers[$key]) ? $this->_headers[$key] : null;
	}

	/**
	 * Set content
	 * 
	 * @param $content
	 * @param $key
	 * @return void
	 */
	public function setContent ($content, $key = 'default')
	{
		$this->_contents[$key] = $content;
	}

	/**
	 * Get content by key
	 * 
	 * @param $key
	 * @return content
	 */
	public function getContent ($key = 'default')
	{
		return isset($this->_contents[$key]) ? $this->_contents[$key] : null;
	}

	/**
	 * Clear body
	 * 
	 * @return void
	 */
	public function clearBody ()
	{
		$this->_headers = array();
		$this->_contents = array();
	}

	/**
	 * Send to client
	 * 
	 * @return void
	 */
	public function send ()
	{
		foreach ($this->_headers as $header)
		{
			header($header, true);
		}
		foreach ($this->_contents as $content)
		{
			echo $content;
		}
	}
}
?>