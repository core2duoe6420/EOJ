TRUNCATE TABLE eojdb.problem_multilang;
TRUNCATE TABLE eojdb.problem;

INSERT INTO problem(p_tlimt,p_mlimt,p_author)
VALUES(1000,2000,1);

INSERT INTO problem(p_tlimt,p_mlimt,p_author)
VALUES(1000,2000,1);

INSERT INTO problem(p_tlimt,p_mlimt,p_author)
VALUES(1000,2000,1);

INSERT INTO problem(p_tlimt,p_mlimt,p_author)
VALUES(1000,2000,1);

INSERT INTO problem(p_tlimt,p_mlimt,p_author)
VALUES(1000,2000,1);

insert into problem_multilang(p_id,p_lang,p_title,p_desc,p_input_tips,p_output_tips,p_sampleinput,p_sampleoutput)
values(1,1,'A+B问题','计算A+B','两个整数 a,b','两整数之和','1 2','3');

insert into problem_multilang(p_id,p_lang,p_title,p_desc,p_input_tips,p_output_tips,p_sampleinput,p_sampleoutput)
values(2,1,'计算月收入','CoCo的表姐刚从大学毕业，进入到一家小型外贸公司工作。该公司员工月收入的计算方法为：月基本工资加当月提成。从键盘输入CoCo表姐某月的基本工资和该月的提成，计算并输出表姐该月的收入。','输入两个数分别代表月基本工资和月提成。','计算并输出月收入，保留2位小数。','3150.2 1200','4350.20');

insert into problem_multilang(p_id,p_lang,p_title,p_desc,p_input_tips,p_output_tips,p_sampleinput,p_sampleoutput)
values(3,1,'统计行数','编写一个程序，要求统计输入文本的行数。','每行输入任意长度的字符串（每一行的字符串的长度小于等于1000），以输入仅由符号@构成的行作为结束， @所在的行不计入行数。','输出文本的行数。','Hello world!<br />I come from China!<br />I’m a boy!<br />@','3');

insert into problem_multilang(p_id,p_lang,p_title,p_desc,p_input_tips,p_output_tips,p_sampleinput,p_sampleoutput)
values(4,1,'计算n!','从键盘输入n，求n!的值并输出。','输入一个正整数n(n≤12)。','输出n!的值。','5','120');

insert into problem_multilang(p_id,p_lang,p_title,p_desc,p_input_tips,p_output_tips,p_sampleinput,p_sampleoutput)
values(5,1,'九九乘法表 ','输入一个正整数n，打印1~n的乘法表。n小于等于9。','输入正整数n。','输出1~n的乘法表，以4列域宽来输出每一个数字。','5','<samp><pre>1<br />2   4<br />3   6   9<br />4   8   12  16<br />5   10  15  20  25</pre></samp>');

