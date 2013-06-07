<?php

class EOJ_Model_CheckedProblem extends EOJ_Model_Problem
{
	private $AnswerCount;
	private $AcceptCount;
	public function SetProblemID($ID){
		$this->ProblemID=$ID;
		$this->connection=mysql_connect("localhost","eojapp","ecust")
		or die("Couldn't connect to server");
		$db=mysql_select_db("eojdb",$this->connection)
		or die("Couldn't select database");
		//get problem all information
//		mysqli_query("SET @a = $ProblemID", $this->connection);
		$run_pro_sql = "select p_title,p_tlimt,p_mlimt,p_desc,p_sampleinput,p_sampleoutput,p_author,p_input_tips,p_output_tips,p_hint from problem,problem_multilang where problem_multilang.p_id=problem.p_id and problem.p_id='$ID'";
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
			$this->source=$row['p_author'];
			$this->inputTips=$row['p_input_tips'];
			$this->output=$row['p_output_tips'];
			$this->hint=$row['p_hint'];
			mysql_close($this->connection);
			return true;
		}
		else
			return false;
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
	
	public function GetProblemList($StartID,$EndID){
		$connection=mysql_connect("localhost","eojapp","ecust")
		or die("Couldn't connect to server");
		$db=mysql_select_db("eojdb",$connection)
		or die("Couldn't select database");
		$result = mysql_query("select problem_multilang.p_id,p_title,p_acc,p_tsubmit from problem_multilang,problem where problem_multilang.p_id=problem.p_id and problem.p_id>='$StartID' and problem.p_id<='$EndID'", $connection) or die("Query Invalid:".mysql_error());
		//$i=0;
		while($row=mysql_fetch_array($result)){
			$array[$row['p_id']]=$row;//ldonknow
			//$i=$i+1;
		}
		mysql_close($connection);
		if(!isset($array))
			$array=array('NULL'=>array('NULL','NULL','NULL','NULL'));
		return $array;
	}
	public function GetMinProblemID(){
		$connection=mysql_connect("localhost","eojapp","ecust")
		or die("Couldn't connect to server");
		$db=mysql_select_db("eojdb",$connection)
		or die("Couldn't select database");
		$result=mysql_query("select min(p_id) minid from eojdb.problem",$connection)or die("Query Invalid:".mysql_error());
		mysql_close($connection);
		$row=mysql_fetch_array($result);
		$minid=$row['minid'];
		return $minid;
	}
	public function GetMaxProblemID(){
		$connection=mysql_connect("localhost","eojapp","ecust")
		or die("Couldn't connect to server");
		$db=mysql_select_db("eojdb",$connection)
		or die("Couldn't select database");
		$result=mysql_query("select max(p_id) maxid from problem",$connection)or die("Query Invalid:".mysql_error());
		mysql_close($connection);
		$row=mysql_fetch_array($result);
		$maxid=$row['maxid'];
		return $maxid;
	}
}
