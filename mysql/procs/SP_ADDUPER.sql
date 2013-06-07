DELIMITER //
CREATE PROCEDURE ADDUPER(
IN iuser_name VARCHAR(255),
IN iuser_passwd CHAR(32),
IN iuser_info VARCHAR(128),
IN iuser_privis TINYINT UNSIGNED)
proc5: BEGIN
  -- this proc help php add a record to uploader tbl
  DECLARE vuser_passwd CHAR(32);
  DECLARE vuser_privis TINYINT UNSIGNED;
  DECLARE ouploader_id INT UNSIGNED;
  DECLARE oexitcode TINYINT;
  
  /* start declare Exception Handlers */
  DECLARE EXIT HANDLER FOR SQLEXCEPTION,SQLWARNING,NOT FOUND
  BEGIN
    -- Mysql SQL Run-time Error
	SET oexitcode=1;
	SET ouploader_id=0;
	ROLLBACK;
	SELECT ouploader_id, oexitcode;
	
	-- generate log
    INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
    VALUES(5,CONCAT('failed add a uploader record ','When meet a mysql sql error'),1);
	COMMIT;
  END;
  /* end declare Exception Handlers */
 
  -- set init value
  SET ouploader_id=0;
 
  -- judge whether the input is null 
  -- and leave this proc when is null
  IF iuser_name IS NULL
  THEN
    SET oexitcode=-1;
	SELECT ouploader_id, oexitcode;
	
	-- generate log
    INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
    VALUES(5,CONCAT('failed because of a null username'),2);
	COMMIT;
	
	LEAVE proc5;
  END IF;
  
  IF iuser_passwd IS NULL
  THEN
	SET vuser_passwd=md5('CS10');
  ELSE
    SET vuser_passwd=iuser_passwd;
  END IF;
  
  IF iuser_privis IS NULL
  THEN
    SET vuser_privis=0;
  ELSE
    SET vuser_privis=iuser_privis;
  END IF;
  
  /* start execution */
  START TRANSACTION;
  INSERT INTO eojdb.uploader(user_name, user_passwd, user_info, user_privis)
  VALUES(iuser_name, vuser_passwd, iuser_info, vuser_privis);
  COMMIT;
  
  -- return uper-id to php
  SET ouploader_id=LAST_INSERT_ID();  
  
  -- set exitcode to php user
  SET oexitcode=0;
  SELECT ouploader_id, oexitcode;
  
  -- generate log
  INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
  VALUES(5,CONCAT('successful add a uploader record that [uper_id] ',ouploader_id),0);
  COMMIT;
  /* end execution */
END
//
DELIMITER ;