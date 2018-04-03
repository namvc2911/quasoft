<?php
/**
 *
 * @author HuyBD
 *
 */
require (dirname(__FILE__) . "/Mail/PHPMailerAutoload.php");
include_once (dirname(__FILE__) . "/Mail/class.phpmailer.php");

class Qss_Lib_Mail
{

	/**
	 *
	 * @var Mail object
	 */
	public $_mail;

	protected $_subject = 'No Subject';

	protected $_body = '';

	public function __construct ()
	{
		$this->_mail = new PHPMailer();
		$this->_mail->CharSet = 'UTF-8';
		$this->_mail->IsSMTP(); // telling the class to use SMTP
		$this->_mail->Host = "smtp.gmail.com"; // SMTP server
		$this->_mail->Port = 465;
		$this->_mail->SMTPAuth = true; // turn on SMTP authentication
		$this->_mail->SMTPSecure = 'ssl';
		$this->_mail->Username = "noreply.quasoft"; // your SMTP username or your gmail username
		$this->_mail->Password = "Quasoft@1234#"; // your SMTP password or your gmail password
		$this->_mail->From = "noreply.quasoft@gmail.com";
		$this->_mail->FromName = "Quasoft CMMS";
		$this->_mail->AltBody = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
	}

	public function setSubject($subject)
	{
		$this->_subject = $subject;
	}
	public function setBody ($body)
	{
		$this->_body = $body;
	}

	public function addTo ($toaddress, $name)
	{
		$this->_mail->AddAddress($toaddress, $name);
	}

	public function addCC ($CCaddress, $name)
	{
		$this->_mail->AddCC($CCaddress, $name);
	}

	public function addBCC ($BCCaddress, $name)
	{
		$this->_mail->AddBCC($BCCaddress, $name);
	}
	
	public function attachFile($source, $filename)
	{
		$this->_mail->AddAttachment($source, $filename);
	}
	public function send ()
	{
		$this->_mail->Subject = $this->_subject;
		$this->_mail->MsgHTML($this->_body);
		if ( !$this->_mail->Send() )
		return $this->_mail->ErrorInfo;
		else
		return false;
	}

}
?>