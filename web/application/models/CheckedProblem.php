<?php

class Application_Model_CheckedProblem extends Application_Model_Problem
{
	private $AnswerCount;
	private $AcceptCount;
	public function SetProblemID($ID){
		$ProblemID=$ID;
		$connection=mysql_connect("localhost","root","1234")
		or die("Couldn't connect to server");
		$db=mysql_select_db("eojdb",$connection)
		or die("Couldn't select database");
		//get problem all information
		mysql_query("SET @a = $ProblemID", $connection);
		$run_pro_sql = "CALL selprob(@a);";
		$result = mysql_query($run_pro_sql, $connection) or die("Query Invalid:".mysql_error());
		$row=mysql_fetch_array($result);
		//
		$problemName=$row['p_title'];
		$timeLimit=$row['p_tlimt'];
		$memoryLimit=$row['p_mlimt'];
		$discription=$row['p_desc'];
		$sampleInput=$row['p_sampleinput'];
		$sampleOutput=$row['p_sampleoutput'];
		$source=$row['p_author'];
		$inputTips=$row['p_input_tips'];
		$output=$row['p_output_tips'];
		$hint=$row['p_hint'];
		mysql_close($connection);
	}
	public function GetproblemName(){
		return $problemName;
	}
	public function GettimeLimit(){
		return $timeLimit;
	}
	public function GetmemoryLimit(){
		return $memoryLimit;
	}
	public function Getdiscription(){
		return $discription;
	}
	public function GetsampleInput(){
		return $sampleInput;
	}
	public function GetsampleOutput(){
		return $sampleOutput;
	}
	public function GetSource(){
		return $source;
	}
	public function GetinputTips(){
		return $inputTips;
	}
	public function GetoutputTips(){
		return $outputTips;
	}
	public function GetHint(){
		return $hint;
	}
	public function GetProblemList(){
		$connection=mysql_connect("","eojapp","ecust")
		or die("Couldn't connect to server");
		$db=mysql_select_db("eojdb",$connection)
		or die("Couldn't select database");
		$result = mysql_query("select problem_multilang.p_id,p_title,p_acc,p_tsubmit from problem_multilang,problem where problem_multilang.p_id=problem.p_id", $connection) or die("Query Invalid:".mysql_error());
		//$i=0;
		while($row=mysql_fetch_array($result)){
			$array[$row['p_id']]=$row;//ldonknow
			//$i=$i+1;
		}
		mysql_close($connection);
		return $array;
	}
}
