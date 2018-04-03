<?php
$domain = @$argv[1];
if(!$domain)
{
	echo "Domain is required.";
	return;
}

defined('QSS_ROOT_DIR') || define('QSS_ROOT_DIR', dirname(dirname(__FILE__)));
defined('QSS_BASE_URL') || define('QSS_BASE_URL', 'http://' . $domain);
defined('QSS_PUBLIC_DIR') || define('QSS_PUBLIC_DIR', dirname(__FILE__));
//Set library when PHP needed, make it find in this location
set_include_path(implode(PATH_SEPARATOR, array(QSS_ROOT_DIR, /* */
    get_include_path())));
require_once 'configs/Application.php';
require_once 'Qss/Application.php';
//set_time_limit(1);
$application = new Qss_Application(QSS_ROOT_DIR . '/configs/', QSS_CONFIG_FILE);
//$service = new Qss_Service();
//$service->Socket->Import($domain,QSS_SOCKET_PORT);
if (isset($application->options->database)) {
    Qss_Db::factory((array) $application->options->database);
}
$socket = new WebSocket($domain, QSS_SOCKET_PORT);

class WebSocket {

    protected $master;
    protected $sockets = array();
    protected $users = array();
    protected $debug = false;
    protected $address;
    protected $port;

    function __construct($address, $port) {
        error_reporting(E_ALL);
        $this->port = $port;
        set_time_limit(0);
        ob_implicit_flush();

        $this->master = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die("socket_create() failed");
        socket_set_option($this->master, SOL_SOCKET, SO_REUSEADDR, 1) or die("socket_option() failed");
        socket_bind($this->master, '0.0.0.0', $port) or die("socket_bind() failed " . $port);
        socket_listen($this->master, 20) or die("socket_listen() failed");
        socket_set_nonblock($this->master);
        $this->sockets[] = $this->master;
        $this->say("Server Started : " . date('Y-m-d H:i:s'));
        $this->say("Listening on : " . $address . " port " . $port);
        $this->say("Master socket : " . $this->master . "\n");
        if ($this->debug) {
            $this->say("Debugging on\n");
        }

        while (true) {
            $changed = $this->sockets;
            $write = NULL;
            $except = NULL;
            if (!count($changed)) {
                continue;
            }
            socket_select($changed, $write, $except, NULL);
            foreach ($changed as $socket) {
                if ($socket == $this->master) {
                    $client = socket_accept($this->master);
                    if ($client < 0) {
                        $this->log("socket_accept() failed");
                        continue;
                    } else {
                        $this->connect($client);
                    }
                } else {
                    $bytes = @socket_recv($socket, $buffer, 2048, 0);
                    if ($bytes == 0) {
                        $this->disconnect($socket);
                    } else {
                        $user = $this->getuserbysocket($socket);
                        //print_r($user);die;
                        /* if(!$user)
                          {
                          $user = $this->connect($client);
                          } */
                        if (!$user->handshake) {
                            if ($this->dohandshake($user, $buffer)) {
                                //$db = Qss_Db::getAdapter('main');
                                //@todo: gá»­i láº¡i cÃ¡c sms cÃ²n lÆ°u database
                                //laay cac message cá»§a user Ä‘Ã³ 10 tin xong gá»­i select * from aaa wwhere (sendid = uid or reciept=uid) order by date desc limit 10 
                                //loop
                                //$this->send(uid, array(command:send, message:'sdfsdfs')); json
                                //@todo: gá»­i update sá»‘ user online, command lÃ  list
                                //láº¥y táº¥t cáº£ cÃ¡c users trong db
                                //loop vÃ  check xem cÃ³ á»Ÿ trong $this->users khÃ´ng, cÃ³ thÃ¬ lÃ  online, ko cÃ³ lÃ  offline
                                //$allUsersDb = $this->getAllUsersDb();
                           		$msg = new stdClass();
			                    $msg->command = 'displayUserChat'; 
			                    foreach ($this->users as $item) {
			                    	//@todo check xem thằng này đọc hết chưa
							        	$allUsersDb = $this->getAllUsersDb($item->id);
										$userIdAndStatus=$this->getListUserStatus($allUsersDb);
										$msg->data=$userIdAndStatus;
			                        $this->send($item->id, json_encode($msg));
			                    }
                                //xá»­ lÃ½ lá»‡nh cá»§a user (send message)
                                $this->process($user, $this->unwrap($buffer));
                            }
                        } else {
                            //xá»­ lÃ½ lá»‡nh cá»§a user yÃªu cáº§u (send message)
                            $this->process($user, $this->unwrap($buffer));
                        }
                    }
                }
            }
        }
    }

