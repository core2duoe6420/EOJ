DELIMITER //
CREATE PROCEDURE ADDEOJUSER(
IN iuser_name VARCHAR(255),
IN iuser_passwd CHAR(32))
proc6: BEGIN
  -- this proc help php add a record to eojuser tbl
  DECLARE vuser_passwd CHAR(32);
  DECLARE ouser_id INT UNSIGNED;
  DECLARE oexitcode TINYINT;
  
  /* start declare Exception Handlers */
  DECLARE EXIT HANDLER FOR SQLEXCEPTION,SQLWARNING,NOT FOUND
  BEGIN
    -- Mysql SQL Run-time Error
	SET oexitcode=1;
	SET ouser_id=0;
	ROLLBACK;
	SELECT ouser_id, oexitcode;
	
	-- generate log
    INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
    VALUES(6,CONCAT('failed add a eojuser record ','When meet a mysql sql error'),1);
	COMMIT;
  END;
  /* end declare Exception Handlers */
  
  -- set the init value
  SET ouser_id=0;
  
  -- judge whether the input is null 
  -- and leave this proc when is null
  IF iuser_name IS NULL
  THEN
    SET oexitcode=-1;
	SELECT ouser_id, oexitcode;
	
	-- generate log
    INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
    VALUES(6,CONCAT('failed because of a null username'),2);
	COMMIT;
	
	LEAVE proc6;
  END IF;
  
  IF iuser_passwd IS NULL
  THEN
	SET vuser_passwd=md5('CS10');
  ELSE
    SET vuser_passwd=iuser_passwd;
  END IF;
  
  /* start execution */
  START TRANSACTION;
  INSERT INTO eojdb.eojuser(user_name, user_passwd)
  VALUES(iuser_name, vuser_passwd);
  COMMIT;
  
  -- return eojuser-id to php
  SET ouser_id=LAST_INSERT_ID();  
  
  -- set exitcode to php user
  SET oexitcode=0;
  SELECT ouser_id, oexitcode;
  
  -- generate log
  INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
  VALUES(6,CONCAT('successful add a eojuser record that [user_id] ',ouser_id),0);
  COMMIT;
  /* end execution */
END
//
DELIMITER ;
