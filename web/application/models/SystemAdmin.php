<?php

class EOJ_Model_SystemAdmin extends EOJ_Model_Adminstrator
{
	public function AppointAdminstrator($userName,$passWord,$userInformation,$power){
		//
		$connection=mysql_connect("localhost","eojapp","ecust")
		or die("Couldn't connect to server");
		$db=mysql_select_db("eojdb",$connection)
		or die("Couldn't select database");
		//
		$callsql="select count(*) from uploader where user_name='$userName'";
		$result = mysql_query($callsql, $connection) or die("daodiyoumeiyouyong:".mysql_error());
		$row=mysql_fetch_array($result);
		if($row[0]!=0)
			return -2;

		$pw=md5($passWord);
		$callsql="call ADDUPER('$userName','$pw','$userInformation','$power')";
		$result = mysql_query($callsql, $connection) or die("daodiyoumeiyouyong:".mysql_error());
		$row=mysql_fetch_array($result);
		$id=$row['ouploader_id'];
		$oec=$row['oexitcode'];
		//
		mysql_close($connection) or die("daodiyoumeiyouyong:".mysql_error());
		//
		if($oec==0){
			return $id;
		}else{
			return $oec;//对应 1 即mysql error 对应 -1 即null value
		}
	}

}

