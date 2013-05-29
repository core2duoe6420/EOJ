DELIMITER //
CREATE PROCEDURE ADDANS(
IN irun_uid INT UNSIGNED, IN irun_pid INT UNSIGNED)
proc3: BEGIN
  -- this proc help the php add a record to run tbl
  DECLARE orun_id INT UNSIGNED;
  DECLARE oexitcode TINYINT;

  /* start declare Exception Handlers */
  DECLARE EXIT HANDLER FOR SQLEXCEPTION,SQLWARNING,NOT FOUND
  BEGIN
    -- rollback the error transaction
	SET orun_id=0;
	SET oexitcode=1;
	ROLLBACK;
	
	-- generate log
    INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
    VALUES(2,CONCAT('failed add a answer that [run_id]',irun_id, 'When meet a mysql sql error'),1);
	COMMIT;
  END;
  /* end declare Exception Handlers */
  
  /* start execution */
  
  -- insert row to the run tbl
  START TRANSACTION;
  INSERT INTO eojdb.run(run_uid,run_pid)
  VALUES(irun_uid, irun_pid);
  COMMIT;
  
  -- set return data to php user
  SET oexitcode=0;
  SET orun_id=LAST_INSERT_ID();
  
  SELECT orun_id, oexitcode;

  -- generate log
  INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
  VALUES(2,CONCAT('successful add a run record that user[id] ',irun_uid,' answer problem[id] ',irun_pid),0);
  COMMIT;
  /* end execution */
END
//
DELIMITER ;
