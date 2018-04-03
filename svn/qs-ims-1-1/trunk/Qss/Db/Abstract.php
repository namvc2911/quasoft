<?php

abstract class Qss_Db_Abstract
{

	public function select ()
	{
		return new Db_Select();
	}

	abstract public function execute ($expr);

	abstract public function escape ($value);

	public function quote ($value)
	{
		return '\'' . $this->escape($value) . '\'';
	}

	abstract public function insert ($table, $data);

	abstract public function insertedId ();

	abstract public function update ($table, $data, $where);

	abstract public function delete ($table, $where);

	abstract public function fetchOne ($select);

	abstract public function fetchAll ($select);

	abstract protected function _sql ($select);
	
	abstract public function open ();
	abstract public function close ();
	abstract public function isOpen ();
	abstract public function beginTransaction ();
	abstract public function commit ();
	abstract public function rollback ();
}
?>