    function process(User &$user, $message) {
        /* Extend and modify this method to suit your needs */
        /* Basic usage is to echo incoming messages back to client */
        $msg = json_decode($message);
        //print_r($msg);
        if (is_object($msg)) {
            switch ($msg->command) {
                case 'send'://gá»­i tin nháº¯n cho user khÃ¡c

                    $this->send($msg->userid, json_encode($msg));
                    //@todo: LÆ°u vÃ o báº£ng chat log
                    $data = array('title' => $msg->message,
                        'sender' => $msg->sender,
                        'receiver' => $msg->userid,
                        'status' => 1,
                    );
                    $this->saveChatLogToDb($data);
                    break;
                case 'list':
                    $chatHistories = $this->getChatLogList($msg->sender, $msg->receiver);
                    $msg->data = $chatHistories;

                    $this->send($msg->sender, json_encode($msg));
                    $this->send($msg->receiver, json_encode($msg));
                    break;
                /*case 'displayUserChat':
                    $allUsersDb = $this->getAllUsersDb();
                    $userIdAndStatus=$this->getListUserStatus($allUsersDb);
                    $msg->data=$userIdAndStatus;
                    foreach ($this->users as $item) {
                        $this->send($item->id, json_encode($msg));
                    }
                    break;*/
            }
        }
    }

//Quang added
    function writeLog($message) {
        $file = 'socketlog.txt';
        $current = file_get_contents($file);
// Append a new person to the file
        $current .= $message . '\n';
// Write the contents back to the file
        file_put_contents($file, $current);
    }

    //return list user, each with status is online or offline
    function getListUserStatus($allUsersDb) {
        foreach ($allUsersDb as &$anUserDb) {
            foreach ($this->users as $item) {
                if ($item->id == $anUserDb->UID) {
                    $anUserDb->online = 1;
                }
            }
        }

        //$this->writeLog(print_r($allUsersDb, TRUE));
        return $allUsersDb;
        //loop this->$users
    }

