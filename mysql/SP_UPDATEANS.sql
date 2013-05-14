DELIMITER //
CREATE PROCEDURE UPDATEANS(IN irun_id INT UNSIGNED,
IN irun_pid INT UNSIGNED, IN irun_uid INT UNSIGNED,
IN irun_mcost SMALLINT UNSIGNED, IN irun_tcost SMALLINT UNSIGNED, 
IN irun_codeloc VARCHAR(256), IN irun_codetype TINYINT UNSIGNED,
IN irun_codel SMALLINT UNSIGNED, IN irun_result SMALLINT,
OUT oexitcode TINYINT)
BEGIN
  -- this proc help the gcc daemon update the answer data to mysql eojdb
  
  /* start declare Exception Handlers */
  DECLARE EXIT HANDLER FOR SQLEXCEPTION,SQLWARNING,NOT FOUND
  BEGIN
    -- rollback the error transaction
	SET oexitcode=1;
	ROLLBACK;
	
	-- generate log
    INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
    VALUES(2,CONCAT('failed to update a answer that [run_id]',irun_id, 'When meet a mysql sql error'),1);
	COMMIT;
  END;
  /* end declare Exception Handlers */
  
  /* start execution */
  -- update the run tbl
  START TRANSACTION;
  UPDATE eojdb.run
  SET run_mcost=irun_mcost, run_tcost=irun_tcost,
      run_codeloc=irun_codeloc,run_codetype=irun_codetype,
	  run_codel=irun_codel,run_result=irun_result
  WHERE run_id=irun_id;
  COMMIT;
  
  -- update the eojuser record
  START TRANSACTION;
  UPDATE eojdb.eojuser
  SET user_tsubmit = user_tsubmit+1
  WHERE user_id = irun_uid;
  
  IF irun_result=1 THEN
    UPDATE eojdb.eojuser
	SET user_acc = user_acc+1
	WHERE user_id = irun_uid;
  END IF;
  COMMIT;
  
  -- update the problem record
  START TRANSACTION;
  UPDATE eojdb.problem
  SET p_tsubmit = p_tsubmit+1
  WHERE p_id = irun_pid;
  
  IF irun_result=1 THEN
    UPDATE eojdb.problem
	SET p_acc = p_acc+1
	WHERE p_id = irun_pid;
  END IF;
  COMMIT;
  
  -- set exitcode to php user
  SET oexitcode=0;
  
  -- generate log
  INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
  VALUES(1,CONCAT('successful update a run record that user[id] ',irun_uid,' answer problem[id] ',irun_pid),0);
  COMMIT;
  /* end execution */
END
//
DELIMITER ;
