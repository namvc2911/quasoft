<?php

class Qss_Service_Admin_Web_Jscss_Save extends Qss_Service_Abstract
{

	public function __doExecute ($params,Qss_Model_UserInfo $user)
	{
		$file = $params['file'];
		$old_file = $params['old_file'];
		$content = $params['content'];

		$filename = QSS_DATA_DIR . '/jscss/'.$user->user_dept_id . '/'. $file;
		$old_filename = QSS_DATA_DIR . '/jscss/'.$user->user_dept_id . '/'. $old_file;
		$handle = @fopen($filename, 'w');
		$result = @fwrite($handle, $content);
		@fclose($handle);
		if($result && file_exists($old_filename) && $old_file != $file)
		{
			unlink($old_filename);
		}
	}

}
?>