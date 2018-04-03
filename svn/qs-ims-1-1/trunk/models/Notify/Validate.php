<?php
class Qss_Model_Notify_Validate extends Qss_Model_Abstract
{
    public function __construct() {
        parent::__construct();
    }
    
    // include module and report validation
    public function getValidateModule()
    {
        $sql = sprintf('select forms.*,
                        fnc.FormCode as FNCID
                    from qsforms as forms
                    left join qsfvalidatecalendars as fnc
                        on fnc.FormCode = forms.FormCode
                    where forms.Effected = 1
                    order by forms.FormCode');
		return $this->_o_DB->fetchAll($sql);
    }
    
    public function getNotifyMembers($fid, $echo = false)
    {
            $sql = sprintf('select * 
                            from qsfvalidatemembers
                            inner join qsusers on qsusers.UID = qsfvalidatemembers.UID
                            where FormCode = "%1$s"',$fid);
            
            if ($echo) 
            {
                echo $sql; 
                die;
            } 
            else 
            {
                return $this->_o_DB->fetchAll($sql);
            }
    }
    public function save($params)
    {
        $fid     = isset($params['fid'])?$params['fid']:0;
        $action  = isset($params['action'])?$params['action']:0;
        $times   = isset($params['times'])?$params['times']:array();
        $members = isset($params['members'])?$params['members']:array();
        $groups = isset($params['groups'])?$params['groups']:array();
        // add created notify user
        //$members[] = $params['createdid'];
        
        // update db
        if($fid)
        {
        	$this->deleteNotify($fid);
            // qsfvalidatecalendars
            
         // qsfmailcalendars
            if(count($times))
            {
	            $dataNotifyCal   = $this->getUpdateNotifyArr($times, $fid, 1, 2);
	            // insert data
            	$this->_o_DB->execute($dataNotifyCal);
            }
            
			 // qsfnotifyusers
            $dataNotifyMem   = $this->getUpdateNotifyArr($members, $fid, 2, 2);
            if($dataNotifyMem)
            {
                $this->_o_DB->execute($dataNotifyMem);
            }
            //add group
            $sqlGroups = '';
            foreach($groups as $item)
            {
                if($sqlGroups != '')
                {
                	$sqlGroups .= ',';
                }
                $sqlGroups .= "('{$fid}',  {$item})";
            }
            if($sqlGroups)
            {
            	$sql = sprintf('insert into qsfvalidategroups(`FormCode`,`GroupID`)
                    VALUES%1$s;' 
            	,$sqlGroups);
            	 $this->_o_DB->execute($sql);
            }
        }
    }
    
    
    public function deleteNotify($fid)
    {
        $delOldNotifyCal = sprintf('delete from qsfvalidatecalendars where FormCode = "%1$s"', $fid);
        $delOldNotifyMem = sprintf('delete from qsfvalidatemembers where FormCode = "%1$s"', $fid);
        $delOldNotifyGroup = sprintf('delete from qsfvalidategroups where FormCode = "%1$s"', $fid);
        
        $this->_o_DB->execute($delOldNotifyCal);
        $this->_o_DB->execute($delOldNotifyMem);
        $this->_o_DB->execute($delOldNotifyGroup);
    }
    
    
    
    /*
     * '+type+','+times+','+sdate
        +','+edate+','+$(qssform).find('#wday').val()
        +','+dayx+','+$(qssform).find('#month').val()
        +','+interval+'"/>'
     */
    private function getUpdateNotifyArr($insertArr, $fid, $table = 1, $insertType = 1)
    {
        $retval   = "";
        $i        = 0;
        $addSemi  = 0;
        $insert   = ($insertType == 1)?'INSERT INTO':'REPLACE INTO';
        
        // qsfvalidatecalendars
        if($table == 1)
        {
            foreach($insertArr as $time)
            {

                $explode = explode(',',$time);

                
                $retval .= ($addSemi == 1)?', ':'';


                $FID      = $fid;
                $Interval = $explode[7];
                $Type     = $explode[0];
                $Time     = $explode[1];
                $SDate    = ($explode[0] != 0 && $explode[2] != 'undefined' && $explode[2])?
                                        Qss_Lib_Date::displaytomysql($explode[2]):date('Y-m-d');
                $EDate    = ($explode[0] != 0 && $explode[3] != 'undefined' && $explode[3])?
                                        Qss_Lib_Date::displaytomysql($explode[3]):'';
                $Date     = ($explode[0] == 0 && $explode[2] != 'undefined' && $explode[2])?
                                        Qss_Lib_Date::displaytomysql($explode[2]):'';
                $Day      = ($explode[0] == 3 || $explode[0] == 4)?$explode[5]:'';

                $WDay     = ($explode[0] == 2)?$explode[4]:'';
                $Month    = ($explode[0] == 4)?$explode[6]:'';
                $i++;
                
                $retval .= "('{$FID}',  '{$Interval}',  '{$Type}'"
                        . ",  '{$SDate}',  '{$EDate}'"
                        . ",  '{$Date}',  '{$Time}',  '{$Day}'"
                        . ",  '{$WDay}',  '{$Month}')";
                
                $addSemi = ($addSemi == 0)?1:1;
            }
            
            $retval = $retval? sprintf('%1$s  `qsfvalidatecalendars` (
                                `FormCode` ,`Interval` ,
                                `Type` ,`SDate` ,
                                `EDate` ,`Date` ,
                                `Time` ,`Day` ,
                                `WDay` ,`Month`
                                )
                                VALUES
                                %2$s;
                                ',$insert, $retval):'';
        }
        // qsfnotifyusers
        else
        {
            foreach($insertArr as $member)
            {
                $retval .= ($addSemi == 1)?', ':'';
                $retval .= "('{$fid}',  '{$member}')";
                $addSemi = ($addSemi == 0)?1:1;
            }
            
            $retval = $retval? sprintf('%1$s  `qsfvalidatemembers` (
                    `FormCode` ,
                    `UID`
                    )
                    VALUES
                    %2$s;
                    ',$insert, $retval):'';
        }
        return $retval;
    }
 	public function getValidation($fid)
    {
       $sql = sprintf('select * from qsiforms
				inner join qsforms on qsforms.FormCode = qsiforms.FormCode
				where deleted=0 and qsforms.FormCode = "%1$s"
				order by EDate
				limit 1000',$fid);
		return $this->_o_DB->fetchAll($sql);
    }
	public function getFormErrors($fid)
    {
       $sql = sprintf('select * from qsiforms
				where deleted=0 and FormCode = "%1$s" and Error = 1
				order by EDate',$fid);
		return $this->_o_DB->fetchAll($sql);
    }
	public function getNotifyAllGroups($fid)
    {
		$sql = sprintf('select qsgroups.*,qsfvalidategroups.GroupID as ExistsGroupID
                            from qsgroups
                            left join qsfvalidategroups on qsgroups.GroupID =qsfvalidategroups.GroupID and FormCode = "%1$s"
                            ',$fid);
        return $this->_o_DB->fetchAll($sql);
    }  
}
?>