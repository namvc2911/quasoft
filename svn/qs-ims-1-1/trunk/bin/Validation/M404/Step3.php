<?php
class Qss_Bin_Validation_M404_Step3 extends Qss_Lib_WValidation
{
	/**
	 * Phai co it nhat mot dong trong danh sach hoa don.
	 */
	public function onNext()
	{
		parent::init();
		$danhSach = $this->_params->ODanhSachHoaDon;
		if(count((array)$danhSach) == 0) {
			$this->setError();
			$this->setMessage($this->_translate(1));
		}
	}
}