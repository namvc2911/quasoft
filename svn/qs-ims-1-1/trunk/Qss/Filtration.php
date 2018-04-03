<?php

class Qss_Filtration
{
	/**
	 * The list of filter for the filtration
	 * @var array
	 */
	protected $_filters = array();
	
	/**
	 * Add a filter into the filter list
	 * 
	 * @param array|Filter_Interface $filter
	 */
	public function add($filter)
	{
		$this->_filters[] = $filter;
	}

	/**
	 * Execute the filter action
	 * @param mixed &$value
	 * @throws Exception
	 */
	public function filter(&$value)
	{
		foreach($this->_filters as $key=>$filter)
		{
			if(is_array($filter))
			{
				$class = isset($filter['class']) ? $filter['class'] : null;
				if($class === null)
					throw new Exception('The filter class is null at ' . var_export($filter, true));

				$args = $filter['args'] ? $filter['args'] : null;
				$namespace = $filter['namespace'] ? $filter['namespace'] : '';
				self::be($value, $class, $args, $namespace);
			}
			elseif($filter instanceof Filter_Interface)
				$filter->filter($value);
			else
				throw new Exception('The filter is not good at ' . var_export($filter, true));
		}
	}
	
	/**
	 * Execute a single filter (be) something
	 * 
	 * @param mixed $value
	 * @param string $class
	 * @param array|null $args
	 * @param string $namespace
	 */
	public static function be(&$value, $class, $args = null, $namespace = null)
	{
		$filter = Loader::loadObject($class, $namespace, $args);
		$filter->filter($value);
	}
}
?>