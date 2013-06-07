DELIMITER //
CREATE PROCEDURE ADDPROB(
IN ip_tlimt INT UNSIGNED,
IN ip_mlimt INT UNSIGNED,
IN ip_specjg BOOLEAN,
IN iuploader_id INT UNSIGNED,
IN ip_lang VARCHAR(20),
IN ip_title VARCHAR(128),
IN ip_desc TEXT,
IN ip_input_tips VARCHAR(1024),
IN ip_output_tips VARCHAR(1024),
IN ip_sampleinput VARCHAR(1024),
IN ip_sampleoutput VARCHAR(1024),
IN ip_hint VARCHAR(1024))
proc10: BEGIN
  -- this proc help php add a record to problem\problem_multilang tbl
  DECLARE vp_title VARCHAR(128);
  DECLARE vp_lang VARCHAR(20);
  DECLARE vp_specjg BOOLEAN;
  DECLARE vrow_c INT;
  DECLARE op_id INT UNSIGNED;
  DECLARE oexitcode TINYINT;
  
  /* start declare Exception Handlers */
  DECLARE EXIT HANDLER FOR SQLEXCEPTION,SQLWARNING,NOT FOUND
  BEGIN
    -- Mysql SQL Run-time Error
	SET oexitcode=1;
	ROLLBACK;
	SELECT op_id, oexitcode;
	
	-- generate log
    INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
    VALUES(10,CONCAT('failed add a problem\problem_multilang record ','When meet a mysql sql error'),1);
	COMMIT;
  END;
  /* end declare Exception Handlers */
  
  -- set init value
  SET op_id=0;
  
  -- judge whether the input is null 
  -- and leave this proc when is null
  IF iuploader_id IS NULL
  THEN
    SET oexitcode=-1;
	SELECT op_id, oexitcode;
	
	-- generate log
    INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
    VALUES(10,CONCAT('failed because of a null uploader id'),2);
	COMMIT;
	
	LEAVE proc10;
  END IF;
  
  IF ip_title IS NULL OR ip_title='无标题'
  THEN
	SET vp_title='无标题';
  ELSE
    SET vp_title=ip_title;
	
	SELECT COUNT(*)
	INTO vrow_c
	FROM eojdb.problem_multilang a
	WHERE a.p_title = vp_title;
	
	-- judge whether it's unique title
	IF vrow_c != 0
	THEN
	  SET oexitcode=2;
	  SELECT op_id, oexitcode;
	  
	  -- generate log
	  INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
	  VALUES(10, CONCAT('failed because of a same problem [p-title] ', vp_title, ' existed'), 1);
	  
	  COMMIT;
	  LEAVE proc10;
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
  INSERT INTO eojdb.problem(p_tlimt, p_mlimt, p_specjg, p_author)
  VALUES(ip_tlimt, ip_mlimt, vp_specjg, iuploader_id);
  
  SET op_id=LAST_INSERT_ID();
  
  INSERT INTO eojdb.problem_multilang(p_id, p_lang, p_title, p_desc, 
    p_input_tips, p_output_tips, p_sampleinput, p_sampleoutput, p_hint)
  VALUES(op_id, vp_lang, vp_title, ip_desc, ip_input_tips,
         ip_output_tips, ip_sampleinput, ip_sampleoutput, ip_hint);
  COMMIT;
  
  -- set exitcode to php user
  SET oexitcode=0;
  SELECT op_id, oexitcode;
  
  -- generate log
  INSERT INTO eojdb.EOJLOG(opcode, opmesg, op_tag)
  VALUES(10,CONCAT('successful add a problem and problem-multilang record that [p_id] ',op_id),0);
  COMMIT;
  /* end execution */
END
//
DELIMITER ;