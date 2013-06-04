DELIMITER //
CREATE PROCEDURE AUDITPROB(
IN iup_id INT UNSIGNED,
IN ijudge BOOLEAN)
proc8: BEGIN
  -- this proc help php update the upload problem status
  DECLARE vrow_c INT;
  DECLARE vhandout_status TINYINT UNSIGNED;
  DECLARE oexitcode TINYINT;
  
  /* start declare Exception Handlers */
  DECLARE EXIT HANDLER FOR SQLEXCEPTION,SQLWARNING,NOT FOUND
  BEGIN
    -- Mysql SQL Run-time Error
	SET oexitcode=1;
	ROLLBACK;
	SELECT oexitcode;
	
	-- generate log
    INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
    VALUES(8,CONCAT('failed update a uploadit record status ','When meet a mysql sql error'),1);
	COMMIT;
  END;
  /* end declare Exception Handlers */
  
  -- judge whether exist null value
  IF iup_id IS NULL or ijudge IS NULL
  THEN
    SET oexitcode=-1;
	SELECT oexitcode;
	
    -- generate log
	INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
	VALUES(8, CONCAT('failed because of a null value'), 1);
	COMMIT;
	
	LEAVE proc8;
  END IF;
  
  -- get handout_status value
  SELECT handout_status INTO vhandout_status
  FROM eojdb.uploadit
  WHERE up_id=iup_id;
  
  /* start execution */
  SET vrow_c=-1;
  START TRANSACTION;
  IF ijudge = true
  THEN
    -- it will get a trigger to insert the problem
	IF vhandout_status != 0
	THEN
		UPDATE eojdb.uploadit
		SET handout_status=0
		WHERE up_id=iup_id;
	ELSE
		SET oexitcode=2;
		SELECT oexitcode;
		
		-- generate log
		INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
		VALUES(8, CONCAT('failed because of handout_status is already 0.'), 1);
		COMMIT;
	
		LEAVE proc8;
	END IF;
  ELSE
	IF vhandout_status = 2
	THEN
		UPDATE uploadit
		SET handout_status=1
		WHERE up_id=iup_id;
	ELSEIF vhandout_status = 1
	THEN
		UPDATE uploadit
		SET handout_status=3
		WHERE up_id=iup_id;
	ELSEIF vhandout_status = 3
	THEN
	    UPDATE uploadit
		SET handout_status=4
		WHERE up_id=iup_id;
	ELSEIF vhandout_status = 4
	THEN
		DELETE FROM uploadit
		WHERE up_id=iup_id
		AND handout_status<>0;
	ELSE 
		SET oexitcode=3;
		SELECT oexitcode;
		
		-- generate log
		INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
		VALUES(8, CONCAT('failed because of unknown [handout_status] ', vhandout_status ,' value'), 1);
		COMMIT;
	
		LEAVE proc8;
	END IF;
  END IF;
  
  -- judge whether it is work ok
  SELECT ROW_COUNT() INTO vrow_c;
  IF vrow_c != 1
  THEN
    SET oexitcode=4;
	ROLLBACK;
	SELECT oexitcode;
	
 	-- generate log
    INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
    VALUES(8,CONCAT('failed to update a uploadit record because of update or delete error, check whether the input judge and id is correct and existed'),2);
	COMMIT;
	
	LEAVE proc8;
  END IF;
  
  COMMIT;
  
  -- set exitcode to php user
  SET oexitcode=0;
  SELECT oexitcode;
  
  -- generate log
  INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
  VALUES(8,CONCAT('successful update a uploadit record that [up_id] ', iup_id, ',status changed to ', vhandout_status),0);
  COMMIT;
  /* end execution */
END
//
DELIMITER ;
