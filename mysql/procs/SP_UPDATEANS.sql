DELIMITER //
CREATE PROCEDURE UPDATEANS(IN irun_id INT UNSIGNED,
IN irun_pid INT UNSIGNED, IN irun_uid INT UNSIGNED,
IN irun_mcost SMALLINT UNSIGNED, IN irun_tcost SMALLINT UNSIGNED, 
IN irun_codeloc VARCHAR(256), IN irun_codetype TINYINT UNSIGNED,
IN irun_codel SMALLINT UNSIGNED, IN irun_result SMALLINT)
proc4: BEGIN
  -- this proc help the gcc daemon update the answer data to mysql eojdb
  DECLARE vrow_c INT;
  DECLARE oexitcode TINYINT;
  
  /* start declare Exception Handlers */
  DECLARE EXIT HANDLER FOR SQLEXCEPTION,SQLWARNING,NOT FOUND
  BEGIN
    -- rollback the error transaction
	SET oexitcode=1;
	ROLLBACK;
	SELECT oexitcode;
	
	-- generate log
    INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
    VALUES(4,CONCAT('failed to update a answer that [run_id] ',irun_id, ' When meet a mysql sql error, exitcode 1'),1);
	COMMIT;
  END;
  /* end declare Exception Handlers */
  
  -- judge whether the value is NULL
  IF irun_id IS NULL or irun_pid IS NULL or irun_uid IS NULL
  THEN
    ROLLBACK;
    SET oexitcode=-1;
	SELECT oexitcode;
	
	-- generate log
    INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
    VALUES(4,CONCAT('failed because of a null value input, exitcode -1'),2);
	COMMIT;
	
	LEAVE proc4;
  END IF;
  
  /* start execution */
  -- update the run tbl
  SET vrow_c=-1;
  START TRANSACTION;
  
  UPDATE eojdb.run
  SET run_mcost=irun_mcost, run_tcost=irun_tcost,
      run_codeloc=irun_codeloc,run_codetype=irun_codetype,
	  run_codel=irun_codel,run_result=irun_result
  WHERE run_id=irun_id;
  
  -- judge whether the data is ok
  SELECT ROW_COUNT() INTO vrow_c;
  IF vrow_c != 1
  THEN
    SET oexitcode=2;
	ROLLBACK;
	SELECT oexitcode;
	
 	-- generate log
    INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
    VALUES(4,CONCAT('failed to update a run record because of data not found, check whether the [runid] ', irun_id ,' is correct and existed, exitcode 2'),2);
	COMMIT;
	
	LEAVE proc4;
  END IF;
  COMMIT;
  
  -- update the eojuser record
  SET vrow_c=-1;
  START TRANSACTION;
  UPDATE eojdb.eojuser
  SET user_tsubmit = user_tsubmit+1
  WHERE user_id = irun_uid;
  
  SELECT ROW_COUNT() INTO vrow_c;
  IF vrow_c != 1
  THEN
    SET oexitcode=3;
	ROLLBACK;
	SELECT oexitcode;
	
 	-- generate log
    INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
    VALUES(4,CONCAT('failed to complete update answer while update a eojuser record error because of data not found, check whether the [eojuser id] ', irun_uid ,' is correct and existed'),2);
	COMMIT;
	
	LEAVE proc4;
  END IF;
  
  IF irun_result=1 THEN
    UPDATE eojdb.eojuser
	SET user_acc = user_acc+1
	WHERE user_id = irun_uid;
  END IF;
  COMMIT;
  
  -- update the problem record
  START TRANSACTION;
  SET vrow_c=-1;
  UPDATE eojdb.problem
  SET p_tsubmit = p_tsubmit+1
  WHERE p_id = irun_pid;
  
  SELECT ROW_COUNT() INTO vrow_c;
  IF vrow_c != 1
  THEN
    SET oexitcode=4;
	ROLLBACK;
	SELECT oexitcode;
	
 	-- generate log
    INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
    VALUES(4,CONCAT('failed to complete a answer record while update problem submitnums error  because of data not found, check whether the [problem id] ', irun_pid ,' is correct and existed'),2);
	COMMIT;
	
	LEAVE proc4;
  END IF;
  
  IF irun_result=1 THEN
    UPDATE eojdb.problem
	SET p_acc = p_acc+1
	WHERE p_id = irun_pid;
  END IF;
  COMMIT;
  
  -- set exitcode to php user
  SET oexitcode=0;
  SELECT oexitcode;
  
  -- generate log
  INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
  VALUES(1,CONCAT('successful update a run record that user[id] ',irun_uid,' answer problem[id] ',irun_pid, ', exitcode 0'),0);
  COMMIT;
  /* end execution */
END
//
DELIMITER ;