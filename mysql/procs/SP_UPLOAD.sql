DELIMITER //
CREATE PROCEDURE UPLOAD(
IN iuploader_id INT UNSIGNED,
IN ip_title VARCHAR(128),
IN ip_desc TEXT,
IN ip_lang VARCHAR(20),
IN ip_tlimt INT UNSIGNED,
IN ip_mlimt INT UNSIGNED,
IN ip_input_tips VARCHAR(1024),
IN ip_output_tips VARCHAR(1024),
IN ip_sampleinput VARCHAR(1024),
IN ip_sampleoutput VARCHAR(1024),
IN ip_hint VARCHAR(1024),
IN ip_specjg BOOLEAN)
proc7: BEGIN
  -- this proc help php add a record to uploadit tbl
  DECLARE vp_title VARCHAR(128);
  DECLARE vp_lang VARCHAR(20);
  DECLARE vp_specjg BOOLEAN;
  DECLARE oup_id INT UNSIGNED;
  DECLARE oexitcode TINYINT;
  DECLARE vrow_c INT;
  
  /* start declare Exception Handlers */
  DECLARE EXIT HANDLER FOR SQLEXCEPTION,SQLWARNING,NOT FOUND
  BEGIN
    -- Mysql SQL Run-time Error
	SET oexitcode=1;
	SET oup_id=0;
	ROLLBACK;
	SELECT oup_id, oexitcode;
	
	-- generate log
    INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
    VALUES(7,CONCAT('failed add a uploadit record ','When meet a mysql sql error'),1);
	COMMIT;
  END;
  /* end declare Exception Handlers */
  
  -- set the init value
  SET oup_id=0;
  
  -- judge whether the input is null 
  -- and leave this proc when is null
  IF iuploader_id IS NULL
  THEN
    SET oexitcode=-1;
	SELECT oup_id, oexitcode;
	
	-- generate log
    INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
    VALUES(7,CONCAT('failed because of a null uploader id'),2);
	COMMIT;
	
	LEAVE proc7;
  END IF;
  
  IF ip_title IS NULL
  THEN
	SET vp_title='无标题';
  ELSE
    SET vp_title=ip_title;
	
	SELECT COUNT(*) 
	INTO vrow_c
	FROM eojdb.uploadit a
	WHERE a.p_title = vp_title;
	
	-- judge whether it's unique title
	IF vrow_c != 0
	THEN
	  SET oexitcode=-1;
	  SELECT oup_id, oexitcode;
	  
	  -- generate log
	  INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
	  VALUES(7, CONCAT('failed because of a same upload [p-title] ', vp_title, ' existed'), 1);
	  
	  COMMIT;
	  LEAVE proc7;
	END IF;
	
	SELECT COUNT(*)
	INTO vrow_c
	FROM eojdb.problem_multilang a
	WHERE a.p_title = vp_title;
	
	-- judge whether it's unique title
	IF vrow_c != 0
	THEN
	  SET oexitcode=-1;
	  SELECT oup_id, oexitcode;
	  
	  -- generate log
	  INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
	  VALUES(7, CONCAT('failed because of a same problem [p-title] ', vp_title, ' existed'), 1);
	  
	  COMMIT;
	  LEAVE proc7;
	END IF;	
  END IF;
  
  IF ip_lang IS NULL
  THEN
    SET vp_lang='简体中文';
  ELSE
    SET vp_lang=ip_lang;
  END IF;
  
  IF ip_specjg IS NULL
  THEN
    SET vp_specjg=false;
  ELSE
    SET vp_specjg=ip_specjg;
  END IF; 
  
  /* start execution */
  START TRANSACTION;
  INSERT INTO eojdb.uploadit(uploader_id, p_title, p_desc, p_lang, 
             p_tlimt, p_mlimt, p_input_tips, p_output_tips, p_sampleinput,
             p_sampleoutput, p_hint, p_specjg)
  VALUES(iuploader_id, vp_title, ip_desc, vp_lang, ip_tlimt, ip_mlimt, 
         ip_input_tips, ip_output_tips, ip_sampleinput, ip_sampleoutput,
		 ip_hint, vp_specjg);
  COMMIT;
  
  -- return up-id to php
  SET oup_id=LAST_INSERT_ID();  
  
  -- set exitcode to php user
  SET oexitcode=0;
  SELECT oup_id, oexitcode;
  
  -- generate log
  INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
  VALUES(7,CONCAT('successful add a uploadit record that [uper_id] ',iuploader_id, ' [up_id] ', oup_id),0);
  COMMIT;
  /* end execution */
END
//
DELIMITER ;