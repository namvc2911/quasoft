<?php

class Qss_Service_Mail_Send extends Qss_Service_Abstract
{

	public function __doExecute ($params)
	{
		//!isset($params['tos']) && !isset($params['ccs']) && !isset($params['bccs']) && 
		if(!$params['tolist'] && !$params['cclist'] && !$params['bcclist'])
		{
			$this->setError();
			$this->setMessage('Địa chỉ gửi đi yêu cầu bắt buộc');
			return false;
		}
		$time = date('H:i');
		$event = new Qss_Model_Event();
		$mailmodel = new Qss_Model_Mail();
		$domodel = new Qss_Model_Document();
		$setting = $mailmodel->getAccountById($params['account']);
		if($setting)
		{
			//$mail = new Qss_Lib_Mail();
			$to = '';
			$cc = '';
			$bcc = '';
			$attachment = '';
			$attachmentdesc = '';
			$arrRefer = array();
			if(isset($params['tos']))
			{
				foreach ($params['tos'] as $address)
				{
					$arr = explode(',', $address);
					if(count($arr) == 3)
					{
						if($arr[2] && Qss_Validation::isEmail($arr[2]))
						{
							$arrRefer[] = array($arr[0],$arr[1]);
							//$mail->addTo($arr[2], $arr[2]);
							if($to)
							{
								$to .= ',';
							}
							$to .= $arr[2];
						}
					}

				}
			}
			if(isset($params['ccs']))
			{
				foreach ($params['ccs'] as $address)
				{
					$arr = explode(',', $address);
					if(count($arr) == 3)
					{
						if($arr[2] && Qss_Validation::isEmail($arr[2]))
						{
							$arrRefer[] = array($arr[0],$arr[1]);
							//$mail->addCC($arr[2], $arr[2]);
							if($cc)
							{
								$cc .= ',';
							}
							$cc .= $arr[2];
						}
					}

				}
			}
			if(isset($params['bccs']))
			{
				foreach ($params['bccs'] as $address)
				{
					$arr = explode(',', $address);
					if(count($arr) == 3)
					{
						if($arr[2] && Qss_Validation::isEmail($arr[2]))
						{
							$arrRefer[] = array($arr[0],$arr[1]);
							//$mail->addBCC($arr[2], $arr[2]);
							if($bcc)
							{
								$bcc .= ',';
							}
							$bcc .= $arr[2];
						}
					}

				}
			}
			/*
			if($params['tolist'])
			{
				$tolist = $mailmodel->getMailListById($params['tolist']);
				foreach ($tolist as $item)
				{
					if($item->Data && Qss_Validation::isEmail($item->Data))
					{
						$arrRefer[] = array($item->IFID,$item->IOID);
						$mail->addTo($item->Data, $item->Data);
					}
				}
			}
			if($params['cclist'])
			{
				$cclist = $mailmodel->getMailListById($params['cclist']);
				foreach ($cclist as $item)
				{
					if($item->Data && Qss_Validation::isEmail($item->Data))
					{
						$arrRefer[] = array($item->IFID,$item->IOID);
						$mail->addCC($item->Data, $item->Data);
					}
				}
			}
			if($params['bcclist'])
			{
				$bcclist = $mailmodel->getMailListById($params['bcclist']);
				foreach ($bcclist as $item)
				{
					if($item->Data && Qss_Validation::isEmail($item->Data))
					{
						$arrRefer[] = array($item->IFID,$item->IOID);
						$mail->addBCC($item->Data, $item->Data);
					}
				}
			}
			*/
			
			if(isset($params['docs']))
			{
				foreach ($params['docs'] as $docid)
				{
					$doc = $domodel->getById($docid, $params['createdid']);
					if($doc)
					{
						$source = QSS_DATA_DIR . '/documents/' . $doc->DID . '.' . $doc->Ext;
						if(file_exists($source))
						{
							//$mail->attachFile($source, $doc->Name);
							$attachmentdesc .= '>>Attachment: ' . $doc->Name;
							if($attachment)
							{
								$attachment .= ',';
							}
							$attachment .= $source;
						}
					}

				}
			}
			$mailmodel->logMail($params['subject'], $params['body'], $to, $cc, $bcc, $attachments);
			/*$mail->setSubject($params['subject']);
			$mail->setBody($params['body']);


			$mail->_mail->Host = $setting->Server; // SMTP server
			$mail->_mail->Port = $setting->Port;
			$mail->_mail->SMTPSecure = 'ssl';
			$mail->_mail->Username = $setting->Account; // your SMTP username or your gmail username
			$mail->_mail->Password = base64_decode($setting->Password); // your SMTP password or your gmail password
			$mail->_mail->From = $setting->Account;
			$mail->_mail->FromName = $setting->Name;
			$ret = $mail->send();
			if($ret)
			{
				$this->setError();
				$this->setMessage('Không thể gửi mail: '.$ret);
			}
			else
			*/
			{
				$data = array('id'=>0,
							'name'=>'Mailer: '.$params['subject'],
							'desc'=>$params['body'].$attachmentdesc,
							'type'=>3,
							'alarm'=>0,
							'repeat'=>1,
							'active'=>1,
							'createdid'=>$params['createdid'],
							'uid'=>'',
							'public'=>0,
							'location'=>'',
							'caltype'=>0,
							'sdate'=>date('d-m-Y'),
							'edate'=>'',
							'stime'=>$time,
							'etime'=>date('H:i'),
							'status'=>2
				);
					
				$id = $event->save($data);
				if($id)
				{
					foreach ($arrRefer as $item)
					{
						$event->saveRefer($id, $item[0], $item[1]);
					}
				}
				Qss_Cookie::set('event_selected', $id);
			}
		}
		else
		{
			$this->setError();
			$this->setMessage('Không tìm được cấu hình gửi mail');
		}
		//$this->setError();
		//$event->save($params);
	}

}
?>