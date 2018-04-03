<?php
class Qss_View_Instance_Field_DBCombobox extends Qss_View_Abstract
{
	public function __doExecute ($name, $val, $refobjid, $reffid, $refformid, $width = 100)
	{
		$sql = sprintf('$d and qsfields.sfieldid=%3$d and (qsforms.Type=1 or qsiobjects.DepartmentID in (%4$d))', $refformid, $refobjid, $reffid, $user->user_dept_id . ',' . $user->user_dept_list);
//		if ( $onchange[1] != '' )
//			$sql .= sprintf(' and qsiobjects.IOID in(%1$s)', $onchange[1]);
		$sql .= '  ';
		$sql .= ' group by VID order by szValue';
		$db = Qss_Db::getAdapter('main');
		$dataSQL = $db->fetchAll($sql);
		$ret = sprintf('<select name="%1$s" style="width: %2$d">', $name, $width );
		$ret .= "<option value=\"0\">---- Lựa chọn ----</option>";
		foreach ( $dataSQL as $data)
		{
			$ret .= "<option value=\"" . $data->IOID . "\"";
			$ret .= ($val == $data->IOID) ? " selected" : " ";
			$ret .= ">" . $data->szValue . "</option>";
		}
		$ret .= "</select>";
		return $ret;
	}
}
?>