<?php
/**
 * @author: ThinhTuan
 * @component: Warehouse
 */
class Qss_Lib_Warehouse_Controller extends Qss_Lib_Controller
{
	
	public $_params;
	public $_common;
	public $_warehouse;
	
	public function init()
	{
		//$this->i_SecurityLevel = 15;
		parent::init();
		$this->_params = $this->params->requests->getParams();
		$this->_common = new Qss_Model_Extra_Extra;
		$this->_warehouse = new Qss_Model_Extra_Warehouse();
	}
	
	// @Function: getInventory(), tra ve ton kho cua thanh phan yeu cau
	// @Parameter: 
	//		- $materials: Cac thanh phan yeu cau, array(RefItem=>,RefAttribute=>)
	// @Return: so luong ton kho cua cac thanh phan yeu cau
	public function getInventory($materials)
	{
		$this->_warehouse = new Qss_Model_Extra_Warehouse();
		$retval = array();
		$inventoryObj = $this->_warehouse->getInventoryForItemAndAttribute($materials);
		foreach ($inventoryObj as $item)
		{
			$retval[$item->ItemKey] = $item->ItemQty; 
		}
		return $retval;
	}
	// End getInventory()
}