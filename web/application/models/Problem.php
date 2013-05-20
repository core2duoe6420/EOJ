<?php

class Application_Model_Problem
{
	protected $problemName;
	protected $timeLimit;
	protected $memoryLimit;
	protected $discription;
	protected $sampleInput;
	protected $sampleOutput;
	protected $source;
	protected $ProblemID;
	protected $inputTips;
	protected $outputTips;
	protected $hint;
	protected $connection;
	public function GetProblemList(){
	}
}
class UncheckedProblem extends Application_Model_Problem{
	public function GetProblemList(){
	}
}