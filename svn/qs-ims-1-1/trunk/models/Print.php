<?php

class Qss_Model_Print extends Qss_Model_Admin_Design
{

	protected $arrData = array();

	/**
	 * construct
	 *
	 * @access  public
	 */
	function __construct ($deptid,$type = Qss_Lib_Const::FORM_DESIGN_FORM)
	{
		parent::__construct($deptid,$type);
	}
	public function sz_fPrintForm (Qss_Model_Form $form, $design_id = 0)
	{
		$ret = "";
		$data = array();
		$fn = $this->szFolder . $design_id . ".html";
		if ( !file_exists($fn) || !$design_id )
		{
			$sql = "select FDID from qsfdesigns where FormCode = '{$form->FormCode}' LIMIT 1";
			$dataSQL = $this->_o_DB->fetchOne($sql);
			if ( $dataSQL )
			{
				$design_id = $dataSQL->FDID;
			}
			$fn = $this->szFolder . $design_id . ".html";
			if ( !file_exists($fn) )
			{
				$design_id = $this->i_fCreateForm($form->FormCode, $design_id);
			}
		}
		$fn = $this->szFolder . $design_id . ".html";
		$arrElement = Qss_Lib_Template::getElement($fn);
		if ( $arrElement[1] )
		{
			foreach ($arrElement[1] as $val)
			{
				$arrVal = @split('_', $val);
				if ( $arrVal[0] == 'ogrid' )
				{
					$objid = (int) $arrVal[1];
					$designid = (int) $arrVal[3];
					$object = $form->a_Objects[$objid];
					$object->initData($form->i_IFID, $form->i_DepartmentID, 0);
					$data[$val] = $this->getObjectListView($object, $designid);
				}
				elseif ( $arrVal[0] == 'otitle' )
				{
					$objid = (int) $arrVal[1];
					$designid = (int) $arrVal[3];
					$object = $form->a_Objects[$objid];
					$object->initData($form->i_IFID, $form->i_DepartmentID, 0);
					$data[$val] = $object->sz_Name;
				}
			}
		}
		foreach ($form->a_Objects as $key => $obj)
		{
			if ( $obj->b_Main )
			{
				$obj->initData($form->i_IFID, $form->i_DepartmentID, $obj->i_IOID);
				$arrElement = Qss_Lib_Template::getElement($fn);
				if ( $arrElement[1] )
				{
					foreach ($arrElement[1] as $val)
					{
						$arrVal = @split('_', $val);
						if ( $arrVal[0] == 'dat' )
						{
							$fieldid = (int) $arrVal[1];
							if( $obj->getFieldByCode($fieldid))
							{
								$data[$val] = $obj->getFieldByCode($fieldid)->sz_fGetDisplay();
							}
						}
						elseif ( $arrVal[0] == 'lb' )
						{
							$fieldid = (int) $arrVal[1];
							if( $obj->getFieldByCode($fieldid))
							{
								$data[$val] = $obj->getFieldByCode($fieldid)->szFieldName;
							}
						}
					}
				}
				$this->setDate($form->startDate);
				$this->arrData['content_id'] = $form->FormCode;
				$this->arrData['record_id'] = $form->FormCode;
			}
		}
		$data = array_merge($data, $this->arrData);
		$template = new Qss_Lib_Template();
		$ret .= $template->parseTemplateFile($fn, $data);
		return $ret;

	}



	function setDate ($time)
	{
		if ( !is_numeric($time) )
		return;
		$arr = getdate($time);
		$this->arrData['sd'] = $arr['mday'];
		$this->arrData['sm'] = $arr['mon'];
		$this->arrData['sy'] = $arr['year'];
		$this->arrData['sw'] = $this->wday[$arr['wday']];
	}

}
?>