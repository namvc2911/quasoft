<?php
class Qss_View_Instance_Object_GridEdit extends Qss_View_Abstract
{

	public function __doExecute ($sql, Qss_Model_Form $form, Qss_Model_Object $object, $currentpage = 1, $limit = 20, $orderfield = 0,$groupby=0)
	{
		$this->html->user = Qss_Register::get('userinfo');
		if($object->i_PageCount && $currentpage > $object->i_PageCount)
		{
			$currentpage = 1;
		}
		$this->html->currentpage = $currentpage;
		$this->html->objects = $object->a_fGetIOIDBySQL($sql, $currentpage, $limit);
		$grouplevel = array();//lấy thành nhiều level nhưng khác key
		foreach($groupby as $group)
		{
			$grouplevel[] = $group;
			$$group = array();
			$tmp = $$group;
			$dataSQL = $object->countGroupBySQL($sql,$grouplevel);
			foreach ($dataSQL as $item)
			{
				$key= '';
				foreach($grouplevel as $level)
				{
					if($key != '')
					{
						$key .= '_';
					}
					$key .= ($item->{$level} === '')?0:$item->{$level};
				}
				//$key = $key?0:$key;
				$tmp[$key] = $item->TongSo;
				
			}
			$this->html->{$group} = $tmp;
			//$this->html->countgroups = $object->countGroupBySQL($sql,$group);
			
		}
		//print_r($this->html->ViTri);die;
		$this->html->gridFieldCount = $object->getGridFieldCount() + 1;
		$this->html->fields = $object->loadFields();
		$this->html->o_Object = $object;
	}
}

?>