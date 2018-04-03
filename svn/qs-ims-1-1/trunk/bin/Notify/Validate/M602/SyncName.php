<?php
class Qss_Bin_Notify_Validate_M602_SyncName extends Qss_Lib_Notify_Validate
{
    const TITLE ='Đồng bộ tên sản phẩm.' ;

    const TYPE ='SUBSCRIBE';

    public function __doExecute()
    {
        try{
        	$sql = sprintf('update OSanPham
        				inner join OKho on OKho.Ref_MaSP = OSanPham.IOID
        				set OSanPham.TenSanPham = OKho.TenSP
        				where OSanPham.TenSanPham <> OKho.TenSP');
			$this->_db->execute($sql);
		}
		catch(Exception $e)
		{
			$this->setError();
			$this->setMessage($e->getMessage());
		}
		//echo $count;
    }
}
?>