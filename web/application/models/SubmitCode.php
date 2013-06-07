<?php

class EOJ_Model_SubmitCode
{
	private $WorkDirectionary;
	private $User;
	private $connect;
	private $dom;
	private function GetPara(){
		$this->dom=new DOMDocument;
		$this->dom->load("/eoj_files/eoj.xml");
		//$this->xeoj=$dom->getElementsByTagName("eoj");
		$this->WorkDirectionary=$this->dom->getElementsByTagName("work_dir")->item(0)->nodeValue;
	}
	private function LinkDataBase(){
		$this->connect=mysql_connect("localhost","eojapp","ecust")
		or die("Couldn't connect to server");
		$db=mysql_select_db("eojdb",$this->connect)
		or die("Couldn't select database");
	}
	public function __construct(){
		$this->LinkDataBase();
		$this->GetPara();
	}
	public function SubmitCode($code,$UserID,$ProblemID,$CodeType){
		$RunID=0;
		$QueryResult=0;
		//mysql_query("SET @a=".$UserID,$this->connect);
		//mysql_query("SET @b=".$ProblemID,$this->connect);
		//mysql_query("SET @c=".$RunID,$this->connect);
		//mysql_query("SET @d=".$QueryResult,$this->connect);
		$run_pro_sql= "select count(*) from problem where p_id='$ProblemID'";
		$result=mysql_query($run_pro_sql, $this->connect) or die("Query Invalid:".mysql_error());
		$row=mysql_fetch_array($result);
		if($row[0]==0)
			return "No such problem";
		
		$run_pro_sql = "CALL ADDANS('$UserID','$ProblemID');";//have error
		//$result = 
		$result= mysql_query($run_pro_sql, $this->connect) or die("Query Invalid:".mysql_error());
		$row=mysql_fetch_array($result);
		$RunID=$row['orun_id'];
		$oexitCode=$row['oexitcode'];
		if($oexitCode!=0){
			return "database error";
		}
		//
		$suffix=".c";
		$compilers=$this->dom->getElementsByTagName("compiler");
		foreach ( $compilers as $compiler) {
			$cid=$compiler->getElementsByTagName("id")->item(0)->nodeValue;
			if($cid==$CodeType){
				$suffix=$compiler->getElementsByTagName("suffix")->item(0)->nodeValue;
				break;
			}
		}
		$FileName=$RunID."-".$ProblemID."-".$UserID.$suffix;
		$File=$this->WorkDirectionary."/".$FileName;
		$handle=fopen($File,"w");
		if ($handle==false) {
			return "open file error";
		}
		if(flock($handle,LOCK_EX)==false){
			fclose($handle);
			return "lock error";
		}
		if(fwrite($handle, $code)==false){
			flock($handle, LOCK_UN);
			fclose($handle);
			return "Can not write file";
		}
		flush($handle);
		flock($handle, LOCK_UN);
		fclose($handle);
		return "Succeed";
	}
	
	
	public function GetResultNoPara(){
		$result=mysql_query("select max(run_id) maxid from run",$this->connect) or  die("Query Invalid:".mysql_error());
		$row=mysql_fetch_array($result);
		$maxid=$row['maxid'];
		$run_pro_sql = "select run_id,run_submitt,run_result,run_pid,run_codetype,run_tcost,run_mcost,user_name from run,eojuser where eojuser.user_id=run.run_uid and run_id<='$maxid' and run_id>=('$maxid'-100)";//
		$result = mysql_query($run_pro_sql, $this->connect) or die("Query Invalid:".mysql_error());
		while($row=mysql_fetch_array($result)){
			$array[$row['run_id']]=$row;
			switch($array[$row['run_id']]['run_result']){
				case 1://accept 1 systemerror -1 compile err2 runtime error 3 timeTilexe 4 memoryacc 5 output limite 6 wrong answer 7 codelength 8
				    $array[$row['run_id']]['run_result']="Accept";
				    break;
				case 2:
					$array[$row['run_id']]['run_result']="Compile Error";
					break;
				case 3:
				    $array[$row['run_id']]['run_result']="Runtime Error";
				    break;
				case 4:
				    $array[$row['run_id']]['run_result']="Time Limit Exceeded";
				    break;
				case 5:
				    $array[$row['run_id']]['run_result']="Memory Limit Exceeded";
				    break;
				case 6:
				    $array[$row['run_id']]['run_result']="Output Limit Exceeded";
				    break;
				case 7:
				    $array[$row['run_id']]['run_result']="Wrong Answer";
				    break;
				case 8:
				    $array[$row['run_id']]['run_result']="CodeLength Limit Exceeded";
				    break;
				default:
				    $array[$row['run_id']]['run_result']="Compiling";
			}
		}
		return $array;
	}
	public function __destruct(){
		@mysql_close($this->connect);
	}
	public function GetResult($ProblemID,$UserName,$Result,$language,$upbound=-1,$lowbound=-1){
		//
		$run_pro_sql = "select run_id,user_name,run_pid,run_result,run_mcost,run_tcost,run_codetype,run_codel,run_submitt from run,eojuser where eojuser.user_id=run.run_uid ";//
		//
		if($ProblemID!=0){
			$run_pro_sql=$run_pro_sql." and run_pid='$ProblemID'";
		}
		if (!is_int($UserName)) {
			$run_pro_sql=$run_pro_sql." and user_name='$UserName'";
		}
		if ($Result!=0) {
			$run_pro_sql=$run_pro_sql." and run_result='$Result'";
		}
		if ($language!=0) {
			$run_pro_sql=$run_pro_sql." and run_codetype='$language'";
		}
		if($upbound!=-1 && $lowbound!=-1){
			$run_pro_sql=$run_pro_sql." and run_id>='$lowbound' and run_id<='$upbound '";
		}
		$run_pro_sql=$run_pro_sql." order by run_id desc";
		$result = mysql_query($run_pro_sql, $this->connect) or die("Query Invalid:".mysql_error());
		
		while($row=mysql_fetch_array($result)){
			$array[$row['run_id']]=$row;
			switch($array[$row['run_id']]['run_result']){
				case 1://accept 1 systemerror -1 compile err2 runtime error 3 timeTilexe 4 memoryacc 5 output limite 6 wrong answer 7 codelength 8
				    $array[$row['run_id']]['run_result']="Accept";
				    break;
				case 2:
					$array[$row['run_id']]['run_result']="Compile Error";
					break;
				case 3:
				    $array[$row['run_id']]['run_result']="Runtime Error";
				    break;
				case 4:
				    $array[$row['run_id']]['run_result']="Time Limit Exceeded";
				    break;
				case 5:
				    $array[$row['run_id']]['run_result']="Memory Limit Exceeded";
				    break;
				case 6:
				    $array[$row['run_id']]['run_result']="Output Limit Exceeded";
				    break;
				case 7:
				    $array[$row['run_id']]['run_result']="Wrong Answer";
				    break;
				case 8:
				    $array[$row['run_id']]['run_result']="CodeLength Limit Exceeded";
				    break;
				case -1:
					$array[$row['run_id']]['run_result']="System Error";
					break;
				default:
				    $array[$row['run_id']]['run_result']="Compiling";
			}
			switch($array[$row['run_id']]['run_codetype']){
			    case 1:
				    $array[$row['run_id']]['run_codetype']="GCC";
					break;
				case 2:
				    $array[$row['run_id']]['run_codetype']="G++";
					break;
			}
		}
		if(!isset($array))
		//select run_id,user_name,run_pid,run_result,run_mcost,run_tcost,run_codetype,run_codel,run_submitt from run,eojuser where eojuser.user_id=run.run_uid
			$array=array('NULL'=>array('run_id'=>'NULL','user_name'=>'NULL','run_pid'=>'NULL','run_result'=>'NULL','run_mcost'=>'NULL','run_tcost'=>'NULL','run_codetype'=>'NULL','run_codel'=>'NULL','run_submitt'=>'NULL'));
		return $array;
	}
	
	public function GetMaxRunID(){
		$connection=mysql_connect("localhost","eojapp","ecust")
		or die("Couldn't connect to server");
		$db=mysql_select_db("eojdb",$connection)
		or die("Couldn't select database");
		$result=mysql_query("select max(run_id) maxid from run",$connection)or die("Query Invalid:".mysql_error());
		mysql_close($connection);
		$row=mysql_fetch_array($result);
		$maxid=$row['maxid'];
		return $maxid;
	}
	public function GetMinRunID(){
		$connection=mysql_connect("localhost","eojapp","ecust")
		or die("Couldn't connect to server");
		$db=mysql_select_db("eojdb",$connection)
		or die("Couldn't select database");
		$result=mysql_query("select min(run_id) minid from run",$connection)or die("Query Invalid:".mysql_error());
		mysql_close($connection);
		$row=mysql_fetch_array($result);
		$minid=$row['minid'];
		return $minid;
	}
}