    function getAllUsersDb($uid) {
        $db = Qss_Db::getAdapter('main');
        if(!$db->isOpen())
        {
        	$db->open();
        }
        $sql = sprintf('SELECT *
        	,(select 1 from qschats where Status = 0 and Receiver = %1$d and qschats.Sender = qsusers.UID) as unread 
        	FROM qsusers',$uid);
        $data = $db->fetchAll($sql);
        $db->close();
        return $data;
    }

    function getChatLogList($sender, $receiver) {
        $db = Qss_Db::getAdapter('main');
    	if(!$db->isOpen())
        {
        	$db->open();
        }
        $sql = 'SELECT * FROM qschats WHERE (Sender =' . $sender . ' AND Receiver=' . $receiver . ') OR (Sender =' . $receiver . ' AND Receiver=' . $sender . ') ORDER BY CID limit 30';
        $data = $db->fetchAll($sql);
        $sql = 'update qschats set Status = 1 WHERE Status = 0 and (Sender =' . $sender . ' AND Receiver=' . $receiver . ') OR (Sender =' . $receiver . ' AND Receiver=' . $sender . ')';
        $db->execute($sql);
        $db->close();
        return $data;
    }

    function saveChatLogToDb($data) {
        $db = Qss_Db::getAdapter('main');
    	if(!$db->isOpen())
        {
        	$db->open();
        }
        $title = $data['title'];
        $title = str_ireplace("'", "&apos;", $title);
        $title = str_ireplace("\\", "&bsol;", $title);
        $title = str_ireplace('"', "&quot;", $title);
        $sql = "INSERT INTO `qschats` (`Title`, `Sender`, `Receiver`, `Status`)
        VALUES (
        '" . $title . "',
        '" . $data['sender'] . "',
        '" . $data['receiver'] . "',
        '0'
        );";
        //echo $sql;
        $db->execute($sql);
        $db->close();
    }

//Finish Quang added
// FIXME throw error if message length is longer than 0x7FFFFFFFFFFFFFFF characters
    function send($userid, $data) {
        //$this->say("> ".$data);
        $client = null;
        foreach ($this->users as $item) {
            if ($item->id == $userid) {
                $client = $item->socket;
            }
        }
        if (!$client) {
            //lÆ°u trÃªn csdl lÃºc connect thÃ¬ send cho user Ä‘Ã³
            return false;
        }
        $header = " ";
        $header[0] = chr(0x81);
        $header_length = 1;

        //Payload length: 7 bits, 7+16 bits, or 7+64 bits
        $dataLength = strlen($data);

        //The length of the payload data, in bytes: if 0-125, that is the payload length.
        if ($dataLength <= 125) {
            $header[1] = chr($dataLength);
            $header_length = 2;
        } elseif ($dataLength <= 65535) {
            // If 126, the following 2 bytes interpreted as a 16
            // bit unsigned integer are the payload length.

            $header[1] = chr(126);
            $header[2] = chr($dataLength >> 8);
            $header[3] = chr($dataLength & 0xFF);
            $header_length = 4;
        } else {
            // If 127, the following 8 bytes interpreted as a 64-bit unsigned integer (the
            // most significant bit MUST be 0) are the payload length.
            $header[1] = chr(127);
            $header[2] = chr(($dataLength & 0xFF00000000000000) >> 56);
            $header[3] = chr(($dataLength & 0xFF000000000000) >> 48);
            $header[4] = chr(($dataLength & 0xFF0000000000) >> 40);
            $header[5] = chr(($dataLength & 0xFF00000000) >> 32);
            $header[6] = chr(($dataLength & 0xFF000000) >> 24);
            $header[7] = chr(($dataLength & 0xFF0000) >> 16);
            $header[8] = chr(($dataLength & 0xFF00 ) >> 8);
            $header[9] = chr($dataLength & 0xFF);
            $header_length = 10;
        }

        $result = @socket_write($client, $header . $data, strlen($data) + $header_length);
        //$result = socket_write($client, chr(0x81) . chr(strlen($data)) . $data, strlen($data) + 2);
        if (!$result) {
            $this->disconnect($client);
            $client = false;
        }
        return true;
        //$this->say("len(".strlen($data).")");
    }

    function connect($socket) {
        $user = new User();
        $user->socket = $socket;
        array_push($this->users, $user);
        array_push($this->sockets, $socket);
        $this->log($socket . " CONNECTED!");
        $this->log(date("d/n/Y ") . "at " . date("H:i:s T"));
    }

    function disconnect($socket) {
        $found = null;
        $n = count($this->users);
        for ($i = 0; $i < $n; $i++) {
            if ($this->users[$i]->socket == $socket) {
                $found = $i;
                break;
            }
        }
        if (!is_null($found)) {
            array_splice($this->users, $found, 1);
            //@todo: gá»­i update sá»‘ user online, giá»‘ng bÃªn trÃªn
            // lÃ´p toÃ n bá»™ trong this->users cÃ¡i list má»›i Ä‘á»ƒ cáº­p nháº­t láº¡i
        }
        $index = array_search($socket, $this->sockets);
        socket_close($socket);
        $this->log($socket . " DISCONNECTED!");
        if ($index >= 0) {
            array_splice($this->sockets, $index, 1);
        }
        //gá»­i ds user 
    	$msg = new stdClass();
		$msg->command = 'displayUserChat'; 
        foreach ($this->users as $item) 
        {
        	//@todo check xem thằng này đọc hết chưa
        	$allUsersDb = $this->getAllUsersDb($item->id);
			$userIdAndStatus=$this->getListUserStatus($allUsersDb);
			$msg->data=$userIdAndStatus;
        	$this->send($item->id, json_encode($msg));
		}
    }

    function dohandshake(User &$user, $buffer) {
        $this->log("\nRequesting handshake...");
        //$this->log($buffer);
        $json = json_decode($buffer);
        if (is_object($json) && $json->PHPSESSID == '*QUASOFT*') {
            $user->handshake = true;
        } else {
            list($resource, $host, $origin, $key1, $key2, $l8b, $key0, $userinfo) = $this->getheaders($buffer);
            $this->log("Handshaking...");
            if ($userinfo) {
                //$port = explode(":",$host);
                //$port = $port[1];
                //$this->log($origin."\r\n".$host);
                $upgrade = "HTTP/1.1 101 WebSocket Protocol Handshake\r\n" .
                        "Upgrade: WebSocket\r\n" .
                        "Connection: Upgrade\r\n" .
                        "Sec-WebSocket-Origin: " . $origin . "\r\n" .
                        "Sec-WebSocket-Accept: " . $this->calcKeyHybi10($key0) . "\r\n" . "\r\n";

                socket_write($user->socket, $upgrade, strlen($upgrade));
                $user->handshake = true;
                $user->userinfo = $userinfo;
                $user->id = $userinfo->user_id;
                //$this->log($upgrade);
                $this->log("Done handshaking...");
            } else {
                $user->handshake = false;
                $this->disconnect($user->socket);
            }
        }
        return $user->handshake;
    }

    function calcKeyHybi10($key) {
        $CRAZY = "258EAFA5-E914-47DA-95CA-C5AB0DC85B11";
        $sha = sha1($key . $CRAZY, true);
        return base64_encode($sha);
    }

    function getheaders($req) {
        $r = $h = $o = $ui = $sk1 = $sk2 = $sk0 = null;
        if (preg_match("/GET (.*) HTTP/", $req, $match)) {
            $r = $match[1];
        }
        if (preg_match("/Host: (.*)\r\n/", $req, $match)) {
            $h = $match[1];
        }
        if (preg_match("/Origin: (.*)\r\n/", $req, $match)) {
            $o = $match[1];
        }
        if (preg_match("/Sec-WebSocket-Key1: (.*)\r\n/", $req, $match)) {
            $this->log("Sec Key1: " . $sk1 = $match[1]);
        }
        if (preg_match("/Sec-WebSocket-Key2: (.*)\r\n/", $req, $match)) {
            $this->log("Sec Key2: " . $sk2 = $match[1]);
        }
        if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $req, $match)) {
            $this->log("new Sec Key2: " . $sk0 = $match[1]);
        }
        if ($match = substr($req, -8)) {
            $this->log("Last 8 bytes: " . $l8b = $match);
        }
        if (preg_match("/PHPSESSID=(.*?)(?:;|\r\n)/", $req, $match)) {
            $sessID = $match[1];
            session_id($sessID);
            @session_start();
            if (isset($_SESSION['userinfo'])) {
                $ui = $_SESSION['userinfo'];
            }
            session_write_close();
        }
        return array($r, $h, $o, $sk1, $sk2, $l8b, $sk0, $ui);
    }

    function getuserbysocket($socket) {
        $found = null;
        foreach ($this->users as $user) {
            if ($user->socket == $socket) {
                $found = $user;
                break;
            }
        }
        /* if(!$found)
          {
          $found = new User();
          $found->socket = $socket;
          //array_push($this->users, $found);
          } */
        return $found;
    }

    function say($msg = "") {
        echo $msg . "\n";
    }

    function log($msg = "") {
        if ($this->debug) {
            echo $msg . "\n";
        }
    }

    function wrap($msg = "") {
        return chr(0) . $msg . chr(255);
    }

    // copied from http://lemmingzshadow.net/386/php-websocket-serverclient-nach-draft-hybi-10/
    function unwrap($data = "") {
        $json = json_decode($data);
        if (is_object($json) && $json->PHPSESSID == '*QUASOFT*') {
            return $data;
        }
        $bytes = $data;
        $dataLength = '';
        $mask = '';
        $coded_data = '';
        $decodedData = '';
        $secondByte = sprintf('%08b', ord($bytes[1]));
        $masked = ($secondByte[0] == '1') ? true : false;
        $dataLength = ($masked === true) ? ord($bytes[1]) & 127 : ord($bytes[1]);
        if ($masked === true) {
            if ($dataLength === 126) {
                $mask = substr($bytes, 4, 4);
                $coded_data = substr($bytes, 8);
            } elseif ($dataLength === 127) {
                $mask = substr($bytes, 10, 4);
                $coded_data = substr($bytes, 14);
            } else {
                $mask = substr($bytes, 2, 4);
                $coded_data = substr($bytes, 6);
            }
            for ($i = 0; $i < strlen($coded_data); $i++) {
                $decodedData .= $coded_data[$i] ^ $mask[$i % 4];
            }
        } else {
            if ($dataLength === 126) {
                $decodedData = substr($bytes, 4);
            } elseif ($dataLength === 127) {
                $decodedData = substr($bytes, 10);
            } else {
                $decodedData = substr($bytes, 2);
            }
        }

        return $decodedData;
    }

}

