<?php
/**
 * Model for instanse form
 *
 */
class Qss_Model_Sms extends Qss_Model_Abstract
{


	public function __construct ()
	{
		parent::__construct();

	}
	public function sendSMS($phone,$content)
	{
		$arrPhone = explode(',',$phone);
		foreach($arrPhone as $phone)
		{
			$phone = trim($phone);
			$phone = preg_replace("%[^0-9\+]%", '', $phone);
			$this->log(0,$content,$phone);
		}
	}
	public function normalSMS($phone,$content,$status=0)
	{
		$arrPhone = explode(',',$phone);
		foreach($arrPhone as $phone)
		{
			$phone = trim($phone);
			$phone = preg_replace("%[^0-9\+]%", '', $phone);
			$this->log($status,$content,$phone);
		}
	}
	public function log($status,$content,$phone)
	{
		$arr = array('Content'=>$content,
					'Phone'=>$phone,
					'Status'=>$status);
		$sql = sprintf('insert into qssmslogs%1$s',$this->arrayToInsert($arr));
		$this->_o_DB->execute($sql);
		//$this->sendIt();
	}
	public function getLogs($pageno = 1)
	{
		$sql = sprintf('select * from qssmslogs order by ID desc limit %1$d,20',($pageno-1) * 20);
		return $this->_o_DB->fetchAll($sql);
	}
	public function countPage()
	{
		$sql = sprintf('select count(*)/20 as count from qssmslogs');
		return $this->_o_DB->fetchOne($sql);
	}
	public function sendIt()
	{
		$this->corectComPort();
		$config = Qss_Config::get('config'); 
		$port = $config->gsmport;
		
		$com = new COM ( "MSCommLib.MSComm", NULL, CP_UTF8);
			
		$com->CommPort = intval($port);
		$com->Settings = "115200,N,8,1";
			
		$com->InputLen  = 0;
		$com->InputMode = 0;
		$com->RThreshold = 0;
		$com->EOFEnable = TRUE;
		$com->NullDiscard = FALSE; 
		$com->RTSEnable = TRUE;
		
		try
		{
			$com->PortOpen = TRUE;
		}
		catch(Exception $e)
		{
			return 0;
		}
	
		$sql = sprintf('select * from qssmslogs where Status = 0');
		$dataSQL = $this->_o_DB->fetchAll($sql);
		$com->Output = "AT\r";
		$buffer = "";
		$timeout = time() + 10;
		while(1 && $timeout > time())
		{
			$buffer .= $com->Input();
			usleep(500000);
			if (preg_match('/'.preg_quote("OK\r\n", '/').'$/', $buffer)) 
			{
				break;
			} 
			elseif (preg_match('/'.preg_quote("ERROR\r\n", '/').'$/', $buffer)) 
			{
				return 0;
			}
		}
		$com->Output = "AT+CMGF=1\r";
		$buffer = "";
		$timeout = time() + 10;
		while(1 && $timeout > time())
		{
			$buffer .= $com->Input();
			usleep(500000);
			if (preg_match('/'.preg_quote("OK\r\n", '/').'$/', $buffer)) 
			{
				break;
			} 
			elseif (preg_match('/'.preg_quote("ERROR\r\n", '/').'$/', $buffer)) 
			{
				return 0;
			}
		}
		foreach($dataSQL as $item)
		{
			$phone = trim($item->Phone);
			$phone = preg_replace("%[^0-9\+]%", '', $phone);
			$content = preg_replace("%[^\040-\176\r\n\t]%", '', $item->Content);
			$com->Output = "AT+CMGS=\"{$phone}\"\r";
			$com->Output = $content;
			$com->Output = chr(26);
			
			$buffer = "";
			$i = 0;
			$status = 0;
			$timeout = time() + 10;
			while(1 && $timeout > time())
			{
				$buffer .= $com->Input();
				usleep(500000);
				if (preg_match('/'.preg_quote("OK\r\n", '/').'$/', $buffer)) 
				{
					$status = 1;
					break;
				} 
				elseif (preg_match('/'.preg_quote("ERROR\r\n", '/').'$/', $buffer)) 
				{
					$status = 0;
					break;
				}
				$i++;
			}
			if($status)
			{
				$this->setOK($item->ID);
			}
		}
		try
		{
			$com->PortOpen = FALSE;
		}
		catch(Exception $e)
		{
			return 0;
		}
		return 1;
	}
	public function setOK($id)
	{
		$sql = sprintf('update qssmslogs set Status = 1 where ID = %1$d',$id);
		$this->_o_DB->execute($sql);
	}
	public function deleteLog($arr)
	{
		$sql = sprintf('delete from qssmslogs where ID in (%1$s)',implode(',',$arr));
		$this->_o_DB->execute($sql);
	}
	
