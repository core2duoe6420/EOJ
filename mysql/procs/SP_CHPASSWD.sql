DELIMITER //
CREATE PROCEDURE CHPASSWD(
IN iuser_id INT UNSIGNED, 
IN iselect TINYINT UNSIGNED,
IN ipasswd CHAR(32))
proc2: BEGIN
  DECLARE vrow_c INT;
  DECLARE oexitcode TINYINT;
  DECLARE vuser VARCHAR(10);
  
  /* start declare Exception Handlers */
  DECLARE EXIT HANDLER FOR SQLEXCEPTION,SQLWARNING,NOT FOUND
  BEGIN
    -- Mysql SQL Run-time Error
	SET oexitcode=1;
	ROLLBACK;
	SELECT oexitcode;
	
	-- generate log
    INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
    VALUES(2,CONCAT('failed modify the passwd that [', vuser ,'] ',iuser_id, 'When meet a mysql sql error, exitcode 1'),1);
	COMMIT;
  END;
  /* end declare Exception Handlers */
  
  IF iuser_id IS NULL or ipasswd IS NULL
  THEN
    ROLLBACK;
    SET oexitcode=-1;
	SELECT oexitcode;
	
	-- generate log
    INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
    VALUES(2,CONCAT('failed because of a null value input, exitcode -1'),2);
	COMMIT;
	
	LEAVE proc2;
  END IF;
  
  IF iselect = 1 
  THEN
	SET vuser='UPLOADER';
  ELSEIF iselect = 2
  THEN
	SET vuser='EOJUSER';
  ELSE
    SET oexitcode=2;
	ROLLBACK;
	SELECT oexitcode;
	
	-- generate log
	INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
    VALUES(2,CONCAT('failed because of a illegal input value, exitcode 2'),2);
	COMMIT;
	
	LEAVE proc2;
  END IF;
  
  /* start execution */
  SET vrow_c=-1;
  
  IF iselect = 1
  THEN
	UPDATE eojdb.UPLOADER
	SET user_passwd=ipasswd
	WHERE uploader_id=iuser_id;
  ELSEIF iselect = 2
  THEN
	UPDATE eojdb.EOJUSER
	SET user_passwd=ipasswd
	WHERE user_id=iuser_id;
  END IF;
  /* end execution */
  
  -- judge whether the data is ok
  SELECT ROW_COUNT() INTO vrow_c;
  IF vrow_c <> 1
  THEN
    ROLLBACK;
	SELECT oexitcode;
    SET oexitcode=3;
	
 	-- generate log
    INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
    VALUES(2,CONCAT('failed because of data not found, check whether the [', vuser , '] ', iuser_id, ' is correct and existed, exitcode 3'),2);
	COMMIT;
	
	LEAVE proc2;
  END IF;
  COMMIT;

  -- set exitcode to php user
  SET oexitcode=0;
  SELECT oexitcode;
  
  -- generate log
  INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
  VALUES(2,CONCAT('successful update the [', vuser, '] ' ,iuser_id, ' passwd, exitcode 0'),0);
  COMMIT;
  /* end execution */
END
//
DELIMITER ;