class User {

    var $id;
    var $socket;
    var $handshake;
    var $userinfo;

}
/*
$domain = @$argv[1];
if(!$domain)
{
	echo "Domain is required.";
	return;
}
defined('QSS_ROOT_DIR') || define('QSS_ROOT_DIR', dirname(dirname(__FILE__)));
defined('QSS_BASE_URL') || define('QSS_BASE_URL', 'http://'.$domain);
defined('QSS_PUBLIC_DIR') || define('QSS_PUBLIC_DIR', dirname(__FILE__));
//Set library when PHP needed, make it find in this location
set_include_path(implode(PATH_SEPARATOR, array(QSS_ROOT_DIR,
get_include_path())));
require_once 'configs/Application.php';
require_once 'Qss/Application.php';
//set_time_limit(1);
$application = new Qss_Application(QSS_ROOT_DIR . '/configs/', QSS_CONFIG_FILE);
//$service = new Qss_Service();
//$service->Socket->Import($domain,QSS_SOCKET_PORT);

$socket = new WebSocket($domain,QSS_SOCKET_PORT);

class WebSocket
{
	protected $master;
	protected $sockets = array();
	protected $users = array();
	protected $debug = false;
	protected $address;
	protected $port;
	protected $files;
	
	function __construct($address,$port)
	{
		$this->files = array();
		error_reporting(E_ALL);
		$this->port = $port;
		set_time_limit(0);
		ob_implicit_flush();

		$this->master=socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die("socket_create() failed");
		socket_set_option($this->master, SOL_SOCKET, SO_REUSEADDR, 1) or die("socket_option() failed");
		socket_bind($this->master, '0.0.0.0', $port) or die("socket_bind() failed ".$port);
		socket_listen($this->master,20) or die("socket_listen() failed");
		socket_set_nonblock($this->master);
		$this->sockets[] = $this->master;
		$this->say("Server Started : ".date('Y-m-d H:i:s'));
		$this->say("Listening on : ".$address." port ".$port);
		$this->say("Master socket : ".$this->master."\n");
		if( $this->debug )
		{
			$this->say("Debugging on\n");
		}

		while(true)
		{
			$changed = $this->sockets;
			$write=NULL;
			$except=NULL;
			if(!count($changed))
			{
				continue;
			}
			socket_select($changed,$write,$except,NULL);
			foreach($changed as $socket)
			{
				if($socket==$this->master)
				{
					$client=socket_accept($this->master);
					if($client<0)
					{
						$this->log("socket_accept() failed");
						continue;
					}
				 	else
				 	{ 
				 		$this->connect($client); 
				 	}
				}
				else
				{
					$bytes = @socket_recv($socket,$buffer,2048,0);
					if($bytes==0)
					{
						$this->disconnect($socket);
					}
					else
					{
						$user = $this->getuserbysocket($socket);
						//print_r($user);die;
						//if(!$user)
						//{
						//	$user = $this->connect($client);
						//}
						if(!$user->handshake)
						{
							if($this->dohandshake($user,$buffer))
							{
								$this->process($user,$this->unwrap($buffer));
							}
						}
						else
						{
							//process user command
							$this->process($user,$this->unwrap($buffer));
						}
					}
				}
			}
		}
	}
	function process(User &$user,$message){
		global $domain;
		$msg = json_decode($message);
		//print_r($msg);
		if(is_object($msg))
		{
			switch ($msg->command)
			{
				case 'startimport':
					//start import, call doImport
	
					//$msg->fileid;
					//$msg->ifid;
					//$msg->fid;
					//$msg->deptid?$msg->deptid:$user->userinfo->user_dept_id;
					//$msg->objid;
					//!$msg->ignore;
					$fileid = substr($msg->fileid,0,strpos($msg->fileid,'.'));
					$this->files[$fileid] = $user;
					$command = sprintf('php %1$s/bin/import.php %2$s %3$s %4$s'
							,QSS_ROOT_DIR
							,$domain
							,base64_encode(serialize($user->userinfo))
							,base64_encode($message));
					if (substr(php_uname(), 0, 7) == "Windows")
					{
				        pclose(popen("start /B ". $command . " >A 2>B", "r")); 
					}
				    else 
				    {
				        exec($command . " >A 2>B &");  
				    } 
					break;
				case 'stopimport':
					//stop import, update user files set status to 0, param is fileid
					$fileid = substr($msg->fileid,0,strpos($msg->fileid,'.'));
					unset($this->files[$fileid]);
					break;
				case 'deleteimport':
					//delete import, unset file in user data
					$fileid = $msg->fileid;
					$msg->status = 3;
					if(isset($this->files[$fileid]))
					{
						$this->send($this->files[$fileid]->socket,json_encode($msg));
					}
					unset($this->files[$fileid]);
					break;
				case 'importstatus':
					//forward to user doing import file know status
					$fileid = $msg->fileid;
					if(isset($this->files[$fileid]))
					{
						$this->send($this->files[$fileid]->socket,$message);
					}
					break;
			}
		}

	}	
// FIXME throw error if message length is longer than 0x7FFFFFFFFFFFFFFF chracters
	function send($client,$data){
		//$this->say("> ".$data);


		$header = " ";
		$header[0] = chr(0x81);
		$header_length = 1;

		//Payload length: 7 bits, 7+16 bits, or 7+64 bits
		$dataLength = strlen($data);

		//The length of the payload data, in bytes: if 0-125, that is the payload length.
		if($dataLength <= 125)
		{
			$header[1] = chr($dataLength);
			$header_length = 2;
		}
		elseif ($dataLength <= 65535)
		{
			// If 126, the following 2 bytes interpreted as a 16
			// bit unsigned integer are the payload length.

			$header[1] = chr(126);
			$header[2] = chr($dataLength >> 8);
			$header[3] = chr($dataLength & 0xFF);
			$header_length = 4;
		}
		else
		{
			// If 127, the following 8 bytes interpreted as a 64-bit unsigned integer (the
			// most significant bit MUST be 0) are the payload length.
			$header[1] = chr(127);
			$header[2] = chr(($dataLength & 0xFF00000000000000) >> 56);
			$header[3] = chr(($dataLength & 0xFF000000000000) >> 48);
			$header[4] = chr(($dataLength & 0xFF0000000000) >> 40);
			$header[5] = chr(($dataLength & 0xFF00000000) >> 32);
			$header[6] = chr(($dataLength & 0xFF000000) >> 24);
			$header[7] = chr(($dataLength & 0xFF0000) >> 16);
			$header[8] = chr(($dataLength & 0xFF00 ) >> 8);
			$header[9] = chr( $dataLength & 0xFF );
			$header_length = 10;
		}

		$result = @socket_write($client, $header . $data, strlen($data) + $header_length);
		//$result = socket_write($client, chr(0x81) . chr(strlen($data)) . $data, strlen($data) + 2);
		if ( !$result ) {
			$this->disconnect($client);
			$client = false;
		}
		//$this->say("len(".strlen($data).")");
	}

	function connect($socket)
	{
		$user = new User();
		$user->socket = $socket;
		array_push($this->users,$user);
		array_push($this->sockets,$socket);
		$this->log($socket." CONNECTED!");
		$this->log(date("d/n/Y ")."at ".date("H:i:s T"));
	}

	function disconnect($socket)
	{
		$found=null;
    	$n=count($this->users);
    	for($i=0;$i<$n;$i++)
    	{
      		if($this->users[$i]->socket==$socket)
      		{ 
      			$found=$i; 
      			break; 
      		}
	    }
	    if(!is_null($found))
	    { 
	    	array_splice($this->users,$found,1); 
	    }
    	$index=array_search($socket,$this->sockets);
		socket_close($socket);
		$this->log($socket." DISCONNECTED!");
		if($index>=0)
		{
			array_splice($this->sockets,$index,1);
		}
	}

	function dohandshake(User &$user,$buffer){
		$this->log("\nRequesting handshake...");
		//$this->log($buffer);
		$json = json_decode($buffer);
		if(is_object($json) && $json->PHPSESSID == '*QUASOFT*')
		{
			$user->handshake=true;
		}
		else 
		{
			list($resource,$host,$origin,$key1,$key2,$l8b,$key0,$userinfo) = $this->getheaders($buffer);
			$this->log("Handshaking...");
			if($userinfo)
			{
				//$port = explode(":",$host);
				//$port = $port[1];
				//$this->log($origin."\r\n".$host);
				$upgrade = "HTTP/1.1 101 WebSocket Protocol Handshake\r\n" .
		                "Upgrade: WebSocket\r\n" .
		                "Connection: Upgrade\r\n" .
		                "Sec-WebSocket-Origin: " . $origin . "\r\n" .
		                "Sec-WebSocket-Accept: " . $this->calcKeyHybi10($key0) . "\r\n" . "\r\n" ;
	
				socket_write($user->socket,$upgrade,strlen($upgrade));
				$user->handshake=true;
				$user->userinfo=$userinfo;
				$user->id = $userinfo->user_id;
				//$this->log($upgrade);
				$this->log("Done handshaking...");
			}
			else
			{
				$user->handshake=false;
				$this->disconnect($user->socket);
			}
		}
		return $user->handshake;
	}

	function calcKeyHybi10($key){
		$CRAZY = "258EAFA5-E914-47DA-95CA-C5AB0DC85B11";
		$sha = sha1($key.$CRAZY,true);
		return base64_encode($sha);
	}

	function getheaders($req){
		$r=$h=$o=$ui=$sk1=$sk2=$sk0=null;
		if(preg_match("/GET (.*) HTTP/" ,$req,$match))
		{
			$r=$match[1];
		}
		if(preg_match("/Host: (.*)\r\n/" ,$req,$match))
		{
			$h=$match[1];
		}
		if(preg_match("/Origin: (.*)\r\n/" ,$req,$match))
		{
			$o=$match[1];
		}
		if(preg_match("/Sec-WebSocket-Key1: (.*)\r\n/",$req,$match))
		{
			$this->log("Sec Key1: ".$sk1=$match[1]);
		}
		if(preg_match("/Sec-WebSocket-Key2: (.*)\r\n/",$req,$match))
		{
			$this->log("Sec Key2: ".$sk2=$match[1]);
		}
		if(preg_match("/Sec-WebSocket-Key: (.*)\r\n/" ,$req,$match))
		{
			$this->log("new Sec Key2: ".$sk0=$match[1]);
		}
		if($match=substr($req,-8))
		{
			$this->log("Last 8 bytes: ".$l8b=$match);
		}
		if(preg_match("/PHPSESSID=(.*?)(?:;|\r\n)/", $req, $match))
		{
			$sessID = $match[1];
			session_id($sessID);
			@session_start();
			if(isset($_SESSION['userinfo']))
			{
				$ui = $_SESSION['userinfo'];
			}
			session_write_close();
		}
		return array($r,$h,$o,$sk1,$sk2,$l8b,$sk0,$ui);
	}

	function getuserbysocket($socket)
	{
		$found=null;
		foreach($this->users as $user)
		{
			if($user->socket == $socket)
			{
				$found=$user; 
				break;
			}
		}
		//if(!$found)
		//{
		//	$found = new User();
		//	$found->socket = $socket;
		//	//array_push($this->users, $found);
		//}
		return $found;
	}

	function say($msg=""){ echo $msg."\n"; }
	function log($msg=""){ if($this->debug){ echo $msg."\n"; } }
	function wrap($msg=""){ return chr(0).$msg.chr(255); }

	// copied from http://lemmingzshadow.net/386/php-websocket-serverclient-nach-draft-hybi-10/
	function unwrap($data="")
	{
		$json = json_decode($data);
		if(is_object($json) && $json->PHPSESSID == '*QUASOFT*')
		{
			return $data;
		}
		$bytes = $data;
		$dataLength = '';
		$mask = '';
		$coded_data = '';
		$decodedData = '';
		$secondByte = sprintf('%08b', ord($bytes[1]));
		$masked = ($secondByte[0] == '1') ? true : false;
		$dataLength = ($masked === true) ? ord($bytes[1]) & 127 : ord($bytes[1]);
		if($masked === true)
		{
			if($dataLength === 126)
			{
				$mask = substr($bytes, 4, 4);
				$coded_data = substr($bytes, 8);
			}
			elseif($dataLength === 127)
			{
				$mask = substr($bytes, 10, 4);
				$coded_data = substr($bytes, 14);
			}
			else
			{
				$mask = substr($bytes, 2, 4);
				$coded_data = substr($bytes, 6);
			}
			for($i = 0; $i < strlen($coded_data); $i++)
			{
				$decodedData .= $coded_data[$i] ^ $mask[$i % 4];
			}
		}
		else
		{
			if($dataLength === 126)
			{
				$decodedData = substr($bytes, 4);
			}
			elseif($dataLength === 127)
			{
				$decodedData = substr($bytes, 10);
			}
			else
			{
				$decodedData = substr($bytes, 2);
			}
		}

		return $decodedData;
	}
}
class User{
	var $fileid;
	var $id;
	var $socket;
	var $handshake;
	var $userinfo;
	//var $files = array();
}*/
?>