	public function deleteAllLog()
	{
	    $sql = sprintf('delete from qssmslogs');
	    $this->_o_DB->execute($sql);	    
	}
	private function getComPort()
	{
		$config = Qss_Config::get('config'); 
		$port = $config->port;
		if($this->checkCom($port))
		{
			return;
		}
		for($i=15; $i > 0; $i--)
		{
			if($i != $port && $this->checkCom($i))
			{
				$config->port = $i;
				return;
			}
		}
	}
	private function checkCom($number)
	{
		$retval = true;
		$com = new COM ( "MSCommLib.MSComm", NULL, CP_UTF8);
			
		$com->CommPort = intval($number);
		$com->Settings = "115200,N,8,1";
			
		$com->InputLen  = 0;
		$com->InputMode = 0;
		$com->RThreshold = 0;
		$com->EOFEnable = TRUE;
		$com->NullDiscard = FALSE; 
		$com->RTSEnable = TRUE;	
		try
		{
			$com->PortOpen = TRUE;
		}
		catch(Exception $e)
		{
			$retval = false;
		}
		if($retval)
		{
			$com->Output = "AT\r";
			$buffer = "";
			$timeout = time() + 10;
			while(1 && $timeout > time())
			{
				$buffer .= $com->Input();
				usleep(500000);
				if (preg_match('/'.preg_quote("OK\r\n", '/').'$/', $buffer)) 
				{
					$retval = true;
					break;
				} 
				elseif (preg_match('/'.preg_quote("ERROR\r\n", '/').'$/', $buffer)) 
				{
					$retval = false;
					break;
				}
			}
			$com->Output = "AT+CMGF=1\r";
			$buffer = "";
			$timeout = time() + 10;
			while(1 && $timeout > time())
			{
				$buffer .= $com->Input();
				usleep(500000);
				if (preg_match('/'.preg_quote("OK\r\n", '/').'$/', $buffer)) 
				{
					$retval = true;
					break;
				} 
				elseif (preg_match('/'.preg_quote("ERROR\r\n", '/').'$/', $buffer)) 
				{
					$retval = false;
					break;
				}
			}
		}
		try
		{
			$com->PortOpen = FALSE;
		}
		catch(Exception $e)
		{
			//$retval = false;
		}
		return $retval;
	}
	public function getSMSSettings()
	{
		$sql = sprintf('select * from sms_config order by MsgID');
		return $this->_o_DB->fetchAll($sql);
	}
	public function saveSMSSettings($params)
	{
		$arrMSG = $params['msgid'];
		$arrStatus = $params['status'];
		$arrIntval = $params['intval'];
		$arrPhone = $params['phone'];
		$arrContent= $params['content'];
		foreach ($arrMSG as $key=>$msg)
		{
			$sql = sprintf('replace into sms_config(MsgID,Status,IntVal,Phone,Content) 
						values(%1$s,%2$d,%3$d,%4$s,%5$s)',
						$this->_o_DB->quote($msg),
						$arrStatus[$key],
						$arrIntval[$key],
						$this->_o_DB->quote($arrPhone[$key]),
						$this->_o_DB->quote($arrContent[$key]));
			$this->_o_DB->execute($sql);
		}
		$sql = sprintf('select * from sms_config order by MsgID');
		return $this->_o_DB->fetchAll($sql);
	}
	public function getSMSSetting($msg)
	{
		$sql = sprintf('select sms_config.*,case when IFNULL(TIMESTAMPDIFF(MINUTE, (select max(`Time`) from qssmslogs where Content=%1$s), now()),IntVal)>=IntVal then 0 else 1 end as STP 
					from sms_config where MsgID=%1$s',
						$this->_o_DB->quote($msg));
		//	$this->_o_DB->execute($sql);
		return $this->_o_DB->fetchOne($sql);
	}
	public function saveSMSSetting($msg)
	{
		$sql = sprintf('REPLACE INTO `sms_config` (`MsgID`,`Content`) values(%1$s,%1$s)',
						$this->_o_DB->quote($msg));
		$this->_o_DB->execute($sql);
	}
}
?>