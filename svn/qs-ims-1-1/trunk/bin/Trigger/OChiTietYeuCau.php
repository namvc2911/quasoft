<?php
class Qss_Bin_Trigger_OChiTietYeuCau extends Qss_Lib_Trigger
{
	/**
	 * onInsert
	 * Kiểm tra xem thuộc tính có bắt buộc hay ko?
	 */
	public function onInsert($object)
	{
		parent::init(); 		
	}
	/**
	 * onUpdate
	 * Kiểm tra xem thuộc tính có bắt buộc hay ko?
	 */
        public function onUpdate($object)
	{
		parent::init();
	}
}