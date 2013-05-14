-- test for proc SELPROB
set @exitcode=1; 
set @id=1; 
set @ip_lang=null;
set @otitle=""; 
set @odesc=""; 
set @olang=""; 
set @olangnum=1; 
set @otlimt=0; 
set @omlimt=0;
set @intips=""; 
set @outtips=""; 
set @samplei="";
set @sampleo=""; 
set @hint=""; 
set @specjg=true;
set @ctime=null; 
set @tsubmit=0;
set @acc=0; 
set @author=-1;
call SELPROB(@id, @ip_lang, @otitle, @odesc, @olang, @olangnum,
       @otlimt, @omlimt, @intips, @outtips, @samplei, @sampleo, @hint,
       @specjg, @ctime, @tsubmit, @acc, @author, @exitcode);
select @ctime;


-- test for proc 
set @exitcode=2;
CALL CHPASSWD(1, md5('test'), @exitcode);
select @exitcode;
select * from uploader;


set @exitcode=1;
call updateans(1,1,1,0,0,'Ëæ±ã³¶',1,1,1,@exitcode);
select @exitcode;