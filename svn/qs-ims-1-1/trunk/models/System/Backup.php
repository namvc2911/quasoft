<?php
class Qss_Model_System_Backup extends Qss_Model_Abstract
{
	function __construct ()
	{
		parent::__construct();
	}

	//-----------------------------------------------------------------------

	function restore($dbname)
	{
		$options = (array) Qss_Register::get('configs')->database;
		$options = $options['main'];
		$host = $options->host;
		$user =  $options->username;
		$pass =  $options->password;
		$name = $options->database;
		$link = mysql_connect($host,$user,$pass);
		$templine = '';
		$filename = QSS_ROOT_DIR . '/backup/'.$dbname;
		if(file_exists($filename))
		{
			$sql = 'DROP DATABASE cmms';
			mysql_query($sql);
			$sql = sprintf('CREATE DATABASE cmms CHARACTER SET utf8 COLLATE utf8_general_ci');
			mysql_query($sql);
			mysql_select_db($name,$link);
			$lines = file($filename);
			// Loop through each line
			foreach ($lines as $line)
			{
				// Skip it if it's a comment
				if (substr($line, 0, 2) == '--' || $line == '')
				continue;

				// Add this line to the current segment
				$templine .= $line;
				// If it has a semicolon at the end, it's the end of the query
				if (substr(trim($line), -1, 1) == ';')
				{
					// Perform the query
					mysql_query($templine);
					// Reset temp variable to empty
					$templine = '';
				}
			}
		}
	}
	function backup($dbfilename)
	{
		$options = (array) Qss_Register::get('configs')->database;
		$options = $options['main'];
		$host = $options->host;
		$user =  $options->username;
		$pass =  $options->password;
		$name = $options->database;
		$link = mysql_connect($host,$user,$pass);
		mysql_select_db($name,$link);

		//get all of the tables
		$tables = array();
		$result = mysql_query('SHOW TABLES');
		while($row = mysql_fetch_row($result))
		{
			$tables[] = $row[0];
		}

		//cycle through
		foreach($tables as $table)
		{
			$result = mysql_query('SELECT * FROM '.$table);
			$num_fields = mysql_num_fields($result);

			$return.= 'DROP TABLE '.$table.';';
			$row2 = mysql_fetch_row(mysql_query('SHOW CREATE TABLE '.$table));
			$return.= "\n\n".$row2[1].";\n\n";

			for ($i = 0; $i < $num_fields; $i++)
			{
				while($row = mysql_fetch_row($result))
				{
					$return.= 'INSERT INTO '.$table.' VALUES(';
					for($j=0; $j < $num_fields; $j++)
					{
						$row[$j] = addslashes($row[$j]);
						$row[$j] = ereg_replace("\n","\\n",$row[$j]);
						if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
						if ($j < ($num_fields-1)) { $return.= ','; }
					}
					$return.= ");\n";
				}
			}
			$return.="\n\n\n";
		}
		if ( !is_dir(QSS_ROOT_DIR . '/backup/') )
		{
			mkdir(QSS_ROOT_DIR . '/backup/');
		}
		//save file
		$handle = fopen(QSS_ROOT_DIR . '/backup/'.$dbfilename,'w+');
		fwrite($handle,$return);
		fclose($handle);
	}
}
?>
