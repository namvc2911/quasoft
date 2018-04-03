<?php
include "sysbase.php";
$db = Qss_Db::getAdapter('main');
$sql = "select * from 
		qsfprocesscalendar
		inner join qsiforms on qsiforms.IFID = qsfprocesscalendar.IFID
  		inner join qsforms on qsforms.FormCode = qsiforms.FormCode
		inner join qsusers on qsusers.UID = qsiforms.UID
  		where deleted = 0 and qsforms.Type = 5 and 
			(
			(qsfprocesscalendar.Type = 0 and Date = CURDATE())
			or
			(qsfprocesscalendar.Type = 5 and hour(CURTIME())%`Interval` = 0)
			or
			(qsfprocesscalendar.Type = 1 and TIMESTAMPDIFF(DAY,qsfprocesscalendar.SDate,CURDATE())%`Interval` = 0)
			or
			(qsfprocesscalendar.Type = 2 and weekday(CURDATE())=WDay and TIMESTAMPDIFF(WEEK,qsfprocesscalendar.SDate,CURDATE())%`Interval` = 0)
			or
			(qsfprocesscalendar.Type = 3 and day(CURDATE())=Day and TIMESTAMPDIFF(MONTH,qsfprocesscalendar.SDate,CURDATE())%`Interval` = 0)
			or
			(qsfprocesscalendar.Type = 4 and day(CURDATE())=Day and month(CURDATE())=Month and TIMESTAMPDIFF(YEAR,qsfprocesscalendar.SDate,CURDATE())%`Interval` = 0)
			)
			and 
			(
			(qsfprocesscalendar.Type = 5 and minute(Time) < minute(CURTIME()) and minute(Time) >= minute(CURTIME())-1)
			or
			(qsfprocesscalendar.Type != 5 and Time < CURTIME() and Time >= ADDTIME(CURTIME() ,'-00:01:00'))
			)
  		order by qsiforms.SDate
  		limit 1000";
$bashes = $db->fetchAll($sql);
$process = new Qss_Model_Process();
foreach ($bashes as $item)
{
	$form = new Qss_Model_Form();
	$form->initData($item->IFID, $item->DepartmentID);
	$service = new Qss_Service();
	$data = $service->Form->Validate($form);
	//echo $data->getMessage(Qss_Service_Abstract::TYPE_TEXT);
	if(!$data->isError())
	{
		/*Login*/
		$params = Qss_Params::getInstance();
		$service = new Qss_Service();
		$userinfo = new Qss_Model_UserInfo();
		$loginservice = $service->Security->UserLogin($item->UserID, $item->Password, $item->DepartmentID,15,0);
		$loginret = $loginservice->getStatus();
		/*end login*/
		if($loginret)
		{
			$params->sessions->set('userinfo', $loginservice->getData());
			$params->registers->set('userinfo', $loginservice->getData());
			$classname = 'Qss_Bin_Process_' . ucfirst($item->class);
			try
			{
				$log = array('FPLID'=>0,
							'IFID'=>$form->i_IFID,
							'UID'=>$item->UID,
							'Date'=>date('Y-m-d'),
							'STime'=>date('H:i:s'),
							'Status'=>0,//treo
							'Note'=>'Schedule:');
				$calid = $process->saveLog($log);
				$bash = new $classname($form);
				$bash->init();
				$bash->__doExecute();
				$log = array('FPLID'=>$calid,
							'ETime'=>date('H:i:s'),
							'Status'=>$bash->isError()?2:1,
							'Note'=>'Scheduled: ' . $bash->getMessage());//
				$calid = $process->saveLog($log);
				//echo $bash->getMessage();
			}
			catch(Exception $e)
			{
				$form->updateError(true, $e->getMessage());
			}
		}
		else
		{
			echo 'Không thể đăng nhập bằng tài khoản ' . $item->UserID;
			$form->updateError(true, 'Không thể đăng nhập bằng tài khoản ' . $item->UserID);
		}
		/*logout*/
		$params->registers->set('userinfo', null);
		$params->sessions->destroy();
	}
}

//select and send log to email
//Send report
$sql = "select distinct cal.* from 
		qsfvalidatecalendars as cal
		where 
			(
			(cal.Type = 0 and Date = CURDATE())
			or
			(cal.Type = 5 and hour(CURTIME())%`Interval` = 0)
			or
			(cal.Type = 1 and TIMESTAMPDIFF(DAY,`SDate`,CURDATE())%`Interval` = 0)
			or
			(cal.Type = 2 and weekday(CURDATE())=WDay and TIMESTAMPDIFF(WEEK,`SDate`,CURDATE())%`Interval` = 0)
			or
			(cal.Type = 3 and day(CURDATE())=Day and TIMESTAMPDIFF(MONTH,`SDate`,CURDATE())%`Interval` = 0)
			or
			(cal.Type = 4 and day(CURDATE())=Day and month(CURDATE())=Month and TIMESTAMPDIFF(YEAR,`SDate`,CURDATE())%`Interval` = 0)
			or
			(cal.Type = 6 and minute(CURTIME())%`Interval` = 0)
			)
			and 
			(
			(cal.Type = 5 and minute(Time) between minute(CURTIME())-1 and minute(CURTIME()))
			or
			(cal.Type != 5 and Time between ADDTIME(CURTIME() ,'-00:01:00') and ADDTIME(CURTIME() ,'00:00:00'))
			or
			(cal.Type = 6)
			)
  		limit 1000";
