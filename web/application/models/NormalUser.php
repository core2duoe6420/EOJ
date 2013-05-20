<?php

class Application_Model_NormalUser
{

	private $UserName;
	private $UserID;
	private $PassWord;
	private $SubmitCount;
	private $AcceptCount;
//	private $db;
	private $connection;
	public function LinkDataBase($User,$PassWord){
		$connection=mysql_connect("",$User,$PassWord)
		or die("Couldn't connect to server");
		$db=mysql_select_db("eojdb",$connection)
		or die("Couldn't select database");
		$UserName=$User;
		$PassWord=$PassWord;
	}
	public function GetNormalUser(){
		$result = mysql_query("select * from eojuser where user_name='$UserName'", $connection) or die("Query Invalid:".mysql_error());
		$row=mysql_fetch_array($result);
		$UserID=$row['user_id'];
		$SubmitCount=$row['user_tsubmit'];
		$AcceptCount=$row['user_acc'];
	}
	public function ReturnUserName(){
		return $UserName;
	}
	public function ReturnUserID(){
		return $UserID;
	}
	public function ReturnAcceptRate(){
		$rate=$AcceptCount/$SubmitCount;
		return $rate;
	}
	public function ReturnAcceptCount(){
		return $AcceptCount;
	}
	public function ReturnSubmitCount(){
		return $SubmitCount;
	}
	public function ReturnConnection(){
		return $connection;
	}
	public _destruct(){
		mysql_close($connection);
	}
	public function ReturnAcceptedProblemList(){
		$result = mysql_query("select run_pid from run where run_uid=$UserID and run_result=1", $connection) or die("Query Invalid:".mysql_error());
		$i=0;
		while($row=mysql_fetch_array($result)){
			$array[$i]=$row['run_pid'];
			$i=$i+1;
		}
		return $array;
	}
	public function ReturnCode($pId,$language=1){
		$result=mysql_query("select run_codeloc from run where run_pid=$pid and run_codetype=$language and run_uid=$UserID ",$connection) or die("Query Invalid:".mysql_error());
		//$code="";
		$row=mysql_fetch_array($result);
		$path=$row['run_codeloc'];
		//$handle=fopen($FileName,"r");
		$code=file_get_contents($path);
		//fclose($handle);
		return $code;
	}

}

