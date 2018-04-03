<?php

class Qss_Service_Admin_Department_Save extends Qss_Service_Abstract
{

	public function __doExecute ($params)
	{
		$dept = new Qss_Model_Admin_Department();
		$deptid = (int) $params['deptid'];
		$dept->init($deptid);
		$dept->save($params);
		/*if(!$deptid)
		{
			$public_folder = $_SERVER['DOCUMENT_ROOT'];
			$data_dir = QSS_DATA_DIR . "/image/"  . $dept->intDepartmentID;
			if(!is_dir($data_dir))
			mkdir($data_dir);
			$link = $public_folder . "/" . $dept->szDataFolder . "/image";
			if ( is_dir($data_dir) && !file_exists($link) )
			{
				$ret = symlink($data_dir,$link);
				if ( !$ret )
				{
					echo "Can not create link " . $link . " to " . $data_dir . "\n";
				}
			}
			else
			{
				echo $data_dir . $link . "\n";
			}

			$data_dir = QSS_DATA_DIR . "/media/"  . $dept->intDepartmentID;
			if(!is_dir($data_dir))
			mkdir($data_dir);
			$link = $public_folder . "/" . $dept->szDataFolder . "/media";
			if ( is_dir($data_dir) && !file_exists($link) )
			{
				$ret = symlink($data_dir,$link);
				if ( !$ret )
				{
					echo "Can not create link " . $link . " to " . $data_dir . "\n";
				}
			}
			else
			{
				echo $data_dir . $link . "\n";
			}

			$data_dir = QSS_DATA_DIR . "/jscss/"  . $dept->intDepartmentID;
			if(!is_dir($data_dir))
			mkdir($data_dir);
			$link = $public_folder . "/" . $dept->szDataFolder . "/jscss";
			if ( is_dir($data_dir) && !file_exists($link) )
			{
				$ret = symlink($data_dir, $link);
				if ( $ret )
				{
					echo "Can not create link " . $link . " to " . $data_dir . "\n";
				}
			}

			$data_dir = QSS_DATA_DIR . "/pictures/"  . $dept->intDepartmentID;
			if(!is_dir($data_dir))
			mkdir($data_dir);
			$link = $public_folder . "/" . $dept->szDataFolder . "/pictures";
			if ( is_dir($data_dir) && !file_exists($link) )
			{
				$ret = symlink($data_dir, $link);
				if ( $ret )
				{
					echo "Can not create link " . $link . " to " . $data_dir . "\n";
				}
			}

			$data_dir = QSS_DATA_DIR . "/files/"  . $dept->intDepartmentID;
			if(!is_dir($data_dir))
			mkdir($data_dir);
			$link = $public_folder . "/" . $dept->szDataFolder . "/files";
			if ( is_dir($data_dir) && !file_exists($link) )
			{
				$ret = symlink($data_dir, $link);
				if ( $ret )
				{
					echo "Can not create link " . $link . " to " . $data_dir . "\n";
				}
			}
		}*/
	}

}
?>