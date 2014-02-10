<?php
//// Including the configuration file ////
require('config.php');
//// Checking requirements ////
if(substr(phpversion(),0,3) < 5.2){die("Please upgrade your PHP installation to at least 5.2 or higher.");}
if(!function_exists('mysql_connect')){die("Your PHP installation does not support MySQL.");}
//// Connecting to the MySQL server ////
$connection=mysql_connect($mysql['host'], $mysql['user'], $mysql['pass']);
if (!($connection)){die("SQL Connection Error.");}
$databasetest=mysql_select_db($mysql['data']);
if(!($databasetest)){die("Database Error.");}
//// Creating the MySQL table if it does not exist ////
$dbinit=mysql_query("CREATE TABLE IF NOT EXISTS `".$mysql['table']."` (`id` int(11) NOT NULL AUTO_INCREMENT,`title` text NOT NULL,`name` text NOT NULL,`email` text NOT NULL,`date` text NOT NULL,`content` text NOT NULL,`ip` text NOT NULL,`op` int(11) NOT NULL,`tid` int(11) NOT NULL,`lock` int(11) NOT NULL,`ban` int(11) NOT NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=13 ;");
//// Functions ////
// Stripping HTML entities etc. from posts //
function removeSpecialChars($input,$textarea){
	$output=htmlentities($input, ENT_QUOTES | ENT_IGNORE, "UTF-8");
	$output=stripslashes($output);
	if($textarea) {
		$output=nl2br($output);
	}
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
	$bbcodecatch=array('/\[b\](.*?)\[\/b\]/is','/\[i\](.*?)\[\/i\]/is','/\[u\](.*?)\[\/u\]/is','/\[url\=(.*?)\](.*?)\[\/url\]/is','/\[url\](.*?)\[\/url\]/is','/\[spoiler\](.*?)\[\/spoiler\]/is');
	$bbcodereplace=array('<b>$1</b>','<i>$1</i>','<u>$1</u>','<a href="$1" rel="nofollow" title="$2 - $1">$2</a>','<a href="$1" rel="nofollow" title="$1">$1</a>','<span class="spoiler" />$1</span>');
	$content=preg_replace($bbcodecatch, $bbcodereplace, $content);
	return $content;
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
<meta name="description" content="<?=$ninechan['desc'];?>">
<?php if($ninechan['styleenable']){print("<link rel=\"stylesheet\" type=\"text/css\" href=\"".$ninechan['style']."\" />");} ?>
</head>
<body>
<h1><a href="./"><?=$ninechan['title'];?></a></h1>&nbsp;<i><?=$ninechan['desc'];?></i><hr />
<?php
if($ninechan['closed']){die("The ".$ninechan['title']." boards are closed right now.<br /><i>Reason: ".$ninechan['closedreason']."</i>");}
$bancheck=mysql_query("SELECT * FROM ".$mysql['table']." WHERE ip='".base64_encode($_SERVER['REMOTE_ADDR'])."'");
while($row=mysql_fetch_array($bancheck)){if($row['ban']){die("You have been banned from this board.");}}
if($_GET['v']=="index") {
	print("<h2>Threads</h2><h3><a href=?v=post>New Thread</a></h3><ol>");
	if($ninechan['sage']){$threads=mysql_query("SELECT * FROM ".$mysql['table']." WHERE op='1' ORDER BY id DESC LIMIT ".$ninechan['sagelimit']);}else{$threads=mysql_query("SELECT * FROM ".$mysql['table']." WHERE op='1' ORDER BY id DESC");}
	$num_rows=mysql_num_rows($threads);
	if(!$num_rows){print("There are no threads.");}
	while($row=mysql_fetch_array($threads)) {
		print("<li><a href=\"?v=thread&t=".$row['tid']."\">".$row['title']."</a></li>");
	}
	print("</ol><h3><a href=?v=post>New Thread</a></h3>");
} elseif(($_GET['v']=="thread")&&(isset($_GET['t']))) {
	if(!is_numeric($_GET['t'])){header('Location: ./');}
	$threads=mysql_query("SELECT * FROM ".$mysql['table']." WHERE tid='".mysql_real_escape_string(preg_replace('/\D/', '', $_GET['t']))."' ORDER BY id");
	$num_rows=mysql_num_rows($threads);
	if(!$num_rows){print("Non-existent thread.");}else{
		$tid="";$lock="";
		while($row=mysql_fetch_array($threads)) {
			$tid=$row['tid'];
			if($row['op']){$lock=$row['lock'];if(!$lock){print("<h2>Thread: ".$row['title']."</h2><h3><a href=?v=post&t=".$row['tid'].">New Reply</a></h3>");}else{print("<h2>Thread: ".$row['title']."</h2><h3>Locked</h3>");}}
			print("<fieldset id=".$row['id'].">");
			if(!$row['email']==""){print("<legend><b>".$row['title']."</b> <a href=\"#".$row['id']."\">by</a> <b><a href=\"mailto:".$row['email']."\">".$row['name']."</a></b></legend>");}else{print("<legend><b>".$row['title']."</b> <a href=\"#".$row['id']."\">by</a> <b>".$row['name']."</b></legend>");}
			print(parseBBcode($row['content'])."<br /><br />");
			if($row['ban']){print("<b><font size=2 class=ban>".$ninechan['bantext']."</font></b><br />");if($auth==md5($ninechan['modpass'])){print("<font size=2>[<a href=?v=mod&del&id=".$row['id']."&t=".$row['tid'].">Delete</a>] [<a href=?v=mod&ban=false&id=".$row['id']."&t=".$row['tid'].">Unban</a>]</font><br />");}}
			if($auth==md5($ninechan['modpass'])&&!$row['ban']){print("<font size=2>[<a href=?v=mod&del&id=".$row['id']."&t=".$row['tid'].">Delete</a>] [<a href=?v=mod&ban=true&id=".$row['id']."&t=".$row['tid'].">Ban</a>]");if(!$lock&&$row['op']){print(" [<a href=?v=mod&lock=true&id=".$row['tid'].">Lock thread</a>]</font><br />");}elseif($row['op']){print(" [<a href=?v=mod&lock=false&id=".$row['tid'].">Unlock thread</a>]</font><br />");}else{print("</font><br />");}}
			print("<i><font size=2>".$row['date']."</font></i></fieldset>");
		}
		if($auth==md5($ninechan['modpass'])){print("<font size=2>[<a href=?v=mod&del=purge&id=".$tid.">Purge thread</a>]</font>");}
		if(!$lock){print("<h3><a href=?v=post&t=".$tid.">New Reply</a></h3>");}else{print("<h3>Locked</h3>");}
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
			if(isset($_POST['tid'])){$lock=$row['lock'];}else{$lock=0;}
			if($lock){
				print("<h2>The thread you're trying to reply to is locked.</h2><meta http-equiv=\"refresh\" content=\"2; URL=".$_SERVER['PHP_SELF']."?v=thread&t=".$row['tid']."\">");
			}else{
				print("<h2>Reply to ".$row['title']." (ID: ".$row['tid'].")</h2><input type=hidden name=tid value='".$_GET['t']."'/>title*: <input type=text name=title value='Re: ".$row['title']."' /><br />");
			}
		}
	}else{
		print("<h2>New Thread</h2>Title*: <input type=text name=title /><br />");
	}
	if(!$lock){print("Name: <input type=text name=name /><br />Email: <input type=text name=email /><br />Comment*:<br /><textarea name=content rows=6 cols=48></textarea><br /><font size=2>* = Required</font><br /><input type=submit value=Submit /></form>");}
} elseif($_GET['v']=="submit") {
	if((isset($_POST['title']))&&(!$_POST['title']=="")&&(!strlen($_POST['title']) < $ninechan['titleminlength'])){$title = removeSpecialChars($_POST['title'], false);}else{die("<h2>Invalid title entered.</h2><meta http-equiv=\"refresh\" content=\"2; URL=".$_SERVER['PHP_SELF']."\">");}
	if((isset($_POST['name']))&&(!$_POST['name']=="")){$name = removeSpecialChars($_POST['name'], false);if(strstr($name,"#")){$name=(strstr($name,"#",true)."<span class=trip>!".parseTrip($_POST['name'])."</span>");}}else{$name="Anonymous";}
	if(isset($_POST['email'])){$email = removeSpecialChars($_POST['email'], false);if(($email=="noko")||($email=="nonoko")){$noredir=true;}}
	$date=date('d/m/Y @ g:iA T');
	if((isset($_POST['content']))&&(!$_POST['content']=="")&&(!strlen($_POST['content']) < $ninechan['commentminlength'])){$content = removeSpecialChars($_POST['content'], true);}else{die("<h2>No comment entered.</h2><meta http-equiv=\"refresh\" content=\"2; URL=".$_SERVER['PHP_SELF']."\">");}
	$ip=base64_encode($_SERVER['REMOTE_ADDR']);
	if(!isset($_POST['tid'])){$op=1;}else{$op=0;}
	if(isset($_POST['tid'])){$tid = removeSpecialChars($_POST['tid'], false);}else{$tidget=mysql_query("SELECT MAX(tid) AS tid FROM ".$mysql['table']." LIMIT 1");$num_rows=mysql_num_rows($tidget);$tid=0;while($row=mysql_fetch_array($tidget)){$tid=$row['tid'];}++$tid;}
	mysql_query("INSERT INTO `".$mysql['data']."`.`".$mysql['table']."` (`title`,`name`,`email`,`date`,`content`,`ip`,`op`,`tid`) VALUES ('$title','$name','$email','$date','$content','$ip','$op','$tid')");
	print("<h1>Posted!</h1>");
	if(@$noredir){print("<meta http-equiv=\"refresh\" content=\"1; URL=".$_SERVER['PHP_SELF']."?v=index\">");}else{print("<meta http-equiv=\"refresh\" content=\"1; URL=".$_SERVER['PHP_SELF']."?v=thread&t=".$tid."\">");}
} elseif($_GET['v']=="mod") {
	if($auth==md5($ninechan['modpass'])){
		if(isset($_POST['modkill'])){session_destroy();header('Location: ?v=mod');}
		print("<h2>Moderator Logout</h2><form method=post action=?v=mod>The Moderator tools show now appear next to posts.<br /><input type=submit value=Logout name=modkill /></form>");
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
		print("<h2>Moderator Login</h2><form method=post action=?v=mod><input type=password name=modpass /><input type=submit value=Login /></form>");
	}
} else {header('Location: ?v=index');}
?>
<h6>Powered by ninechan <?php if($ninechan['showversion']){print("v1.6 ");} ?>&copy; <a href="http://flashwave.pw/">Flashwave</a> 2014</h6>
</body>
</html>
