
<?php

define('HOST_MSSQL','WIN-CLJ1B0GQ6JP');#хост 
define('LOGIN_MSSQL','sa');#логин базы
define('PASSWORD_MSSQL','RFonline123');#пароль базы
define('WORLD','RF_WORLD');#база мира
if(@mssql_connect(HOST_MSSQL,LOGIN_MSSQL,PASSWORD_MSSQL))
{}
else
{echo"<font color='white'>Fazer com o Servidor Offline";}
mssql_select_db(WORLD);

function sel_eserial()
{
$seleserial = mssql_query("SELECT eSerial FROM tbl_patriarch_candidate ORDER BY eSerial DESC");
while($eserial1=mssql_fetch_array($seleserial))
{
$eser[]=$eserial1['eSerial'];
}
$eserial=$eser{0};
list($proveserial)=mssql_fetch_array(mssql_query("SELECT eSerial FROM tbl_patriarch_candidate WHERE eSerial='$eserial' AND ClassType='0'"));
if($proveserial =='')
{$serial=$eserial-1;}
else{$serial=$eserial;}
return $serial;
}
patr_form();
function patr_form()
{
$n=0;
while($n<9)
{
$serial=sel_eserial();
$select1= mssql_fetch_array(mssql_query("SELECT Serial,AName FROM tbl_patriarch_candidate WHERE eSerial='$serial' AND ClassType='$n' AND Race='0'"));
$aserial["0"]["$n"] = $select1['Serial']; 
$aname["0"]["$n"] = $select1['AName'];
$select2= mssql_fetch_array(mssql_query("SELECT Serial,AName FROM tbl_patriarch_candidate WHERE eSerial='$serial' AND ClassType='$n' AND Race='1'"));
$aserial["1"]["$n"] = $select2['Serial']; 
$aname["1"]["$n"] = $select2['AName'];
$select3= mssql_fetch_array(mssql_query("SELECT Serial,AName FROM tbl_patriarch_candidate WHERE eSerial='$serial' AND ClassType='$n' AND Race='2'"));
$aserial["2"]["$n"] = $select3['Serial']; 
$aname["2"]["$n"] = $select3['AName'];
$n++;
}
echo"
<form method='post'><table  style=' width:100%;border:0px'>
   <tr >
<td width='40%'>
<table width='100%'>";
echo"<b><font color='white' size='5'>Conselho Atual<br>Fazer com o Servidor Offline</b><br><br>";

$m=0;
while($m<3)
{
switch($m)
{
case 0:
$racename = "<font color='yellow' size='4'>Bellato";     
break;  
case 1:
$racename = "<font color='green' size='4'>Cora";     
break;  
case 2:
$racename = "<font color='red' size='4'>ACC";     
break;  
}
$k=0;
while($k<9)
{
$name["$k"]=$aname["$m"]["$k"];
$candser["$k"]=$aserial["$m"]["$k"];
$k++;
}
echo"
<tr>
<td><font color='yellow'>$racename</font></td></tr>
<tr><td><font color='red' size='3'>Arconde </font><input class='pole2' name='n0$m' value='$name[0]' type=text>
<input name='s0$m' value='$candser[0]' type=hidden></td></tr>

<tr><td><font color='white'>Conselhos</font><br>
<font color='white'>1: <input class='pole2' name='n1$m' value='$name[1]' type=text><br>
<input name='s1$m' value='$candser[1]' type=hidden>
<font color='white'>2: <input class='pole2' name='n5$m' value='$name[5]' type=text><br>
<input name='s5$m' value='$candser[5]' type=hidden></td></tr>

<tr><td><font color='white'>Time de Ataque </font><br> 
<font color='white'>1: <input class='pole2' name='n2$m' value='$name[2]' type=text><br>
<input name='s2$m' value='$candser[2]' type=hidden>
<font color='white'>2: <input class='pole2' name='n6$m' value='$name[6]' type=text><br>
<input name='s6$m' value='$candser[6]' type=hidden></td></tr>

<tr><td><font color='white'>Time de Defesa </font> <br>
<font color='white'>1: <input class='pole2' name='n3$m' value='$name[3]' type=text><br>
<input name='s3$m' value='$candser[3]' type=hidden>
<font color='white'>2: <input class='pole2' name='n7$m' value='$name[7]' type=text><br>
<input name='s7$m' value='$candser[7]' type=hidden></td></tr>

<tr><td><font color='white'>Time de Suporte </font> <br>
<font color='white'>1: <input class='pole2' name='n4$m' value='$name[4]' type=text><br>
<input name='s4$m' value='$candser[4]' type=hidden>
<font color='white'>2: <input class='pole2' name='n8$m' value='$name[8]' type=text><br>
<input name='s8$m' value='$candser[8]' type=hidden></td></tr>";
$m++;
}

echo"</table>
<br><input name='savepatr' type='submit' value='Salvar' ></td>
<td> <rows='5' disabled='disabled'>$_SESSION[error]<br>

";
$eserial=$serial+1;
$candlist=mssql_query("SELECT AName,Race,Serial FROM tbl_patriarch_candidate WHERE eSerial='$eserial' AND ClassType='255' AND State='1'");
echo"<b><font color='white'size='5'>Candidatos a eleicao</b><br><br><table>
<TH><font color='white'> Nome </TH> 
<TH><font color='white'> Race </TH>
<TH><font color='white'> Remover os candidatos</TH>";

while(list($candname,$candrace1,$candserial)=mssql_fetch_array($candlist))
{
switch($candrace1)
{
case 0:
$candrace = "<font color='yellow'>Bellato";     
break;  
case 1:
$candrace = "<font color='green'>Cora";     
break;  
case 2:
$candrace = "<font color='red'>ACC";     
break;  
}
echo"<form method='post'><tr >
<td width='20%' align='center'><font color='orange'>$candname</font></td>
<td width='20%' align='center'><font color='white'>$candrace</td>
<td width='20%' align='center'><input type='button' value='Tirar' type='submit' name='canddel'>
<input value='$candserial' type='hidden' name='candserial'></td>
<td width='40%'></td></tr></form>";
}

$electlist=mssql_query("SELECT AName,Race,Serial FROM tbl_patriarch_candidate WHERE eSerial='$eserial' AND ClassType='255' AND State='2'");
while(list($electname,$electrace1,$electserial)=mssql_fetch_array($electlist))
{
$electrace = switch_race($electrace1);     



echo"<form method='post'><tr >
<td width='20%' align='center'><font color='red'>$electname</font></td>
<td width='20%' align='center'>$electrace</td>
<td width='20%' align='center'><input value='Tirar' type='submit' name='electdel'>
<input value='$electserial' type='hidden' name='electserial'></td>
<td width='40%'></td></tr></form>";
}
echo"</table></td>
</tr></table></form>";
}
function save_patr()
{
$error="<font color='white'>Os Conselhos Foram Alterados.";
$eserial=sel_eserial();
$n=0;
while ($n<9) 
{
$m=0;
while ($m<3)
{
$name=$_POST["n{$n}{$m}"];
$serial=$_POST["s{$n}{$m}"];
if($name!='')
{
$sel1=mssql_fetch_array(mssql_query("SELECT Serial,Lv,Race FROM tbl_base WHERE Name='$name'"));
$sel2 = mssql_fetch_array(mssql_query("SELECT PvpPoint,GuildSerial,CharacterGrade FROM tbl_general WHERE Serial='$sel1[Serial]'"));
$sel3 = mssql_fetch_array(mssql_query("SELECT id FROM tbl_Guild WHERE Serial='$sel01[GuildSerial]'"));
switch($sel1['Race'])
{
case 0:
$race["$m"] = 0;     
break;  
case 1:
$race["$m"] = 0;     
break;  
case 2:
$race["$m"] = 1;     
break;  
case 3:
$race["$m"] = 1;     
break;  
case 4:
$race["$m"] = 2;     
break;  
}
if($race["$m"]==$m and $sel1['Serial']!='')
{
if($serial!='')
{
mssql_query("UPDATE tbl_patriarch_candidate SET Race='$m',Lv='$sel1[Lv]',PvpPoint='$sel2[PvpPoint]',ASerial='$sel1[Serial]',
AName='$name',GSerial='$sel2[GuildSerial]',GName='$sel3[id]',Grade='$sel2[CharacterGrade]' WHERE Serial='$serial'");
}
else
{
mssql_query("INSERT INTO tbl_patriarch_candidate (eSerial,Race,Lv,PvpPoint,ASerial,AName,GSerial,GName,ClassType,State,Grade) VALUES
('$eserial','$m','$sel1[Lv]','$sel2[PvpPoint]','$sel1[Serial]','$name','$sel2[GuildSerial]','$sel3[id]','$n','2','$sel2[CharacterGrade]')");
}
}
else
{$error.=" <font color='white'>O Personagem $name nao pode ser o arconte";}
}
else { mssql_query("DELETE FROM tbl_patriarch_candidate WHERE Serial='$serial'");}

$m++;
}
$n++;
}
echo"
<head><meta http-equiv='Refresh' content='0; $_SERVER[REQUEST_URI]'></head>
";
$_SESSION['error']="$error"; 
}
function switch_race($value)
{
switch($value)
{
case 0:
$race = "<font color='white'>Bellato";     
break;  
case 1:
$race = "<font color='white'>Cora";     
break;  
case 2:
$race = "<font color='white'>ACC";     
break;  
}
return $race;
}
function del_cand($serial)
{
mssql_query("DELETE FROM tbl_patriarch_candidate WHERE Serial='$serial'");
echo"
<head><meta http-equiv='Refresh' content='0; $_SERVER[REQUEST_URI]'></head>
";

}
if(isset($_POST['savepatr']))
{
save_patr();

}
if(isset($_POST['canddel']))
{
del_cand($_POST['candserial']);
}
if(isset($_POST['electdel']))
{
del_cand($_POST['electserial']);
}


?>