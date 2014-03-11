<?php
//// Including the configuration file ////
require('config.php');
include('lang/'.$ninechan['lang'].'.php');
//// Checking requirements ////
if(substr(phpversion(),0,3) < 5.2){die(L_PHP_OUTDATED);}
if(!function_exists('mysql_connect')){die(L_SQL_FUNCTION);}
//// Connecting to the MySQL server ////
$connection=mysql_connect($mysql['host'], $mysql['user'], $mysql['pass']);
if (!($connection)){die(L_SQL_CONNECT);}
$databasetest=mysql_select_db($mysql['data']);
if(!($databasetest)){die(L_SQL_DATABASE);}
//// Creating the MySQL table if it does not exist ////
$dbinit=mysql_query("CREATE TABLE IF NOT EXISTS `".$mysql['table']."` (`id` int(11) NOT NULL AUTO_INCREMENT,`title` text NOT NULL,`name` text NOT NULL,`email` text NOT NULL,`date` text NOT NULL,`content` text NOT NULL,`ip` text NOT NULL,`op` int(11) NOT NULL,`tid` int(11) NOT NULL,`lock` int(11) NOT NULL,`ban` int(11) NOT NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
//// Functions ////
// Stripping HTML entities etc. from posts //
function removeSpecialChars($input){
	$output=htmlentities($input, ENT_QUOTES | ENT_IGNORE, "UTF-8");
	$output=stripslashes($output);
	return $output;
}
// Parsing tripcodes //
function parseTrip($name){
	if(ereg("(#|!)(.*)", $name, $matches)){
		$cap=$matches[2];
		$cap=strtr($cap,"&amp;", "&");
		$cap=strtr($cap,",", ",");
		$salt=substr($cap."H.",1,2);
		$salt=preg_replace("[^\.-z]",".",$salt);
		$salt=strtr($salt,":;<=>?@[\\]^_`","ABCDEFGabcdef");
		return substr(crypt($cap,$salt),-10)."";
	}
}
// Parsing BBS codes //
function parseBBcode($content){
	$bbcodecatch=array('/\[b\](.*?)\[\/b\]/is','/\[i\](.*?)\[\/i\]/is','/\[u\](.*?)\[\/u\]/is','/\[url\=(.*?)\](.*?)\[\/url\]/is','/\[url\](.*?)\[\/url\]/is','/\[spoiler\](.*?)\[\/spoiler\]/is','/&gt;&gt;(.*[0-9])/i','/^&gt;(.*?)$/im','/youtube.com\/watch\?v=(.*[a-zA-Z0-9_])/i');
	$bbcodereplace=array('<b>$1</b>','<i>$1</i>','<u>$1</u>','<a href="$1" rel="nofollow" title="$2 - $1">$2</a>','<a href="$1" rel="nofollow" title="$1">$1</a>','<span class="spoiler">$1</span>','<a class="lquote" href="#$1">&gt;&gt;$1</a>','<span class="quote">&gt;$1</span>','<iframe width="420" height="315" src="//www.youtube.com/embed/$1" frameborder="0" allowfullscreen></iframe>');
	$content=preg_replace($bbcodecatch, $bbcodereplace, $content);
	return nl2br($content);
}
// Banning a post //
function banPost($id,$ban){
	global $mysql;
	if($ban){
		mysql_query("UPDATE `".$mysql['table']."` SET `ban`=1 WHERE `id`=".$id);
	}else{
		mysql_query("UPDATE `".$mysql['table']."` SET `ban`=0 WHERE `id`=".$id);
	}
}
// Removing a post //
function delPost($id){
	global $mysql;
	mysql_query("DELETE FROM `".$mysql['table']."` WHERE `id`=".$id);
}
// Removing every post in the thread (including OP) //
function pruneThread($id){
	global $mysql;
	mysql_query("DELETE FROM `".$mysql['table']."` WHERE `tid`=".$id);
}
// Locking a thread //
function lockThread($id,$lock){
	global $mysql;
	if($lock){
		mysql_query("UPDATE `".$mysql['table']."` SET `lock`=1 WHERE `tid`=".$id);
	}else{
		mysql_query("UPDATE `".$mysql['table']."` SET `lock`=0 WHERE `tid`=".$id);
	}
}
//// Starting Session ////
session_start();
$auth=@$_SESSION['mod'];
?>
<html>
<head>
<title><?=$ninechan['title'];?></title>
<?php
if($ninechan['descenable']){print("<meta name=\"description\" content=\"".$ninechan['desc']."\" />");}
if($ninechan['styleenable']){print("<link rel=\"stylesheet\" type=\"text/css\" href=\"".$ninechan['style']."\" />");} ?>
</head>
<body>
<h1><a href="./"><?=$ninechan['title'];?></a></h1><?php if($ninechan['descenable']){print("&nbsp;<i>".$ninechan['desc']."</i>");} ?><hr />
<?php
if($ninechan['closed']){die(L_BOARD_CLOSED."<br /><i>".L_REASON.": ".$ninechan['closedreason']."</i>");}
$bancheck=mysql_query("SELECT * FROM ".$mysql['table']." WHERE ip='".base64_encode($_SERVER['REMOTE_ADDR'])."'");
while($row=mysql_fetch_array($bancheck)){if($row['ban']){die(L_BANNED);}}
if($_GET['v']=="index") {
	print("<h2>".L_THREADS."</h2><h3><a href=?v=post>".L_NEWTHREAD."</a></h3><ol>");
	if($ninechan['sage']){$threads=mysql_query("SELECT * FROM ".$mysql['table']." WHERE op='1' ORDER BY id DESC LIMIT ".$ninechan['sagelimit']);}else{$threads=mysql_query("SELECT * FROM ".$mysql['table']." WHERE op='1' ORDER BY id DESC");}
	$num_rows=mysql_num_rows($threads);
	if(!$num_rows){print(L_EMPTY);}
	while($row=mysql_fetch_array($threads)) {
		print("<li><a href=\"?v=thread&t=".$row['tid']."\">".$row['title']."</a></li>");
	}
	print("</ol><h3><a href=?v=post>".L_NEWTHREAD."</a></h3>");
} elseif(($_GET['v']=="thread")&&(isset($_GET['t']))) {
	if(!is_numeric($_GET['t'])){header('Location: ./');}
	$threads=mysql_query("SELECT * FROM ".$mysql['table']." WHERE tid='".mysql_real_escape_string(preg_replace('/\D/', '', $_GET['t']))."' ORDER BY id");
	$num_rows=mysql_num_rows($threads);
	if(!$num_rows){print(L_NONEXISTENT);}else{
		$tid="";$lock="";
		while($row=mysql_fetch_array($threads)) {
			$tid=$row['tid'];
			if($row['op']){$lock=$row['lock'];if(!$lock){print("<h2>".L_THREAD.": ".$row['title']."</h2><h3><a href=?v=post&t=".$row['tid'].">".L_NEWREPLY."</a></h3>");}else{print("<h2>".L_THREAD.": ".$row['title']."</h2><h3>".L_LOCKED."</h3>");}if($auth==md5($ninechan['modpass'])){print("<font size=2>[<a href=?v=mod&del=purge&id=".$tid.">".L_PURGE."</a>]");if(!$lock){print(" [<a href=?v=mod&lock=true&id=".$tid.">".L_LOCK."</a>]</font>");}else{print(" [<a href=?v=mod&lock=false&id=".$tid.">".L_UNLOCK."</a>]</font>");}}}
			print("<fieldset id=".$row['id'].">");
			if(!$row['email']==""){print("<legend><b>".$row['title']."</b> <a href=\"#".$row['id']."\">".L_BY."</a> <b><a href=\"mailto:".$row['email']."\">".$row['name']."</a></b></legend>");}else{print("<legend><b>".$row['title']."</b> <a href=\"#".$row['id']."\">".L_BY."</a> <b>".$row['name']."</b></legend>");}
			print(parseBBcode($row['content'])."<br /><br />");
			if($row['ban']){print("<b><font size=2 class=ban>".$ninechan['bantext']."</font></b><br />");if($auth==md5($ninechan['modpass'])){print("<font size=2>[<a href=?v=mod&del&id=".$row['id']."&t=".$row['tid'].">".L_DELETE."</a>] [<a href=?v=mod&ban=false&id=".$row['id']."&t=".$row['tid'].">".L_UNBAN."</a>] [IP: ".base64_decode($row['ip'])."]</font><br />");}}
			if($auth==md5($ninechan['modpass'])&&!$row['ban']){print("<font size=2>[<a href=?v=mod&del&id=".$row['id']."&t=".$row['tid'].">".L_DELETE."</a>] [<a href=?v=mod&ban=true&id=".$row['id']."&t=".$row['tid'].">".L_BAN."</a>] [IP: ".base64_decode($row['ip'])."]</font><br />");}
			print("<i><font size=2>".$row['date']."</font></i></fieldset>");
		}
		if($auth==md5($ninechan['modpass'])){print("<font size=2>[<a href=?v=mod&del=purge&id=".$tid.">".L_PURGE."</a>]");if(!$lock){print(" [<a href=?v=mod&lock=true&id=".$tid.">".L_LOCK."</a>]</font>");}else{print(" [<a href=?v=mod&lock=false&id=".$tid.">".L_UNLOCK."</a>]</font>");}}
		if(!$lock){print("<h3><a href=?v=post&t=".$tid.">".L_NEWREPLY."</a></h3>");}else{print("<h3>".L_LOCKED."</h3>");}
	}
} elseif($_GET['v']=="post") {
	print("<form method=post action=?v=submit>");
	$lock="";
	if(isset($_GET['t'])){
		if(!is_numeric($_GET['t'])){header('Location: ./');}
		$threads=mysql_query("SELECT * FROM ".$mysql['table']." WHERE tid='".mysql_real_escape_string(preg_replace('/\D/', '', $_GET['t']))."' and op='1' ORDER BY id");
		$num_rows=mysql_num_rows($threads);
		if(!$num_rows){header('Location: ./');}
		while($row=mysql_fetch_array($threads)) {
			if(isset($_GET['t'])){$lock=$row['lock'];}
			if($lock){
				print("<h2>".L_LOCKEDMSG."</h2><meta http-equiv=\"refresh\" content=\"2; URL=".$_SERVER['PHP_SELF']."?v=thread&t=".$row['tid']."\">");
			}else{
				print("<h2>".L_RETO." ".$row['title']." (ID: ".$row['tid'].")</h2><input type=hidden name=tid value='".$_GET['t']."'/>".L_TITLE."*: <input type=text name=title value='Re: ".$row['title']."' /><br />");
			}
		}
	}else{
		print("<h2>".L_NEWTHREAD."</h2>".L_TITLE."*: <input type=text name=title /><br />");
	}
	if(!$lock){print("".L_NAME.": <input type=text name=name /><br />".L_EMAIL.": <input type=text name=email /><br />".L_COMMENT."*:<br /><textarea name=content rows=6 cols=48></textarea><br /><font size=2>* = ".L_REQUIRED."</font><br /><input type=submit value=".L_SUBMIT." /></form>");}
} elseif($_GET['v']=="submit") {
	if(strlen($_POST['title']) >= $ninechan['titleminlength']){$title = removeSpecialChars($_POST['title']);}else{die("<h2>".L_INVALIDTITLE."</h2><meta http-equiv=\"refresh\" content=\"2; URL=".$_SERVER['PHP_SELF']."\">");}
	if((isset($_POST['name']))&&(!$_POST['name']=="")){$name = removeSpecialChars($_POST['name']);if(strstr($name,"#")){$name=(strstr($name,"#",true)."<span class=trip>!".parseTrip($_POST['name'])."</span>");}}else{$name="Anonymous";}
	if(isset($_POST['email'])){$email = removeSpecialChars($_POST['email']);if(($email=="noko")||($email=="nonoko")){$noredir=true;}}
	$date=date('d/m/Y @ g:iA T');
	if(strlen($_POST['content']) >= $ninechan['commentminlength']){$content = removeSpecialChars($_POST['content']);}else{die("<h2>".L_NOCOMMENT."</h2><meta http-equiv=\"refresh\" content=\"2; URL=".$_SERVER['PHP_SELF']."\">");}
	$ip=base64_encode($_SERVER['REMOTE_ADDR']);
	if(!isset($_POST['tid'])){$op=1;}else{$op=0;}
	if(isset($_POST['tid'])){$tid = removeSpecialChars($_POST['tid']);}else{$tidget=mysql_query("SELECT MAX(tid) AS tid FROM ".$mysql['table']." LIMIT 1");$num_rows=mysql_num_rows($tidget);$tid=0;while($row=mysql_fetch_array($tidget)){$tid=$row['tid'];}++$tid;}
	mysql_query("INSERT INTO `".$mysql['data']."`.`".$mysql['table']."` (`title`,`name`,`email`,`date`,`content`,`ip`,`op`,`tid`) VALUES ('$title','$name','$email','$date','$content','$ip','$op','$tid')");
	print("<h1>".L_POSTED."</h1>");
	if(@$noredir){print("<meta http-equiv=\"refresh\" content=\"1; URL=".$_SERVER['PHP_SELF']."?v=index\">");}else{print("<meta http-equiv=\"refresh\" content=\"1; URL=".$_SERVER['PHP_SELF']."?v=thread&t=".$tid."\">");}
} elseif($_GET['v']=="mod") {
	if($auth==md5($ninechan['modpass'])){
		if(isset($_POST['modkill'])){session_destroy();header('Location: ?v=mod');}
		print("<h2>".L_MODLOGOUT."</h2><form method=post action=?v=mod>".L_MODTOOLS."<br /><input type=submit value=".L_LOGOUT." name=modkill /></form>");
		if((isset($_GET['ban']))&&(isset($_GET['id']))&&(isset($_GET['t']))){
			if($_GET['ban']=="true"){
				banPost($_GET['id'],true);
			}else{
				banPost($_GET['id'],false);
			}
			header('Location: ?v=thread&t='.$_GET['t']);
		}
		if((isset($_GET['del']))&&(isset($_GET['id']))){
			if($_GET['del']=="purge"){
				pruneThread($_GET['id']);
				header('Location: ?v=index');
			}else{
				delPost($_GET['id']);
				header('Location: ?v=thread&t='.$_GET['t']);
			}
		}
		if((isset($_GET['lock']))&&(isset($_GET['id']))){
			if($_GET['lock']=="true"){
				lockThread($_GET['id'],true);
			}else{
				lockThread($_GET['id'],false);
			}
			header('Location: ?v=thread&t='.$_GET['id']);
		}
	}else{
		if(isset($_POST['modpass'])){if(md5($_POST['modpass'])==md5($ninechan['modpass'])){$_SESSION['mod']=md5($ninechan['modpass']);}header('Location: ?v=mod');}
		print("<h2>".L_MODLOGIN."</h2><form method=post action=?v=mod><input type=password name=modpass /><input type=submit value=".L_LOGIN." /></form>");
	}
} else {header('Location: ?v=index');}
?>
<!-- Please retain the full copyright notice below including the link to flashii.net. This not only gives respect to the amount of time given freely by the developer but also helps build interest, traffic and use of ninechan. Thanks, Julian van de Groep -->
<h6><a href="http://9chan.us">ninechan</a> <?php if($ninechan['showversion']){print("v1.8 ");} ?>&copy; <a href="http://flashii.net/">Flashwave</a></h6>
</body>
</html>
