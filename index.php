<?php
//// Including the configuration file ////
require('config.php'); //-- Main Configuration
include('lang/'.$ninechan['lang'].'.php'); //-- Language file
//// Checking requirements ////
if(substr(phpversion(),0,3) < 5.3){die(L_PHP_OUTDATED);} //-- PHP Outdated Noticed
if(!function_exists('mysqli_connect')){die(L_SQL_FUNCTION);} //-- MySQLI not installed Notice
if(file_exists("updatedb.php")){die(L_UDB_EXISTS);} //-- Ninechan updater still present Notice
//// Checking SQL connection ////
$sqldb = new mysqli($sql['host'],$sql['user'],$sql['pass'],$sql['data']); //-- Connect to the SQL server
if($sqldb->connect_errno){die(L_SQL_CONNECT);} //-- die on connection error
//// Initialize SQL database ////
$dbinit=$sqldb->query("CREATE TABLE IF NOT EXISTS `".$sql['table']."` (`id` int(11) NOT NULL AUTO_INCREMENT,`title` text NOT NULL,`name` text NOT NULL,`trip` text NOT NULL,`email` text NOT NULL,`date` text NOT NULL,`content` text NOT NULL,`password` text NOT NULL,`ip` text NOT NULL,`op` int(11) NOT NULL,`tid` int(11) NOT NULL,`locked` int(11) NOT NULL,`ban` int(11) NOT NULL,`del` int(11) NOT NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=latin1;"); //-- Create database table when it doesn't exist
//// Functions ////
// Stripping HTML entities etc. from posts //
function removeSpecialChars($input){
	$output=htmlentities($input, ENT_QUOTES | ENT_IGNORE, "UTF-8");
	$output=stripslashes($output);
	return $output;
}
// Parsing tripcodes //
function parseTrip($name){
	if(preg_match("/(#|!)(.*)/", $name, $matches)){ /* Regular Tripcode */
		$cap=$matches[2];
		$cap=mb_convert_encoding($cap,'SJIS','UTF-8');
		$cap=str_replace('#', '', $cap);
		$cap=str_replace('&', '&amp;', $cap);
		$cap=str_replace('"', '&quot;', $cap);
		$cap=str_replace("'", '&#39;', $cap);
		$cap=str_replace('<', '&lt;', $cap);
		$cap=str_replace('>', '&gt;', $cap);
		$salt=substr($cap.'H.',1,2);
		$salt=preg_replace('/[^.\/0-9:;<=>?@A-Z\[\\\]\^_`a-z]/','.',$salt);
		$salt=strtr($salt,':;<=>?@[\]^_`','ABCDEFGabcdef');
		$trip=substr(crypt($cap,$salt),-10);
		return $trip;
	}
}
// Parsing BBS codes //
function parseBBcode($content){
	$bbcodecatch=array('/\[b\](.*?)\[\/b\]/is','/\[i\](.*?)\[\/i\]/is','/\[u\](.*?)\[\/u\]/is','/\[url\=(.*?)\](.*?)\[\/url\]/is','/\[url\](.*?)\[\/url\]/is','/\[spoiler\](.*?)\[\/spoiler\]/is','/&gt;&gt;(.*[0-9])/i','/^&gt;(.*?)$/im','/^.*(youtu.be|youtube.com\/embed\/|watch\?v=|\&v=)([^!<>@&#\/\s]*)/is');
	$bbcodereplace=array('<b>$1</b>','<i>$1</i>','<u>$1</u>','<a href="$1" rel="nofollow" title="$2 - $1">$2</a>','<a href="$1" rel="nofollow" title="$1">$1</a>','<span class="spoiler">$1</span>','<a class="lquote" href="#$1">&gt;&gt;$1</a>','<span class="quote">&gt;$1</span>','<object type="application/x-shockwave-flash" style="width:425px; height:350px;" data="http://www.youtube.com/v/$2"><param name="movie" value="http://www.youtube.com/v/$2" /></object>');
	$content=preg_replace($bbcodecatch, $bbcodereplace, $content);
	return nl2br($content);
}
// Generating Random Password //
function generatePassword() {
	$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_@#$!*\/[]{}=+';
	for ($i = 0, $pass = ''; $i < 34; $i++) {
		$index = rand(0, mb_strlen($chars) - 1);
		$pass .= mb_substr($chars, $index, 1);
	}
	return $pass;
}
// Banning a post //
function banPost($id,$ban){
	global $sql;
	global $sqldb;
	if($ban){
		$sqldb->query("UPDATE `".$sql['table']."` SET `ban`=1 WHERE `id`=".$id);
	}else{
		$sqldb->query("UPDATE `".$sql['table']."` SET `ban`=0 WHERE `id`=".$id);
	}
}
// Removing a post //
function delPost($id,$del){
	global $sql;
	global $sqldb;
	if($del){
		$sqldb->query("UPDATE `".$sql['table']."` SET `del`=1 WHERE `id`=".$id);
	}else{
		$sqldb->query("UPDATE `".$sql['table']."` SET `del`=0 WHERE `id`=".$id);
	}
}
// Removing every post in the thread (including OP) //
function pruneThread($id,$prune){
	global $sql;
	global $sqldb;
	if($prune){
		$sqldb->query("UPDATE `".$sql['table']."` SET `del`=1 WHERE `tid`=".$id);
	}else{
		$sqldb->query("UPDATE `".$sql['table']."` SET `del`=0 WHERE `tid`=".$id);
	}
}
// Locking a thread //
function lockThread($id,$lock){
	global $sql;
	global $sqldb;
	if($lock){
		$sqldb->query("UPDATE `".$sql['table']."` SET `locked`=1 WHERE `tid`=".$id);
	}else{
		$sqldb->query("UPDATE `".$sql['table']."` SET `locked`=0 WHERE `tid`=".$id);
	}
}
//// reCAPTCHA stuff ////
if($ninechan['recaptcha']){
	require($ninechan['recaptchalib']);
}
//// Starting Session ////
session_start();
$auth=@$_SESSION['mod'];
?>
<html>
<head>
<meta http-equiv="Content-type" content="text/html; charset=<?=$ninechan['charset'];?>">
<title><?=$ninechan['title'];?></title>
<?php
if($ninechan['descenable']){print('<meta name="description" content="'.$ninechan['desc'].'" />');}
?>
<script type="text/javascript">
//// Function to write to a cookie ////
function setCookie(name,content,expire) {
	if(expire=="forever"){var expire = 60*60*24*365*99;}
	if(expire=="default"){var expire = 60*60*24*7;}
	document.cookie='<?php print($ninechan['cookieprefix']); ?>'+name+'='+content+';max-age='+expire;
}
//// Function to delete a cookie ////
function delCookie(name) {
	document.cookie='<?php print($ninechan['cookieprefix']); ?>'+name+'=;max-age=1;path=/'
}
//// Function to get data from a cookie ////
function getCookie(name) {
	return (name = new RegExp('(?:^|;\\s*)' + ('' + '<?php print($ninechan['cookieprefix']); ?>'+name).replace(/[-[\]{}()*+?.,\\^$|#\s]/g, '\\$&') + '=([^;]*)').exec(document.cookie)) && name[1];
}
//// Get main style ////
function getMainStyle() {
	var i,a;
	for(i=0; (a = document.getElementsByTagName('link')[i]); i++) {
		if(a.getAttribute('rel').indexOf('style') != -1 && a.getAttribute('rel').indexOf('alt') == -1 && a.getAttribute('title')) {
			return a.getAttribute('title');
		}
	}
	return null;
}
//// Get the currently active style ////
function getActiveStyle() {
	var i, a;
	for(i=0; (a = document.getElementsByTagName('link')[i]); i++) {
		if(a.getAttribute('rel').indexOf('style') != -1 && a.getAttribute('title') && !a.disabled) {
			return a.getAttribute('title');
		}
	}
	return null;
}
//// Switch to another style ////
function setStyle(title) {
	var i, a, main;
	var titleFound = false;
	setCookie('style',title,'forever');
	for(i=0; (a = document.getElementsByTagName('link')[i]); i++) {
		if(a.getAttribute('rel').indexOf('style') != -1 && a.getAttribute('title')) {
			a.disabled = true;
			if(a.getAttribute('title') == title) {
				a.disabled = false;
				titleFound = true;
			}
		}
	}
	if(!titleFound && title != null) {
		setStyle(getMainStyle());
	}
}
//// Initiate Frontend Javascript Data ////
function init() {
	setStyle(getCookie('style'));
}
</script>
<?php
if($ninechan['styleenable']){ //-- Check if styles are enabled
	foreach($ninechan['styles'] as $style){ //-- Get styles from array
		$alternate = ($style == $ninechan['defaultstyle']) ? '' : 'alternate ';  //-- Append "alternate " if the style isn't the default style
		print('<link rel="'.$alternate.'stylesheet" type="text/css" href="'.$style.'" title="'.str_replace('.css', '', $style).'" />'); //-- List every style
	}
}
?>
</head>
<body onload="init();">
<h1><a href="./"><?=$ninechan['title'];?></a></h1><?php if($ninechan['descenable']){print("&nbsp;<i>".$ninechan['desc']."</i>");} ?><hr />
<?php
if($ninechan['closed']){die(L_BOARD_CLOSED."<br /><i>".L_REASON.": ".$ninechan['closedreason']."</i>");} //-- die if board is set as closed in the config file
$bancheck=$sqldb->query("SELECT * FROM ".$sql['table']." WHERE ip='".base64_encode($_SERVER['REMOTE_ADDR'])."'"); //-- Check if poster IP is banned
while($row=$bancheck->fetch_array(MYSQLI_ASSOC)){if($row['ban']){die(L_BANNED);}}
if(!isset($_COOKIE[$ninechan['cookieprefix'].'pass'])) { //-- Check if pass cookie is set if not set it
	setcookie($ninechan['cookieprefix']."pass",generatePassword(),time()+604800,"/",$_SERVER['SERVER_NAME']); //-- Generate random password
}
if($_GET['v']=="index") {
	print("<h2>".L_THREADS."</h2><h3><a href=?v=post>".L_NEWTHREAD."</a></h3><ol>");
	if($ninechan['sage']){ //-- Check if thread list limit is set
		$threads=$sqldb->query("SELECT * FROM ".$sql['table']." WHERE `del`=0 AND `op`=1 ORDER BY date DESC LIMIT ".$ninechan['sagelimit']);
	} else {
		$threads=$sqldb->query("SELECT * FROM ".$sql['table']." WHERE `del`=0 ORDER BY date DESC");
	}
	if(!$threads->num_rows){ //-- If there's no thread display message
		print(L_EMPTY);
	} else { //-- Otherwise list threads
		while($row=$threads->fetch_array(MYSQLI_ASSOC)) {
			if($row['op']){
				print("<li><a href=\"?v=thread&t=".$row['tid']."\">".$row['title']."</a></li>");
			}
		}
	}
	print("</ol><h3><a href=?v=post>".L_NEWTHREAD."</a></h3>");
} elseif(($_GET['v']=="thread")&&(isset($_GET['t']))) {
	if(!is_numeric($_GET['t'])){ //-- Return to index if ?t= value is not numeric
		header('Location: ./');
	}
	$threads=$sqldb->query("SELECT * FROM ".$sql['table']." WHERE tid='".$sqldb->real_escape_string(preg_replace('/\D/', '', $_GET['t']))."' ORDER BY id"); //-- Get posts with same thread ID from the database
	if(!$threads->num_rows){ //-- Display message if thread doesn't exist
		print(L_NONEXISTENT);
	} else {
		$tid=null;$lock=null;$del=null;$name=null;$trip=null; //-- Define variables so PHP is happy
		while($row=$threads->fetch_array(MYSQLI_ASSOC)) {
			$tid=$row['tid']; //-- Assign thread ID
			if($row['op']){ //-- Check if post is OP
				$lock=$row['locked']; //-- Check if thread is locked
				if(!$lock){
					print("<h2>".L_THREAD.": ".$row['title']."</h2><h3><a href=?v=post&t=".$row['tid'].">".L_NEWREPLY."</a></h3>");
				} else {
					print("<h2>".L_THREAD.": ".$row['title']."</h2><h3>".L_LOCKED."</h3>");
				}
				if($auth==$ninechan['modpass']){ //-- Show special "thread management orientated" moderator tools
					print("<font size=2>[<a href=?v=mod&del=purge&id=".$tid.">".L_PURGE."</a>]");
					if(!$lock){
						print(" [<a href=?v=mod&lock=true&id=".$tid.">".L_LOCK."</a>]</font>");
					} else {
						print(" [<a href=?v=mod&lock=false&id=".$tid.">".L_UNLOCK."</a>]</font>");
					}
				}
			}
			if(!$row['del']){ //-- Check if post isn't marked as deleted
				if($ninechan['forcedanon']){ //-- Check if forced anon is enabled
					$name=$ninechan['anonname'];
					$trip=null;
				} elseif($ninechan['modsareanon']==1&&in_array($row['trip'],$ninechan['modtrip'])){ //-- Check if forced anon for mods is enabled
					$name=$ninechan['anonname'];
					$trip=null;
				} elseif($ninechan['modsareanon']==2&&in_array($row['trip'],$ninechan['modtrip'])){ //-- Check if forced trip anon for mods is enabled
					$name=$row['name'];
					$trip=null;
				} elseif($ninechan['adminsareanon']==1&&in_array($row['trip'],$ninechan['admintrip'])){ //-- Check if forced anon for admins is enabled
					$name=$ninechan['anonname'];
					$trip=null;
				} elseif($ninechan['adminsareanon']==2&&in_array($row['trip'],$ninechan['admintrip'])){ //-- Check if forced trip anon for admins is enabled
					$name=$row['name'];
					$trip=null;
				} else { //-- If not get name from the database
					if(empty($row['name'])){
						$name=$ninechan['anonname'];
					} else {
						$name=$row['name'];
					}
					if(!empty($row['trip'])){
						$trip='<span class="trip">!'.$row['trip'].'</span>';
					} else {
						$trip=null;
					}
				}
				print("<fieldset id=".$row['id'].">");
				print("<legend><b>".$row['title']."</b> <a href=\"#".$row['id']."\">".L_BY."</a> <b>");
				if(!empty($row['email'])){
					print('<a href="mailto:'.$row['email'].'">'.$name.$trip.'</a>');
				} else {
					print($name.$trip);
				}
				if(in_array($row['trip'],$ninechan['admintrip'])){ //-- Check if tripcode is Admin
					print(' <span class="admincap">## Admin</span>');
				} elseif(in_array($row['trip'],$ninechan['modtrip'])){ //-- Check if tripcode is Mod
					print(' <span class="modcap">## Mod</span>');
				}
				print("</b></legend>");
				print(parseBBcode($row['content'])."<br /><br />"); //-- Parse BBcodes and Quotation arrows on post content
				if($row['ban']){ //-- Check if user was banned for this post
					print("<b><font size=2 class=ban>".$ninechan['bantext']."</font></b><br />"); //-- USER WAS BANNED FOR THIS POST
					if($auth==$ninechan['modpass']){
						print("<font size=2>[<a href=?v=mod&del=true&id=".$row['id']."&t=".$row['tid'].">".L_DELETE."</a>] [<a href=?v=mod&ban=false&id=".$row['id']."&t=".$row['tid'].">".L_UNBAN."</a>] [IP: ".base64_decode($row['ip'])."]</font><br />"); //-- Show unban button in moderator tools
					}
				}
				if($auth==$ninechan['modpass']&&!$row['ban']){
					print("<font size=2>[<a href=?v=mod&del=true&id=".$row['id']."&t=".$row['tid'].">".L_DELETE."</a>] [<a href=?v=mod&ban=true&id=".$row['id']."&t=".$row['tid'].">".L_BAN."</a>] [IP: ".base64_decode($row['ip'])."]</font><br />"); //-- Regular mod tools
				}
				print('<font size=2><i>'.date($ninechan['dateFormat'], $row['date']).' <a href="#'.$row['id'].'">No.</a> <a href="?v=post&t='.$tid.'&text=>>'.$row['id'].'">'.$row['id'].'</a> [<a href="?v=del&id='.$row['id'].'" title="L_DELPOST">X</a>]</i></font></fieldset>'); //-- Date, ID, etc.
			}
		}
		if($auth==$ninechan['modpass']){ //-- Show thread orientated moderator tools again
			print("<font size=2>[<a href=?v=mod&del=purge&id=".$tid.">".L_PURGE."</a>]");
			if(!$lock){
				print(" [<a href=?v=mod&lock=true&id=".$tid.">".L_LOCK."</a>]</font>");
			} else {
				print(" [<a href=?v=mod&lock=false&id=".$tid.">".L_UNLOCK."</a>]</font>");
			}
		}
		if(!$lock){ //-- Display Reply if thread isn't locked
			print("<h3><a href=?v=post&t=".$tid.">".L_NEWREPLY."</a></h3>");
		} else { //-- Display Locked if thread is locked
			print("<h3>".L_LOCKED."</h3>");
		}
	}
} elseif($_GET['v']=="post") {
	print('<form method="post" action="'.$_SERVER['PHP_SELF'].'?v=submit"><table id="postForm" class="postForm"><tbody>');
	$lock=null;$title=null; //-- Define variables so PHP is happy
	if(isset($_GET['t'])){ //-- Check if ?t= is set, if yes go into reply mode
		if(!is_numeric($_GET['t'])) {
			header('Location: ./'); //-- Redirect to index if ?t= isn't numeric
		}
		$threads=$sqldb->query("SELECT * FROM ".$sql['table']." WHERE tid='".$sqldb->real_escape_string(preg_replace('/\D/', '', $_GET['t']))."' and op='1' ORDER BY id"); //-- Get data from database
		if(!$threads->num_rows) { //-- If thread doesn't exist just go straight back to the index
			header('Location: ./');
		}
		while($row=$threads->fetch_array(MYSQLI_ASSOC)) { //-- Check if thread isn't locked
			if(isset($_GET['t'])){
				$lock=$row['locked'];
			}
			if($lock){ //-- Display message if thread is locked
				print('<h2>'.L_LOCKEDMSG.'</h2><meta http-equiv="refresh" content="2; URL="'.$_SERVER['PHP_SELF'].'?v=thread&t='.$row['tid'].'" />');
			} else { //-- If not display thread based header stuff
				print('<h2>'.L_RETO.' '.$row['title'].' (ID: '.$row['tid'].')</h2><input type="hidden" name="tid" value="'.$_GET['t'].'" />');
				$title=('Re: '.$row['title']);
			}
		}
	} else { //-- If not in reply mode display New Thread
		print('<h2>'.L_NEWTHREAD.'</h2>');
	}
	if(isset($_GET['text'])) { //-- Check if predefined text is set in the url
		$contentbox=$_GET['text']."\n";
	} else { //-- If not set to null so PHP is happy
		$contentbox=null;
	}
	if(!$lock){ //-- Only display post page if thread isn't locked
		print('<tr><td>'.L_NAME.'</td><td><input name="name" type="text" value="'.@$_COOKIE[$ninechan['cookieprefix'].'name'].'" /></td></tr>');
		print('<tr><td>'.L_EMAIL.'</td><td><input name="email" type="text" value="'.@$_COOKIE[$ninechan['cookieprefix'].'email'].'" /></td></tr>');
		print('<tr><td>'.L_TITLE.'</td><td><input name="title" type="text" value="'.$title.'" /></td></tr>');
		print('<tr><td>'.L_COMMENT.'</td><td><textarea name="content" rows="6" cols="48">'.$contentbox.'</textarea></td></tr>');
		if($ninechan['recaptcha']){ //-- Display reCAPTCHA if enabled in config
			print('<tr><td>'.L_VERIFICATION.'</td><td>'.recaptcha_get_html($ninechan['recaptchapublic']).'</td></tr>');
		}
		print('<tr><td>'.L_PASSWORD.'</td><td><input name="password" type="password" placeholder="'.L_PASSWORDCONTEXT.'" value="'.@$_COOKIE[$ninechan['cookieprefix'].'pass'].'" /> <input value="'.L_SUBMIT.'" type="submit" /></td></tr>');
		print('</tbody></table></form>');
	}
} elseif($_GET['v']=="submit") {
	if($ninechan['recaptcha'])	{
		$recaptcha = recaptcha_check_answer($ninechan['recaptchaprivate'],$_SERVER['REMOTE_ADDR'],$_POST['recaptcha_challenge_field'],$_POST['recaptcha_response_field']); //-- Check CAPTCHA data
		if(!$recaptcha->is_valid) { //-- If CAPTCHA is invalid die and display error message
			die('<h2>'.L_INVALIDCAPTCHA.'</h2><meta http-equiv="refresh" content="2; URL='.$_SERVER['PHP_SELF'].'" />');
		}
	}
	if(!empty($_POST['title']) && strlen($_POST['title']) <= $ninechan['titlemaxlength']) { //-- Check if title isn't longer than allowed and isn't empty
		$title = removeSpecialChars($_POST['title']); //-- Removed "exploitable" characters from the title
	} else {
		die('<h2>'.L_INVALIDTITLE.'</h2><meta http-equiv="refresh" content="2; URL='.$_SERVER['PHP_SELF'].'" />'); //-- If conditions aren't met display an error message and die
	}
	if(isset($_POST['name']) && !empty($_POST['name'])){ //-- Check if name is set otherwise leave variables null
		$name = removeSpecialChars($_POST['name']);
		setcookie($ninechan['cookieprefix']."name",$name,time()+604800,"/",$_SERVER['SERVER_NAME']); //-- Assign it to a cookie
		if(strstr($name,"#")) { //-- Check if # is set in name indicating a tripcode
			$name = (strstr($name,"#",true));
			$trip = parseTrip($_POST['name']);
		} else { //-- if not just null it
			$trip=null;
		}
	} else {
		$name=null;
		$trip=null;
		}
	if(isset($_POST['email'])) { //-- Check if email isset
		$email = removeSpecialChars($_POST['email']);
		setcookie($ninechan['cookieprefix']."email",$email,time()+604800,"/",$_SERVER['SERVER_NAME']); //-- Assign it to a cookie
		if($email=="noko"){ //-- Check for noko and set email to null
			$noredir=true;
			$email=null;
		}
	}
	$date = time(); //-- Assigning time(), nothing special here
	if(!empty($_POST['content']) && strlen($_POST['content']) <= $ninechan['commentmaxlength']) { //-- Check if comment is set and isn't too long
		$content = removeSpecialChars($_POST['content']); //-- Clean exploitable chars
	} else { //-- die if not comment is set
		die('<h2>'.L_NOCOMMENT.'</h2><meta http-equiv="refresh" content="2; URL='.$_SERVER['PHP_SELF'].'" />');
	}
	if(isset($_POST['password'])) { //-- Check if password is set
		$password = md5($_POST['password']); //-- Hash it
		setcookie($ninechan['cookieprefix']."pass",$_POST['password'],time()+604800,"/",$_SERVER['SERVER_NAME']); //-- Assign it to a cookie
	} else { //-- If not generate a random password
		$genpass = generatePassword();
		$password = md5($genpass); //-- Hash is
		setcookie($ninechan['cookieprefix']."pass",$genpass,time()+604800,"/",$_SERVER['SERVER_NAME']); //-- Assign it
	}
	$ip = base64_encode($_SERVER['REMOTE_ADDR']); //-- Base64 encode ip address
	if(!isset($_POST['tid'])) { //-- Check if Thread ID is not set
		$op = 1; //-- Set post to OP
		$tidget = $sqldb->query("SELECT MAX(tid) AS tid FROM ".$sql['table']." LIMIT 1"); //-- Get latest thread ID from database
		$tid = ++$tidget->fetch_array(MYSQLI_ASSOC)['tid']; //-- Add one to it
	} else {
		$op = 0; //-- Set post to regular post
		$tid = removeSpecialChars($_POST['tid']); //-- Get tid from post
	}
	$sqldb->query("INSERT INTO `".$sql['data']."`.`".$sql['table']."` (`title`,`name`,`trip`,`email`,`date`,`content`,`password`,`ip`,`op`,`tid`) VALUES ('$title','$name','$trip','$email','$date','$content','$password','$ip','$op','$tid')"); //-- Store it in the database
	setcookie($ninechan['cookieprefix']."cooldown",time(),time()+604800,"/",$_SERVER['SERVER_NAME']); //-- Set time of last post
	print('<h1>'.L_POSTED.'</h1>'); //-- Display Posted message when message is posted
	if(@$noredir) { //-- If noko is set as email redirect to index after making the post
		print('<meta http-equiv="refresh" content="1; URL='.$_SERVER['PHP_SELF'].'?v=index" />');
	} else { //-- If not redirect to thread
		print('<meta http-equiv="refresh" content="1; URL='.$_SERVER['PHP_SELF'].'?v=thread&t='.$tid.'" />');
	}
} elseif($_GET['v']=="del") {
	$lock=null; //-- Define variable so PHP is happy
	if(@isset($_POST['id'])) { //-- Check if id post variable is set
		$threads=$sqldb->query("SELECT * FROM ".$sql['table']." WHERE id='".$sqldb->real_escape_string(preg_replace('/\D/', '', $_POST['id']))."' ORDER BY id LIMIT 1"); //-- Get data from database
		if(!$threads->num_rows) { //-- If thread doesn't exist just go straight back to the index
			header('Location: ./');
		}
		while($row=$threads->fetch_array(MYSQLI_ASSOC)) {
			if($row['locked']) {
				print('<h2>'.L_LOCKEDMSG_2.'</h2><meta http-equiv="refresh" content="2; URL="'.$_SERVER['PHP_SELF'].'?v=index" />');
			} else {
				if($row['password']==md5($_POST['password'])){
					delPost($row['id'],true);
					print('<h2>'.L_DEL_SUCCEED.'</h2><meta http-equiv="refresh" content="2; URL="'.$_SERVER['PHP_SELF'].'?v=index" />');
				} else {
					print('<h2>'.L_DEL_FAILED.'</h2><meta http-equiv="refresh" content="2; URL="'.$_SERVER['PHP_SELF'].'?v=del&id='.$row['id'].'" />');
				}
			}
		}
	} elseif(isset($_GET['id'])){ //-- Check if ?id= is set, if yes go into reply mode
		print('<form method="post" action="'.$_SERVER['PHP_SELF'].'?v=del"><table id="postForm" class="postForm"><tbody>');
			if(!is_numeric($_GET['id'])) {
			header('Location: ./'); //-- Redirect to index if ?id= isn't numeric
		}
		$threads=$sqldb->query("SELECT * FROM ".$sql['table']." WHERE id='".$sqldb->real_escape_string(preg_replace('/\D/', '', $_GET['id']))."' ORDER BY id"); //-- Get data from database
		if(!$threads->num_rows) { //-- If thread doesn't exist just go straight back to the index
			header('Location: ./');
		}
		while($row=$threads->fetch_array(MYSQLI_ASSOC)) { //-- Check if thread isn't locked
			if(isset($_GET['id'])){
				$lock=$row['locked'];
			}
			if($lock){ //-- Display message if thread is locked
				print('<h2>'.L_LOCKEDMSG_2.'</h2><meta http-equiv="refresh" content="2; URL="'.$_SERVER['PHP_SELF'].'?v=index" />');
			} else { //-- If not display thread based header stuff
				print('<h2>'.L_DELPOST.' '.$row['id'].'</h2><input type="hidden" name="id" value="'.$_GET['id'].'" />');
			}
		}
		if(!$lock){ //-- Only display post page if thread isn't locked
			print('<tr><td>'.L_PASSWORD.'</td><td><input name="password" type="password" placeholder="'.L_PASSWORDCONTEXT.'" value="'.@$_COOKIE[$ninechan['cookieprefix'].'pass'].'" /> <input value="'.L_SUBMIT.'" type="submit" /></td></tr>');
			print('</tbody></table></form>');
		}
	} else {
		header('Location: ./'); //-- Redirect to index if ?id= isn't numeric
	}
} elseif($_GET['v']=="mod") {
	if($auth==$ninechan['modpass']){ //-- Check if authenticated as a moderator
		if(isset($_POST['modkill'])){ //-- Kill moderator session if request is given
			session_destroy();
			header('Location: ?v=mod');
		}
		print('<h2>'.L_MODLOGOUT.'</h2><form method="post" action="'.$_SERVER['PHP_SELF'].'?v=mod">'.L_MODTOOLS.'<br /><input type="submit" value="'.L_LOGOUT.'" name="modkill" /></form>'); //-- Display logout form when logged in
		if((isset($_GET['ban']))&&(isset($_GET['id']))&&(isset($_GET['t']))){ //-- Ban handler
			if($_GET['ban']=="true"){
				banPost($_GET['id'],true);
			}else{
				banPost($_GET['id'],false);
			}
			header('Location: ?v=thread&t='.$_GET['t']);
		}
		if((isset($_GET['del']))&&(isset($_GET['id']))){ //-- Deletion handler
			if($_GET['del']=="purge"){
				pruneThread($_GET['id'],true);
				header('Location: ?v=index');
			}else{
				if($_GET['del']=="true"){
					delPost($_GET['id'],true);
				}else{
					delPost($_GET['id'],false);
				}
				header('Location: ?v=thread&t='.$_GET['t']);
			}
		}
		if((isset($_GET['lock']))&&(isset($_GET['id']))){ //-- Lock handler
			if($_GET['lock']=="true"){
				lockThread($_GET['id'],true);
			}else{
				lockThread($_GET['id'],false);
			}
			header('Location: ?v=thread&t='.$_GET['id']);
		}
	} else { //-- Else display login screen
		if(isset($_POST['modpass'])){
			if($_POST['modpass']==$ninechan['modpass']){
				$_SESSION['mod']=$ninechan['modpass'];
			}
			header('Location: ?v=mod');
		}
		print('<h2>'.L_MODLOGIN.'</h2><form method="post" action="'.$_SERVER['PHP_SELF'].'?v=mod"><input type="password" name="modpass" /><input type="submit" value="'.L_LOGIN.'" /></form>');
	}
} else { //-- Redirect to index if no ?v= attribute is given
	header('Location: ?v=index');
}
if($ninechan['styleenable']){ //-- List available styles
	print("<h6>");
	foreach($ninechan['styles'] as $style){
		print('[<a href="javascript:;" onclick="setStyle(\''.str_replace('.css', '', $style).'\');">'.str_replace('.css', '', $style).'</a>] ');
	}
	print("</h6>");
}
?>
<!-- Please retain the full copyright notice below including the link to flashii.net. This not only gives respect to the amount of time given freely by the developer but also helps build interest, traffic and use of ninechan. -->
<h6><a href="http://nine.flashii.net/" target="_blank">ninechan</a> <?php if($ninechan['showversion']){print("v1.9 ");} ?>&copy; <a href="http://flashii.net/" target="_blank">Flashwave</a></h6>
</body>
</html>
