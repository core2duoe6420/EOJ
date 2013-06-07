<?php

class EOJ_Model_ProblemPublisher extends EOJ_Model_Adminstrator
{
	public function uploadproblem($iuploader_id,$ip_title,$ip_desc,$ip_lang,$ip_tlimt,$ip_mlimt,$ip_input_tips,$ip_output_tips,$ip_sampleinput,$ip_sampleoutput,$ip_hint,$ip_specjg){
		//
		$connection=mysql_connect("localhost","eojapp","ecust")
		or die("Couldn't connect to server");
		$db=mysql_select_db("eojdb",$connection)
		or die("Couldn't select database");
		//
		if($ip_tlimt==null){
			$ip_tlimt=2000;
		}
		if($ip_mlimt==null){
			$ip_mlimt=65536;
		}
		$callsql="call UPLOAD('$iuploader_id','$ip_title','$ip_desc','$ip_lang','$ip_tlimt','$ip_mlimt','$ip_input_tips','$ip_output_tips','$ip_sampleinput','$ip_sampleoutput','$ip_hint','$ip_specjg')";
		$result = mysql_query($callsql, $connection) or die("daodiyoumeiyouyong:".mysql_error());
		$row=mysql_fetch_array($result);
		$opid=$row['oup_id'];
		$oec=$row['oexitcode'];
		//
		mysql_close($connection) or die("daodiyoumeiyouyong:".mysql_error());
		//
		if ($oec==0) {
			return $opid;
		}else{
			return $oec-1;//对应 1 即mysql error  对应 -1 即p_title已经在uploadit,problem_multilang出现
		}
	}
	public function UpdateUploadedProblem($up_id,$ip_title,$ip_desc,$ip_lang,$ip_tlimt,$ip_mlimt,$ip_input_tips,$ip_output_tips,$ip_sampleinput,$ip_sampleoutput,$ip_hint,$ip_specjg){
		//
		$connection=mysql_connect("localhost","eojapp","ecust")
		or die("Couldn't connect to server");
		$db=mysql_select_db("eojdb",$connection)
		or die("Couldn't select database");
		//
		if($ip_tlimt==null){
			$ip_tlimt=2000;
		}
		if($ip_mlimt==null){
			$ip_mlimt=65536;
		}
		$callsql="call UPDATEUP('$up_id','$ip_title','$ip_desc','$ip_lang','$ip_tlimt','$ip_mlimt','$ip_input_tips','$ip_output_tips','$ip_sampleinput','$ip_sampleoutput','$ip_hint','$ip_specjg')";
		$result = mysql_query($callsql, $connection) or die("daodiyoumeiyouyong:".mysql_error());
		$row=mysql_fetch_array($result);
		$oec=$row['oexitcode'];
		//
		mysql_close($connection) or die("daodiyoumeiyouyong:".mysql_error());
		//
		return $oec;//对应 0 即成功完成 对应 1 即mysql error	对应 -1 即null value	对应 2 即p_title already existed	对应 3 即update时error，可能是未找到数据
	}

}

