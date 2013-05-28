<?php

class EOJ_Model_SubmitCode
{
	private $WorkDirectionary;
	private $User;
	private $connect;
	private function GetPara(){
		$dom=new DOMDocument;
		//$dom->load("eoj.xml");
		//$WorkDirectionary=$dom->getElementsByTagName("work_dir");
		$WorkDirectionary='/';
	}
	private function LinkDataBase(){
		$this->connect=mysql_connect("localhost","root","123456")
		or die("Couldn't connect to server");
		$db=mysql_select_db("eojdb",$this->connect)
		or die("Couldn't select database");
	}
	public function __construct(){
		$this->LinkDataBase();
		$this->GetPara();
	}
	public function SubmitCode($code,$UserID,$ProblemID,$CodeType){
		//$RunID;
		mysql_query("SET @a=".$UserID,$this->connect);
		mysql_query("SET @b=".$ProblemID,$this->connect);
		$run_pro_sql = "CALL ADDANS(@a,@b);";
		$result = mysql_query($run_pro_sql, $this->connect) or die("Query Invalid:".mysql_error());
		$row=mysql_fetch_array($result);
		$RunID=$row['orun_id'];
		$FileName=$RunID."-".$ProblemID."-".$UserID.".".$CodeType;
		$File=$WorkDirectionary."/".$FileName;
		$handle=fopen($File,"w");
		flock($handle,LOCK_EX | LOCK_SH);
		if(fwrite($handle, $code)==false){
			return "Can not write file";
		}
		flock($handle, LOCK_UN);
		fclose($handle);
		return "Succeed";
	}
	public function GetResult(){
		$run_pro_sql = "CALL (@a,@b);";//should add sth
		echo $this->connect;
		$result = mysql_query($run_pro_sql, $this->connect) or die("Query Invalid:".mysql_error());
		while($row=mysql_fetch_array($result)){
			$array[$row['run_id']]=$row;
		}
		return $array;
	}
	public function __destruct(){
		mysql_close($this->connect);
	}

}