$allform = $db->fetchAll($sql);
if(count((array)$allform))
{
	include 'syslogin.php';
}
foreach ($allform as $item)
{
	$formcode = substr($item->FormCode,20,4);
	$form = new Qss_Model_Form();
	$form->init($formcode, 1, 1);
	$service = new Qss_Service();
	$service->Notify->Validate($form,$item->FormCode);
}
if(count((array)$allform))
{
	include 'syslogout.php';
}

//select and send log to email
//Send report
$sql = "select distinct cal.* from 
		qsfmailcalendars as cal
		where  
			(
			(cal.Type = 0 and Date = CURDATE())
			or
			(cal.Type = 5 and hour(CURTIME())%`Interval` = 0)
			or
			(cal.Type = 1 and TIMESTAMPDIFF(DAY,`SDate`,CURDATE())%`Interval` = 0)
			or
			(cal.Type = 2 and weekday(CURDATE())=WDay and TIMESTAMPDIFF(WEEK,`SDate`,CURDATE())%`Interval` = 0)
			or
			(cal.Type = 3 and day(CURDATE())=Day and TIMESTAMPDIFF(MONTH,`SDate`,CURDATE())%`Interval` = 0)
			or
			(cal.Type = 4 and day(CURDATE())=Day and month(CURDATE())=Month and TIMESTAMPDIFF(YEAR,`SDate`,CURDATE())%`Interval` = 0)
			)
			and 
			(
			(cal.Type = 5 and minute(Time) between minute(CURTIME())-1 and minute(CURTIME()))
			or
			(cal.Type != 5 and Time between ADDTIME(CURTIME() ,'-00:01:00') and ADDTIME(CURTIME() ,'00:00:00'))
			)
  		limit 1000";
$allform = $db->fetchAll($sql);
if(count((array)$allform))
{
	include 'syslogin.php';
}
foreach ($allform as $item)
{
	$formcode = substr($item->FormCode,20,4);
	$form = new Qss_Model_Form();
	$form->init($formcode, 1, 1);
	$service = new Qss_Service();
	$service->Notify->Mail($form,$item->FormCode);
}
if(count((array)$allform))
{
	include 'syslogout.php';
}

//select and send log to email
//Send report
$sql = "select distinct cal.* from 
		qsfsmscalendars as cal
		where 
			(
			(cal.Type = 0 and Date = CURDATE())
			or
			(cal.Type = 5 and hour(CURTIME())%`Interval` = 0)
			or
			(cal.Type = 1 and TIMESTAMPDIFF(DAY,`SDate`,CURDATE())%`Interval` = 0)
			or
			(cal.Type = 2 and weekday(CURDATE())=WDay and TIMESTAMPDIFF(WEEK,`SDate`,CURDATE())%`Interval` = 0)
			or
			(cal.Type = 3 and day(CURDATE())=Day and TIMESTAMPDIFF(MONTH,`SDate`,CURDATE())%`Interval` = 0)
			or
			(cal.Type = 4 and day(CURDATE())=Day and month(CURDATE())=Month and TIMESTAMPDIFF(YEAR,`SDate`,CURDATE())%`Interval` = 0)
			)
			and 
			(
			(cal.Type = 5 and minute(Time) between minute(CURTIME())-1 and minute(CURTIME()))
			or
			(cal.Type != 5 and Time between ADDTIME(CURTIME() ,'-00:01:00') and ADDTIME(CURTIME() ,'00:00:00'))
			)
  		limit 1000";
$allform = $db->fetchAll($sql);
if(count((array)$allform))
{
	include 'syslogin.php';
}
foreach ($allform as $item)
{
	$formcode = substr($item->FormCode,20,4);
	$form = new Qss_Model_Form();
	$form->init($formcode, 1, 1);
	$service = new Qss_Service();
	$service->Notify->Sms($form,$item->FormCode);
}
if(count((array)$allform))
{
	include 'syslogout.php';
}

//send mail
$sql = sprintf('select * from qsmaillogs where Status <> 1 order by Date asc limit 10');
$dataSQL = $db->fetchAll($sql);
/*$sql = sprintf('update qsmaillogs set Status=2 where Status = 0 order by Date asc limit 10');
$db->execute($sql);*/
$mailmodel = new Qss_Model_Mail();
foreach ($dataSQL as $item)
{
	$mail = new Qss_Lib_Mail();
	$to = '';
	$cc = '';
	$bcc = '';
	$attachment = '';
	$arrRefer = array();
	if($item->To)
	{
		foreach (explode(',', $item->To) as $address)
		{
			$mail->addTo($address,'');
		}
	}
	if($item->Cc)
	{
		foreach (explode(',', $item->Cc) as $address)
		{
			$mail->addCC($address,'');
		}
	}
	if($item->Bcc)
	{
		foreach (explode(',', $item->Bcc) as $address)
		{
			$mail->addBCC($address,'');
		}
	}
	if($item->Attachments)
	{
		foreach (explode(',', $item->Attachments) as $attachment)
		{
			$mail->attachFile($attachment,'');
		}
	}
	$mail->setSubject($item->Subject);
	$mail->setBody($item->Body);
	$ret = $mail->send();
	if($ret)
	{
		$mailmodel->logSend($item->MLID,2,$ret);
	}
	else
	{
		$mailmodel->logSend($item->MLID,1);
	}
	//Send sms
	/*$sms = new Qss_Model_Sms();
	$retval =  $sms->sendIt();*/
}
?>