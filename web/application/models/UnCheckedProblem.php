<?php

class EOJ_Model_UnCheckedProblem extends EOJ_Model_Problem
{
	private $uploadID;
	private $uploaderID;
	private $upTime;
	private $handoutStatus;
	public function SetUploadID($ID){
		$this->uploadID=$ID;
		$this->connection=mysql_connect("localhost","eojapp","ecust")
		or die("Couldn't connect to server");
		$db=mysql_select_db("eojdb",$this->connection)
		or die("Couldn't select database");
		//get problem all information
		$run_pro_sql = "select p_title,p_tlimt,p_mlimt,user_name,up_time,
		                p_desc,p_sampleinput,p_sampleoutput,handout_Status,
		                p_input_tips,p_output_tips,p_hint,uploadit.uploader_id,
		                p_specjg,p_lang 
		                from uploadit,uploader 
		                where uploadit.uploader_id=uploader.uploader_id and up_id='$ID'";
		$result = mysql_query($run_pro_sql, $this->connection) or die("Query Invalid:".mysql_error());
		$row=mysql_fetch_array($result);
		//
		if($row)
		{
			$this->problemName=$row['p_title'];
			$this->timeLimit=$row['p_tlimt'];
			$this->memoryLimit=$row['p_mlimt'];
			$this->discription=$row['p_desc'];
			$this->sampleInput=$row['p_sampleinput'];
			$this->sampleOutput=$row['p_sampleoutput'];
			$this->source=$row['user_name'];
			$this->inputTips=$row['p_input_tips'];
			$this->outputTips=$row['p_output_tips'];
			$this->hint=$row['p_hint'];
			$this->uploaderID=$row['uploader_id'];//
			$this->handoutStatus=$row['handout_Status'];//
			$this->upTime=$row['up_time'];//
			$this->specialJudge=$row['p_specjg'];
			$this->language=$row['p_lang'];
			mysql_close($this->connection);
			//return $this->handoutStatus;
			return true;
		}
		else
			return false;
	}
	public function GetSpecialJudge(){
		return $this->specialJudge;
	}
	public function Getlanguage(){
		return $this->language;
	}
	public function GetUpTime(){
		return $this->upTime;
	}
	public function GethandoutStatus(){
		return $this->handoutStatus;
	}
	public function GetuploaderID(){
		return $this->uploaderID;
	}
	public function GetuploadID(){
		return $this->uploadID;
	}
	public function GetproblemName(){
		return $this->problemName;
	}
	public function GettimeLimit(){
		return $this->timeLimit;
	}
	public function GetmemoryLimit(){
		return $this->memoryLimit;
	}
	public function Getdiscription(){
		return $this->discription;
	}
	public function GetsampleInput(){
		return $this->sampleInput;
	}
	public function GetsampleOutput(){
		return $this->sampleOutput;
	}
	public function GetSource(){
		return $this->source;
	}
	public function GetinputTips(){
		return $this->inputTips;
	}
	public function GetoutputTips(){
		return $this->outputTips;
	}
	public function GetHint(){
		return $this->hint;
	}
	
	public function PCGetMinProblemID(){
		$connection=mysql_connect("localhost","eojapp","ecust")
		or die("Couldn't connect to server");
		$db=mysql_select_db("eojdb",$connection)
		or die("Couldn't select database");
		$result=mysql_query("select min(up_id) minid from uploadit",$connection)or die("Query Invalid:".mysql_error());
		mysql_close($connection);
		$row=mysql_fetch_array($result);
		$minid=$row['minid'];
		return $minid;
	}
	public function PCGetMaxProblemID(){
		$connection=mysql_connect("localhost","eojapp","ecust")
		or die("Couldn't connect to server");
		$db=mysql_select_db("eojdb",$connection)
		or die("Couldn't select database");
		$result=mysql_query("select max(up_id) maxid from uploadit",$connection)or die("Query Invalid:".mysql_error());
		mysql_close($connection);
		$row=mysql_fetch_array($result);
		$maxid=$row['maxid'];
		return $maxid;
	}
	//
	//for the problem publisher
	public function PPGetProblemList($ProblemPublisherID,$StartID=0,$EndID=9999){
		$connection=mysql_connect("localhost","eojapp","ecust")
		or die("Couldn't connect to server");
		$db=mysql_select_db("eojdb",$connection)
		or die("Couldn't select database");
		
		$result = mysql_query("select up_id,p_title,handout_status
			                   from uploadit 
			                   where  uploadit.uploader_id='$ProblemPublisherID' order by up_id desc", $connection) or die("Query Invalid:".mysql_error());
		while($row=mysql_fetch_array($result)){
			$array[$row['up_id']]=$row;
			switch($array[$row['up_id']][2]){
				case 2:
				    $array[$row['up_id']][2]='待审核';
				    break;
				case 1:
					$array[$row['up_id']][2]="未通过";
					break;
				case 0:
				    $array[$row['up_id']][2]="通过";
				    break;
			}
		}
		if(!isset($array))
			$array=array(array('NULl','NULL','NULL'));
		mysql_close($connection);
		return $array;
	}
	//
	
	//for the problem checker
	public function PCGetProblemList($StartID=0,$EndID=9999){
		$connection=mysql_connect("localhost","eojapp","ecust")
		or die("Couldn't connect to server");
		$db=mysql_select_db("eojdb",$connection)
		or die("Couldn't select database");
		
		$result = mysql_query("select up_id,user_name,p_title,handout_status
			                   from uploadit,uploader
			                   where uploader.uploader_id=uploadit.uploader_id and uploadit.up_id>='$StartID' and uploadit.up_id<='$EndID' order by up_id desc", $connection) or die("Query Invalid:".mysql_error());
		while($row=mysql_fetch_array($result)){
			$array[$row['up_id']]=$row;
			switch($array[$row['up_id']][3]){
				case 2:
				    $array[$row['up_id']][3]='待审核';
				    break;
				case 1:
					$array[$row['up_id']][3]="未通过";
					break;
				case 0:
				    $array[$row['up_id']][3]="通过";
				    break;
			}
		}
		if(!isset($array))
			$array=array(array('NULl','NULL','NULL'));
		mysql_close($connection);
		return $array;
	}
}