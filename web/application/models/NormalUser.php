<?php

class EOJ_Model_NormalUser
{
	private $UserName;
	private $UserID;
	private $PassWord;
	private $SubmitCount;
	private $AcceptCount;
//	private $db;
	private $connection;
	private function LinkDataBase(){
		$this->connection=mysql_connect("localhost","eojapp","ecust")
		or die("Couldn't connect to server");
		//Zend_Registry::set('connection',$this->connection)
		$db=mysql_select_db("eojdb",$this->connection);
		//or die("Couldn't select database");
	}
	public function Login($UserN,$Pass_Word){
		$this->LinkDataBase();
		$result = mysql_query("select * from eojuser where user_name='$UserN'", $this->connection) or die("Query Invalid:".mysql_error());
		if($result==false){
			return -3;//database query false
		}
		$row=mysql_fetch_array($result);
		if($row==false){
			return -2;//No Such User
		}
		$PassWordBuff=$row['user_passwd'];
		$this->PassWord=($Pass_Word);
		if($PassWordBuff!=$this->PassWord){
			return -1;//Wrong PassWord
		}
		$this->UserName=$UserN;
		$this->UserID=$row['user_id'];
		$this->SubmitCount=$row['user_tsubmit'];
		$this->AcceptCount=$row['user_acc'];
		return $this->UserID;
	}
	//
	public function getInfo($UserName){
		$this->LinkDataBase();
		$result = mysql_query("select * from eojuser where user_name='$UserName'", $this->connection) or die("Query Invalid:".mysql_error());
		
		$row=mysql_fetch_array($result);
		
		if($row)
		{
			$this->UserName=$row['user_name'];
			$this->UserID=$row['user_id'];
			$this->SubmitCount=$row['user_tsubmit'];
			$this->AcceptCount=$row['user_acc'];
			return true;
		}
		else
			return false;
	}
	
	public function ReturnUserName(){
		return $this->UserName;
	}
	public function ReturnUserID(){
		return $this->UserID;
	}
	//
	public function ReturnAcceptRate(){
		$rate=$this->AcceptCount/$this->SubmitCount;
		return $rate;
	}
	public function ReturnAcceptCount(){
		return $this->AcceptCount;
	}
	public function ReturnSubmitCount(){
		return $this->SubmitCount;
	}
	public function ReturnConnection(){
		return $this->connection;
	}
	public function __construct()
	{
		$this->LinkDataBase();
	}
	public function __destruct(){
		@mysql_close($this->connection);
	}
	public function ReturnAcceptedProblemList(){
		$result = mysql_query("select run_pid from run where run_uid=$UserID and run_result=1", $this->connection) or die("Query Invalid:".mysql_error());
		$i=0;
		while($row=mysql_fetch_array($result)){
			$array[$i]=$row['run_pid'];
			$i=$i+1;
		}
		return $array;
	}
	public function ReturnCode($RunId){
		$result=mysql_query("select run_codeloc from run where run_id ='$RunId'",$this->connection) or die("Query Invalid:".mysql_error());
		//$code="";
		$row=mysql_fetch_array($result);
		$path=$row['run_codeloc'];
		//$handle=fopen($FileName,"r");
		$code=file_get_contents($path);
		//fclose($handle);
		return $code;
	}
	
	public function Register($UserN,$Pass_Word){
		$this->LinkDataBase();
		$pw=md5($Pass_Word);
		$ID=0;
		$res=0;
		$sqlquery="call ADDEOJUSER('$UserN','$pw')";
		mysql_query($sqlquery,$this->connection) or die("Query Invalid:".mysql_error());
		mysql_close($this->connection);
		return $res;//0 success other error;
	}
	
	public function ChangePassword($ID,$newPW){
		//
		$connection=mysql_connect("localhost","eojapp","ecust")
		or die("Couldn't connect to server");
		$db=mysql_select_db("eojdb",$connection)
		or die("Couldn't select database");
		//
		$pw=md5($newPW);
		$sqlquery="call CHPASSWD('$ID',2,'$pw')";
		$result=mysql_query($sqlquery,$connection) or die("Query Invalid:".mysql_error());
		$row=mysql_fetch_array($result);
		$oec=$row['oexitcode'];
		//
		mysql_close($connection) or die("daodiyoumeiyouyong:".mysql_error());
		//
		return $oec;//对应 0 即成功完成 对应 -1 即（不允许的）null value found 对应 1 即 mysql error 对应 2 即输入参数值有误 对应 3 即 update未成功，但不算mysql error，可能是未找到数据
	}
}

