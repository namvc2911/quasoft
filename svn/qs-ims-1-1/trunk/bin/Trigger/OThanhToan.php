<?php
class Qss_Bin_Trigger_OThanhToan extends Qss_Lib_Trigger
{
	/**
	 * Kiểm tra điều khoản thanh toán
	 */
	public function onUpdate($object)
	{
		$this->checkPercent($object);
	}
	/**
	 * Kiểm tra điều khoản thanh toán
	 */
	public function onInsert($object)
	{
		$this->checkPercent($object);
	}
	private function checkPercent($object)
	{
		
	}
	
	private function checkEqual($object)
	{
		
	}
	
}
?>
