<?php

class EOJ_Model_ProblemChecker extends EOJ_Model_Adminstrator
{
	public function CheckProblem($upID,$ProblemJudgeResult){//2 上传后待审核，1则为未通过，0为通过审核
		//
		$connection=mysql_connect("localhost","eojapp","ecust")
		or die("Couldn't connect to server");
		$db=mysql_select_db("eojdb",$connection)
		or die("Couldn't select database");
		//
		$callsql="call AUDITPROB('$upID','$ProblemJudgeResult')";
		$result = mysql_query($callsql, $connection) or die("daodiyoumeiyouyong:".mysql_error());
		$row=mysql_fetch_array($result);
		$oec=$row['oexitcode'];
		//
		mysql_close($connection) or die("daodiyoumeiyouyong:".mysql_error());
		//
		return $oec;//对应 0 即成功完成  对应 1 即mysql error  对应 -1 即null value  对应 2 即handout_status 已经为0  对应 3 即handout_status unknown  对应 4 即更新或删除数据失败，可能是未找到数据
	}

}

