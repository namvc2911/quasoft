<?php
class Qss_Bin_Notify_Validate_M602_Sap extends Qss_Lib_Notify_Validate
{
	const TITLE ='Đồng bộ tồn kho dữ liệu từ SAP BAPI.';

	const TYPE ='SUBSCRIBE';

	public function __doExecute()
	{
		$sap = new Qss_Lib_Sap_Saprfc(array(
			"logindata"=>array(
				"ASHOST"=>"1.11.213.7" // application server
				,"SYSNR"=>"00" // system number
				,"CLIENT"=>"500" // client
				,"USER"=>"abcuser" // user
				,"PASSWD"=>"abcpass" // password
				)
			,"show_errors"=>false // let class printout errors
			,"debug"=>true)) ; // detailed debugging information
		// Call-Function
		$result=$sap->callFunction("BAPI_INVETORY_GETLIST",
			array());
		// Call successfull?
		if ($sap->getStatus() == SAPRFC_OK) 
		{
			echo 'dddddddd';
			foreach ($result["INVENTORY"] as $item)
			{
				
			}
		} 
		else 
		{
			// No, print long Version of last Error
			// or print your own error-message with the strings received from
			// $sap->getStatusText() or $sap->getStatusTextLong()
			$this->setError();
			$this->setMessage($sap->getStatusTextLong());
		}
		// Logoff/Close saprfc-connection LL/2001-08
		$sap->logoff();
    }
}
?>