<?php
class Qss_Model_Admin_Department extends Qss_Model_Abstract {

	public $intDepartmentID = 0;

	public $szCode;

	public $szName;

	public $szAddress;

	public $szFax;

	public $szTel;

	public $szMail;

	public $intParentID;

	public $bLegalEntity;

	public $dStartDate;

	public $dEndDate;

	var $data;

	public $dataField = 'DepartmentID';
	public $arrField = array(1 => 'DeptCode', 2 => 'Name', 3 => 'Address', 4 => 'Tel', 5 => 'Fax', 6 => 'Mail', 7 => 'LegalEntity', 8 => 'StartDate', 9 => 'EndDate');
	public $arrFieldName = array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9);
	public $arrFieldType = array(1 => 1, 2 => 1, 3 => 1, 4 => 1, 5 => 1, 6 => 1, 7 => 9, 8 => 8, 9 => 8);

	//-----------------------------------------------------------------------
	/**
	 * construct a department
	 *
	 * @access  public
	 */
	function __construct() {
		parent::__construct();
	}

	//-----------------------------------------------------------------------
	/**
	 * instance of department
	 *
	 * This method set the department id
	 *
	 * @author HuyBD
	 * @param $depid department id for build an department (qsdepartments)
	 * @return boolean
	 */
	function init($depid) {
		$ret = false;
		$this->intDepartmentID = $depid;
		if (!$this->intDepartmentID) {
			return false;
		}

		$sql = "select * from qsdepartments where DepartmentID=" . (int) $this->intDepartmentID;
		$dataSQL = $this->_o_DB->fetchOne($sql);
		if ($dataSQL) {
			$this->szCode = $dataSQL->DeptCode;
			$this->szName = $dataSQL->Name;
			$this->szAddress = $dataSQL->Address;
			$this->szFax = $dataSQL->Fax;
			$this->szTel = $dataSQL->Tel;
			$this->szMail = $dataSQL->Mail;
			$this->intParentID = $dataSQL->ParentID;
			$this->bLegalEntity = $dataSQL->LegalEntity;
			$this->dStartDate = $dataSQL->StartDate;
			$this->dEndDate = $dataSQL->EndDate;
			$ret = true;
		}

		return $ret;
	}
	public function getAll($deptid = 0) {
		$sql = sprintf('select * from qsdepartments where DepartmentID!=%1$d', $deptid);
		return $this->_o_DB->fetchAll($sql);
	}
	//-----------------------------------------------------------------------
	/**
	 * Return textbox element of department name
	 *
	 * @access  public
	 * @todo        call form update form of department
	 * @param   variable have default value is 200
	 */
	function save($params) {
		$this->szCode = $params['szCode'];
		$this->szName = $params['szName'];
		$this->szAddress = $params['szAddress'];
		$this->szFax = $params['szFax'];
		$this->szTel = $params['szTel'];
		$this->szMail = $params['szMail'];
		$this->bLegalEntity = @$params['bLegalEntity'];
		$this->intParentID = $params['intParentID'];
		$this->dStartDate = $params['dStartDate'];
		$this->dEndDate = $params['dEndDate'];
		$data = array('DeptCode' => $this->szCode,
			'Name' => $this->szName,
			'Address' => $this->szAddress,
			'Fax' => $this->szFax,
			'Tel' => $this->szTel,
			'Mail' => $this->szMail,
			'ParentID' => (int) $this->intParentID,
			'LegalEntity' => $this->bLegalEntity,
			'StartDate' => Qss_Lib_Date::displaytomysql($this->dStartDate),
			'EndDate' => Qss_Lib_Date::displaytomysql($this->dEndDate));
		if ($this->intDepartmentID) {
			$sql = sprintf('update qsdepartments set %1$s where DepartmentID = %2$d', /* */
				$this->arrayToUpdate($data),
				$this->intDepartmentID);
		} else {
			$sql = sprintf('insert into qsdepartments%1$s', /* */
				$this->arrayToInsert($data));
		}
		$deptid = $this->_o_DB->execute($sql);
		$this->intDepartmentID = $deptid;
	}

	//-----------------------------------------------------------------------
	/**
	 * Delete department
	 *
	 * @author HuyBD
	 *
	 * @return void
	 */
	function delete() {
		return "doesn't support!";
	}

	//-----------------------------------------------------------------------
	/**
	 *
	 * @param $width
	 * @return unknown_type
	 */
	function getParentEle($width = 100) {
		global $db;
		$ret = "";
		$sql = sprintf('select * from qsdepartments where DepartmentID=%1$d or ParentID=%1$d', $this->intDepartmentIDM);
		$array = array();
		$db->query($sql);
		$array["0"] = "---select parent---";
		while ($db->next_record()) {
			$array[$db->f("DepartmentID")] = $db->f("Name");
		}
		return parent::ComboBox("intParentID", $array, $this->intParentID, $width);
	}

	function getSubDepartments($deptid = 0, $search = '') {
		$like = '';
		if ($search) {
			$like .= ' and Name like ' . $this->_o_DB->quote("%{$search}%");
		}
		$sql = sprintf('select * from qsdepartments where (ParentID=%1$d or DepartmentID = %1$d) %2$s order by ParentID', /* */
			$deptid, $like);
		return $this->_o_DB->fetchAll($sql);
	}
	function getByParent($id) {
		$sql = sprintf('select * from qsdepartments where ParentID=%1$d order by ParentID', /* */
			$id);
		return $this->_o_DB->fetchAll($sql);
	}

	function smartCopy($source, $dest, $options = array('folderPermission' => 0777, 'filePermission' => 0777)) {
		$result = false;

		if (is_file($source)) {
			if ($dest[strlen($dest) - 1] == '/') {
				if (!file_exists($dest)) {
					cmfcDirectory::makeAll($dest, $options['folderPermission'], true);
				}
				$__dest = $dest . "/" . basename($source);
			} else {
				$__dest = $dest;
			}
			$result = copy($source, $__dest);
			chmod($__dest, $options['filePermission']);

		} elseif (is_dir($source)) {
			if ($dest[strlen($dest) - 1] == '/') {
				if ($source[strlen($source) - 1] == '/') { //Copy only contents
				} else {
					//Change parent itself and its contents
					$dest = $dest . basename($source);
					@mkdir($dest);
					chmod($dest, $options['filePermission']);
				}
			} else {
				if ($source[strlen($source) - 1] == '/') {
					//Copy parent directory with new name and all its content
					@mkdir($dest, $options['folderPermission']);
					chmod($dest, $options['filePermission']);
				} else {
					//Copy parent directory with new name and all its content
					@mkdir($dest, $options['folderPermission']);
					chmod($dest, $options['filePermission']);
				}
			}

			$dirHandle = opendir($source);
			while ($file = readdir($dirHandle)) {
				if ($file != "." && $file != "..") {
					if (!is_dir($source . "/" . $file)) {
						$__dest = $dest . "/" . $file;
					} else {
						$__dest = $dest . "/" . $file;
					}
					//echo "$source/$file ||| $__dest<br />";
					$result = $this->smartCopy($source . "/" . $file, $__dest, $options);
				}
			}
			closedir($dirHandle);

		} else {
			$result = false;
		}
		return $result;
	}

	/**
	 * Lay toan bo cong ty truc thuoc cong ty
	 * @param type $parentID
	 */
	public function getChildDepartments($parentID, &$child = array()) {
		$sql = sprintf('select * from qsdepartments where ParentID = %1$d ', $parentID);
		$dataSql = $this->_o_DB->fetchAll($sql);

		if ($dataSql) {
			foreach ($dataSql as $item) {
				$child[] = $item;
				$this->getChildDepartments($item->DepartmentID, $child);
			}
		}

		return $child;
	}

	/**
	 * Lay toan bo id cua cong ty truc thuoc cong ty
	 * @param type $parentID
	 */
	public function getChildDepartmentIDs($parentID, &$child = array()) {
		$sql = sprintf('select * from qsdepartments where ParentID = %1$d ', $parentID);
		$dataSql = $this->_o_DB->fetchAll($sql);

		if ($dataSql) {
			foreach ($dataSql as $item) {
				$child[] = $item->DepartmentID;
				$this->getChildDepartmentIDs($item->DepartmentID, $child);
			}
		}

		return $child;
	}

	/**
	 * Lấy toàn bộ id công ty trực thuộc bao gồm cả id truyen vao
	 * @param type $deptid
	 * @return type
	 */
	public function getAllDepartmentIDsByParent($deptid) {
		$all = array();
		$all[] = $deptid;
		$all = $this->getChildDepartmentIDs($deptid, $all);
		return $all;
	}

}
?>