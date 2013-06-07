<?php

class EOJ_Model_Adminstrator
{
	protected $ID;//uploader_id
	protected $name;//user_name
	protected $password;//user_passwd
	protected $briefInfor;//user_info
	protected $power;//user_privis
	protected $connection;
	public function Login($User,$PassWord,$Power){
		$this->connection=mysql_connect("localhost","eojapp","ecust")
		or die("Couldn't connect to server");
		$db=mysql_select_db("eojdb",$this->connection)
		or die("Couldn't select database");
		$this->name=$User;
		$this->password=md5($PassWord);
//		$this->power=$Power;
		$result = mysql_query("select user_privis,user_passwd,uploader_id,user_info from uploader where user_name='$User'", $this->connection) or die("Query Invalid:".mysql_error());
		$row=mysql_fetch_array($result);
		if($row==false){
			return -3;//no such user
		}
		if ($row['user_privis']!=$Power) {
			return -2;//power false
		}
		if($row['user_passwd']!=$this->password){
			return -1;//password error
		}
		$this->power=$row['user_privis'];
		$this->ID=$row['uploader_id'];
		$this->briefInfor=$row['user_info'];
//		$res=array("ID"=>$this->ID,"power"=>$this->power);
		return $this->ID;
	}
	
	//getinfo
	
	
	public function getID(){
		return $this->ID;
	}
	public function getName(){
		return $this->name;
	}
	public function getPassword(){
		return $this->passWord;
	}
	public function getbriefInfor(){
		return $this->briefInfor;
	}
	public function getpower(){
		return $this->power;
	}
	public function getconnection(){
		return $this->connection;
	}
	public function __destruct(){
		@mysql_close($this->connection);// or die("daodiyoumeiyouyong:".mysql_error());
	}
	public function ChangePassword($ID,$newPW){
		//
		$this->connection=mysql_connect("localhost","eojapp","ecust")
		or die("Couldn't connect to server");
		$db=mysql_select_db("eojdb",$this->connection)
		or die("Couldn't select database");
		//
		$pw=md5($newPW);
		$sqlquery="call CHPASSWD('$ID',1,'$pw')";
		$result=mysql_query($sqlquery,$this->connection) or die("Query Invalid:".mysql_error());
		$row=mysql_fetch_array($result);
		$oec=$row['oexitcode'];
		//
		mysql_close($this->connection) or die("daodiyoumeiyouyong:".mysql_error());
		//
		return $oec;//对应 0 即成功完成 对应 -1 即（不允许的）null value found 对应 1 即 mysql error 对应 2 即输入参数值有误 对应 3 即 update未成功，但不算mysql error，可能是未找到数据
	}
	
	public function Getbriefinformation($ID){
		//
		$this->connection=mysql_connect("localhost","eojapp","ecust")
		or die("Couldn't connect to server");
		$db=mysql_select_db("eojdb",$this->connection)
		or die("Couldn't select database");
		//
		$sqlquery="select user_info
					from uploader
					where uploader_id='$ID'";
		$result=mysql_query($sqlquery,$this->connection) or die("Query Invalid:".mysql_error());
		$row=mysql_fetch_array($result);
		//
		mysql_close($this->connection) or die("daodiyoumeiyouyong:".mysql_error());
		//

		if ($row==null) {
			return -1;
		}else{
			$this->briefInfor=$row['user_info'];
		}
		return $this->briefInfor;
	}
}

