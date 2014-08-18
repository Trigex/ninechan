<!DOCTYPE html>
<?php
// Ninechan v10.1
// This is the last feature update for ninechan
// Any updates after this will be for security (but only if urgent)
// A Ninechan v2.0 converter (for your config and database) will be available as soon as it's ready
// Thanks for using/coping with my (shitty) board script :)

// Configuration files
require 'config.php'; // Include Configuration
include 'lang/'.$ninechan['lang'].'.php'; // Include Language file

// Error Reporting
error_reporting($ninechan['exposeerrors'] ? -1 : 0);

// Check dependencies
if(version_compare(phpversion(), '5.3.0', '<')) { // PHP 5.3 or higher
	print L_PHP_OUTDATED;
	exit;
}
if(!extension_loaded('mysqli')) { // MySQL Improved
	print L_SQL_FUNCTION;
	exit;
}
if(file_exists("updatedb.php")) { // Ninechan Updater
	print L_UDB_EXISTS;
	exit;
}

// Connect to SQL
$sqldb = new mysqli($sql['host'], $sql['user'], $sql['pass'], $sql['data']);

if($sqldb->connect_errno) { // Catch connection error
	print L_SQL_CONNECT;
	exit;
}
// Initialise Database
$sqldb->query("CREATE TABLE IF NOT EXISTS `".$sql['data']."`.`".$sql['table']."` (`id` int(11) NOT NULL AUTO_INCREMENT,`title` text NOT NULL,`name` text NOT NULL,`trip` text NOT NULL,`email` text NOT NULL,`date` text NOT NULL,`content` text NOT NULL,`password` text NOT NULL,`ip` text NOT NULL,`op` int(11) NOT NULL,`tid` int(11) NOT NULL,`locked` int(11) NOT NULL,`ban` int(11) NOT NULL,`del` int(11) NOT NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=latin1;"); //-- Create database table when it doesn't exist

// Functions
// Cleaning posts
function removeSpecialChars($data) {
	$data = htmlentities($data, ENT_QUOTES | ENT_IGNORE, "UTF-8");
	$data = stripslashes($data);
	return $data;
}

// Parsing tripcodes
function parseTrip($name) {
	if(preg_match("/(#|!)(.*)/", $name, $matches)) {
		$cap = $matches[2];
		$cap = mb_convert_encoding($cap, 'SJIS', 'UTF-8');
		$cap = str_replace('#', '', $cap);
		$cap = str_replace('&', '&amp;', $cap);
		$cap = str_replace('"', '&quot;', $cap);
		$cap = str_replace("'", '&#39;', $cap);
		$cap = str_replace('<', '&lt;', $cap);
		$cap = str_replace('>', '&gt;', $cap);
		$salt = substr($cap.'H.',1,2);
		$salt = preg_replace('/[^.\/0-9:;<=>?@A-Z\[\\\]\^_`a-z]/', '.', $salt);
		$salt = strtr($salt, ':;<=>?@[\]^_`', 'ABCDEFGabcdef');
		$trip = substr(crypt($cap, $salt), -10);
		return $trip;
	}
}

// Parsing BBcodes
function parseBBcode($content){
	$bbcodecatch	= array('/\[b\](.*?)\[\/b\]/is', '/\[i\](.*?)\[\/i\]/is', '/\[u\](.*?)\[\/u\]/is', '/\[url\=(.*?)\](.*?)\[\/url\]/is', '/\[url\](.*?)\[\/url\]/is', '/\[spoiler\](.*?)\[\/spoiler\]/is', '/&gt;&gt;(.*[0-9])/i', '/^&gt;(.*?)$/im','/^.*(youtu.be|youtube.com\/embed\/|watch\?v=|\&v=)([^!<>@&#\/\s]*)/is');
	$bbcodereplace	= array('<b>$1</b>', '<i>$1</i>', '<u>$1</u>', '<a href="$1" rel="nofollow" title="$2 - $1">$2</a>', '<a href="$1" rel="nofollow" title="$1">$1</a>', '<span class="spoiler">$1</span>', '<a class="lquote" href="#$1">&gt;&gt;$1</a>', '<span class="quote">&gt;$1</span>', '<object type="application/x-shockwave-flash" style="width:425px; height:350px;" data="http://www.youtube.com/v/$2"><param name="movie" value="http://www.youtube.com/v/$2" /></object>');
	$content		= preg_replace($bbcodecatch, $bbcodereplace, $content);
	return nl2br($content);
}

// Generating Random Password
function generatePassword() {
	$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_@#$!*\/[]{}=+';
	for ($i = 0, $pass = ''; $i < 34; $i++) {
		$index = rand(0, mb_strlen($chars) - 1);
		$pass .= mb_substr($chars, $index, 1);
	}
	return $pass;
}

// Banning a post
function banPost($id, $ban) {
	global $sql, $sqldb;
	
	if($ban)
		$sqldb->query("UPDATE `".$sql['data']."`.`".$sql['table']."` SET `ban`=1 WHERE `id`=".$id);
	else
		$sqldb->query("UPDATE `".$sql['data']."`.`".$sql['table']."` SET `ban`=0 WHERE `id`=".$id);
}

// Removing a post
function delPost($id, $del) {
	global $sql, $sqldb;
	
	if($del)
		$sqldb->query("UPDATE `".$sql['data']."`.`".$sql['table']."` SET `del`=1 WHERE `id`=".$id);
	else
		$sqldb->query("UPDATE `".$sql['data']."`.`".$sql['table']."` SET `del`=0 WHERE `id`=".$id);
}
// Removing every post in the thread
function pruneThread($id, $prune) {
	global $sql, $sqldb;
	
	if($prune)
		$sqldb->query("UPDATE `".$sql['data']."`.`".$sql['table']."` SET `del`=1 WHERE `tid`=".$id);
	else
		$sqldb->query("UPDATE `".$sql['data']."`.`".$sql['table']."` SET `del`=0 WHERE `tid`=".$id);
}
// Locking a thread
function lockThread($id, $lock) {
	global $sql, $sqldb;
	
	if($lock)
		$sqldb->query("UPDATE `".$sql['data']."`.`".$sql['table']."` SET `locked`=1 WHERE `tid`=".$id);
	else
		$sqldb->query("UPDATE `".$sql['data']."`.`".$sql['table']."` SET `locked`=0 WHERE `tid`=".$id);
}

// reCAPTCHA
if($ninechan['recaptcha'])
	require $ninechan['recaptchalib'];

// Session
session_start();			// Start a session
$auth = @$_SESSION['mod'];	// Set an alias for mod
?>
<html>
	<head>
		<meta http-equiv="Content-type" content="text/html; charset=<?=$ninechan['charset'];?>" />
		<title><?=$ninechan['title'];?></title>
		<?php
		if($ninechan['desc'])
			print '<meta name="description" content="'.$ninechan['desc'].'" />'."\r\n";
		?>
		<script type="text/javascript">
		/// Apologies for my shitty Javascript
		// Function to write to a cookie
		function setCookie(name, content, expire) {
			if(expire=="forever"){var expire = 60*60*24*365*99;}
			if(expire=="default"){var expire = 60*60*24*7;}
			document.cookie='<?php print($ninechan['cookieprefix']); ?>'+name+'='+content+';max-age='+expire;
		}
		
		// Function to delete a cookie
		function delCookie(name) {
			document.cookie='<?php print($ninechan['cookieprefix']); ?>'+name+'=;max-age=1;path=/'
		}
		
		// Function to get data from a cookie
		function getCookie(name) {
			return (name = new RegExp('(?:^|;\\s*)' + ('' + '<?php print($ninechan['cookieprefix']); ?>'+name).replace(/[-[\]{}()*+?.,\\^$|#\s]/g, '\\$&') + '=([^;]*)').exec(document.cookie)) && name[1];
		}
		
		// Get main style
		function getMainStyle() {
			var i,a;
			for(i=0; (a = document.getElementsByTagName('link')[i]); i++) {
				if(a.getAttribute('rel').indexOf('style') != -1 && a.getAttribute('rel').indexOf('alt') == -1 && a.getAttribute('title')) {
					return a.getAttribute('title');
				}
			}
			return null;
		}
		
		// Get the currently active style
		function getActiveStyle() {
			var i, a;
			for(i=0; (a = document.getElementsByTagName('link')[i]); i++) {
				if(a.getAttribute('rel').indexOf('style') != -1 && a.getAttribute('title') && !a.disabled) {
					return a.getAttribute('title');
				}
			}
			return null;
		}
		
		// Switch to another style
		function setStyle(title) {
			var i, a, main;
			var titleFound = false;
			setCookie('style', title, 'forever');
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
		
		// Initiate Frontend Javascript Data
		function init() {
			if(getCookie('style') == null)
				setStyle(getMainStyle());
			else
				setStyle(getCookie('style'));
		}
		</script>
		<?php
		if($ninechan['styles']) { // Check if styles are enabled
			foreach($ninechan['styles'] as $styleUrl => $styleName){		// Get styles from array
				reset($ninechan['styles']);									// Reset Array
				$mainStyle = key($ninechan['styles']);						// Get first entry
				$alternate = ($styleUrl == $mainStyle) ? '' : 'alternate ';	// Append alternate to the rel of the non-main styles
				print '<link rel="'.$alternate.'stylesheet" type="text/css" href="'.$styleUrl.'" title="'.$styleName.'" />'."\r\n"; // List every style
			}
		}
		?>
	</head>
	<body onload="init();">
		<h1><a href="./"><?=$ninechan['title'];?></a></h1>
		<?=($ninechan['desc'] ? '&nbsp;<i>'.$ninechan['desc'].'</i>' : null);?>
		<hr />
		<?php
		if($ninechan['closed']) { // Exit if board is set as closed in the config file
			print L_BOARD_CLOSED."<br /><i>".L_REASON.": ".$ninechan['closedreason']."</i>";
			exit;
		}
		$bancheck = $sqldb->query("SELECT * FROM ".$sql['table']." WHERE ip='".base64_encode($_SERVER['REMOTE_ADDR'])."'"); // Check if poster IP is banned
		while($row = $bancheck->fetch_array(MYSQLI_ASSOC)){if($row['ban']){die(L_BANNED);}}
		if(!isset($_COOKIE[$ninechan['cookieprefix'].'pass'])) { // Check if pass cookie is set if not set it
			setcookie($ninechan['cookieprefix']."pass",generatePassword(),time()+604800,"/",$_SERVER['SERVER_NAME']); // Generate random password
		}
		if(isset($_GET['v'])) {
			switch($_GET['v']) {
				// Main index
				case 'index':
					print '<h2>'.L_THREADS.'</h2>';	// Section title
					print '<h3><a href="?v=post">'.L_NEWTHREAD.'</a></h3>'; // New thread link
					
					// Query to get OP posts
					$getThreads = $sqldb->query("SELECT * FROM `".$sql['data']."`.`".$sql['table']."` WHERE `del`='0' AND `op`='1' ORDER BY `date` DESC".($ninechan['sage'] ? " LIMIT ".$ninechan['sagelimit'] : null));
					
					// List posts
					if(!$getThreads->num_rows) {		// Check if there's more than 1 post
						print '<h3>'.L_EMPTY.'</h3>';	// Return L_EMPTY otherwise
					} else {
						print '<ol>';
						
						while($thread = $getThreads->fetch_array(MYSQLI_ASSOC)) {
							print '<li><a href="?v=thread&t='.$thread['tid'].'">'.$thread['title'].'</a>';
						}
						
						print '</ol>';
					}
					
					print '<h3><a href="?v=post">'.L_NEWTHREAD.'</a></h3>'; // New thread link
				break;
				
				// Thread view
				case 'thread':
					if(!isset($_GET['t']) || !is_numeric($_GET['t'])) { // Just return L_NONEXISTENT if t is invalid
						print L_NONEXISTENT;
						break;
					}
					
					$getThread = $sqldb->query("SELECT * FROM `".$sql['data']."`.`".$sql['table']."` WHERE `tid`='".$sqldb->real_escape_string(preg_replace('/\D/', '', $_GET['t']))."' AND `del`='0' ORDER BY `id`");
					
					if(!$getThread->num_rows) {	// Check if requested thread exists
						print L_NONEXISTENT;	// If not return L_NONEXISTENT
						break;
					} else {
						$threadData = array(); // Assign array to variable so we can store things in it later
						
						while($post = $getThread->fetch_array(MYSQLI_ASSOC)) {
							$postData = null; // Make sure $postData isn't set
							$postData = array(); // Then apply an array
							
							if($post['op']) { // Assign thread variables
								$threadData['id']	= $post['tid'];
								$threadData['lock']	= $post['locked'];
								
								print '<h2>'.L_THREAD.': '.$post['title'].'</h2>'; // Print L_THREAD and the name of the thread
								
								if($threadData['lock']) // Check if thread is locked and if true display message
									print '<h3>'.L_LOCKED.'</h3>';
								else // otherwise print reply button
									print '<h3><a href=?v=post&t='.$post['tid'].'>'.L_NEWREPLY.'</a></h3>';
								
								// Mod tools
								if($auth == $ninechan['modpass']) {
									print '<font size="2">[<a href=?v=mod&del=purge&id='.$threadData['id'].'>'.L_PURGE.'</a>]';
									if($threadData['lock']) {
										print ' [<a href="?v=mod&lock=false&id='.$threadData['id'].'">'.L_UNLOCK.'</a>]</font>';
									} else {
										print ' [<a href="?v=mod&lock=true&id='.$threadData['id'].'">'.L_LOCK.'</a>]</font>';
									}
								}
							}
							
							// Assign post variables
							$postData['name']	= null;
							$postData['trip']	= null;
							$postData['del']	= null;
							
							// Didn't feel like redoing this part, sorry [
							if($ninechan['forcedanon']){
								$postData['name'] = $ninechan['anonname'];
								$postData['trip'] = null;
							} elseif($ninechan['modsareanon']==1&&in_array($row['trip'],$ninechan['modtrip'])){ //-- Check if forced anon for mods is enabled
								$postData['name'] = $ninechan['anonname'];
								$postData['trip'] = null;
							} elseif($ninechan['modsareanon']==2&&in_array($row['trip'],$ninechan['modtrip'])){ //-- Check if forced trip anon for mods is enabled
								$postData['name'] = $post['name'];
								$postData['trip'] = null;
							} elseif($ninechan['adminsareanon']==1&&in_array($row['trip'],$ninechan['admintrip'])){ //-- Check if forced anon for admins is enabled
								$postData['name'] = $ninechan['anonname'];
								$postData['trip'] = null;
							} elseif($ninechan['adminsareanon']==2&&in_array($row['trip'],$ninechan['admintrip'])){ //-- Check if forced trip anon for admins is enabled
								$postData['name'] = $post['name'];
								$postData['trip'] = null;
							} else {
								if(empty($post['name'])){
									$postData['name'] = $ninechan['anonname'];
								} else {
									$postData['name'] = $post['name'];
								}
								if(!empty($post['trip'])){
									$postData['trip'] = ' <span class="trip">!'.$post['trip'].'</span>';
								} else {
									$postData['trip'] = null;
								}
							}
							// ]
							
							print '<fieldset id="'.$post['id'].'">';
							print '<legend><b>'.$post['title'].'</b> <a href="#'.$post['id'].'">'.L_BY.'</a> <b>';
							
							if(empty($post['email']))
								print '<a href="mailto:'.$post['email'].'">'.$postData['name'].$postData['trip'].'</a>';
							else
								print $postData['name'].$postData['trip'];
							
							if(in_array($post['trip'], $ninechan['admintrip'])) // Check if tripcode is Admin
								print ' <span class="admincap">## Admin</span>';
							elseif(in_array($post['trip'], $ninechan['modtrip'])) // Check if tripcode is Mod
								print ' <span class="modcap">## Mod</span>';
						
							print '</b></legend>';
							
							print parseBBcode($post['content']); // Parse BBcodes on post content
							print '<br /><br />';
							
							print ($post['ban'] ? '<b><font size="2" class="ban">'.L_POSTBANNED.'</font></b><br />' : null);
							
								
							if($auth == $ninechan['modpass']) {
								print '<font size=2>[<a href="?v=mod&del=true&id='.$post['id'].'&t='.$post['tid'].'">'.L_DELETE.'</a>] [<a href="?v=mod&ban='.($post['ban'] ? 'false' : 'true').'&id='.$post['id'].'&t='.$post['tid'].'">'.($post['ban'] ? L_UNBAN : L_BAN).'</a>] [IP: '.base64_decode($post['ip']).']</font><br />'; //-- Regular mod tools
							}
							
							print '<font size=2><i>'.date($ninechan['dateFormat'], $post['date']).' <a href="#'.$post['id'].'">No.</a> <a href="?v=post&t='.$post['tid'].'&text=>>'.$post['id'].'">'.$post['id'].'</a> [<a href="?v=del&id='.$post['id'].'" title="'.L_DELPOST.'">X</a>]</i></font>';
							
							print '</fieldset>';
						}
						
						// Mod tools
						if($auth == $ninechan['modpass']) {
							print '<font size="2">[<a href=?v=mod&del=purge&id='.$threadData['id'].'>'.L_PURGE.'</a>]';
							if($threadData['lock']) {
								print ' [<a href="?v=mod&lock=false&id='.$threadData['id'].'">'.L_UNLOCK.'</a>]</font>';
							} else {
								print ' [<a href="?v=mod&lock=true&id='.$threadData['id'].'">'.L_LOCK.'</a>]</font>';
							}
						}
						
						if($threadData['lock']) // Check if thread is locked and if true display message
							print '<h3>'.L_LOCKED.'</h3>';
						else // otherwise print reply button
							print '<h3><a href=?v=post&t='.$threadData['id'].'>'.L_NEWREPLY.'</a></h3>';
					}
				break;
				
				// Posting
				case 'post':
					$postData = array(); // Assign array to variable so we can store things in it later
					
					print '<form method="post" action="?v=submit">';					
					print '<table id="postForm" class="postForm">';
					
					if(isset($_GET['t'])) {
						if(!is_numeric($_GET['t'])) {
							header('Location: ./');
							print '<meta http-equiv="refresh" content="0; url=./" />'; // fallback
						}
						
						$getData = $sqldb->query("SELECT * FROM `".$sql['data']."`.`".$sql['table']."` WHERE `tid`='".$sqldb->real_escape_string(preg_replace('/\D/', '', $_GET['t']))."' and op='1' ORDER BY `id` LIMIT 1");
						
						while($data = $getData->fetch_array(MYSQLI_ASSOC)) {
							$postData['lock'] = $data['locked'];
							
							if($postData['lock']) {
								print '<h2>'.L_LOCKEDMSG.'</h2>';
								print '<meta http-equiv="refresh" content="2; URL="./?v=thread&t='.$data['tid'].'" />';
							} else {
								print '<h2>'.L_RETO.' '.$data['title'].' ['.$data['tid'].']</h2>';
								print '<input type="hidden" name="tid" value="'.$_GET['t'].'" />';
								$postData['title'] = 'Re: '.$data['title'];
							}
						}
					} else {
						print '<h2>'.L_NEWTHREAD.'</h2>';
						$postData['title'] = null;
						$postData['lock'] = false;
					}
					
					if(isset($_GET['text'])) {
						$postData['text'] = $_GET['text']."\r\n";
					} else {
						$postData['text'] = null;
					}
					
					if(!$postData['lock']) { //-- Only display post page if thread isn't locked
						print('<tr><td>'.L_NAME.'</td><td><input name="name" type="text" value="'.@$_COOKIE[$ninechan['cookieprefix'].'name'].'" /></td></tr>');
						print('<tr><td>'.L_EMAIL.'</td><td><input name="email" type="text" value="'.@$_COOKIE[$ninechan['cookieprefix'].'email'].'" /></td></tr>');
						print('<tr><td>'.L_TITLE.'</td><td><input name="title" type="text" value="'.$postData['title'].'" /></td></tr>');
						print('<tr><td>'.L_COMMENT.'</td><td><textarea name="content" rows="6" cols="48">'.$postData['text'].'</textarea></td></tr>');
						if($ninechan['recaptcha']){ //-- Display reCAPTCHA if enabled in config
							print('<tr><td>'.L_VERIFICATION.'</td><td>'.recaptcha_get_html($ninechan['recaptchapublic']).'</td></tr>');
						}
						print('<tr><td>'.L_PASSWORD.'</td><td><input name="password" type="password" placeholder="'.L_PASSWORDCONTEXT.'" value="'.@$_COOKIE[$ninechan['cookieprefix'].'pass'].'" /> <input value="'.L_SUBMIT.'" type="submit" /></td></tr>');
						print('</table></form>');
					}
				break;
				
				// Submitting posts
				case 'submit':
					$submitData = array(); // Assign array to variable so we can store things in it later
					
					// Check ReCAPTCHA
					if($ninechan['recaptcha'])	{
						$recaptcha = recaptcha_check_answer($ninechan['recaptchaprivate'], $_SERVER['REMOTE_ADDR'], $_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field']); // ReCAPTCHA data
						
						if(!$recaptcha->is_valid) { // If ReCAPTCHA is invalid die and display error message
							print '<h2>'.L_INVALIDCAPTCHA.'</h2><meta http-equiv="refresh" content="2; URL='.$_SERVER['PHP_SELF'].'" />';
							break;
						}
					}
					
					// Assign variables
					$submitData['title']	= removeSpecialChars($_POST['title']);
					$submitData['content']	= removeSpecialChars($_POST['content']);
					$submitData['name']		= removeSpecialChars($_POST['name']);
					$submitData['nameNT']	= strstr($submitData['name'], "#", true);
					$submitData['trip']		= parseTrip($_POST['name']);
					$submitData['email']	= ($_POST['email'] == 'noko' ? null : removeSpecialChars($_POST['email']));
					$submitData['date']		= time();
					$submitData['password']	= md5(strlen($_POST['password']) ? $_POST['password'] : generatePassword());
					$submitData['ip']		= base64_encode($_SERVER['REMOTE_ADDR']);
					$submitData['op']		= (isset($_POST['tid']) ? 0 : 1);
					$submitData['id']		= ($submitData['op'] ? ($sqldb->query("SELECT MAX(tid) AS tid FROM ".$sql['table']." LIMIT 1")->fetch_array(MYSQLI_ASSOC)['tid'] + 1) : removeSpecialChars($_POST['tid']));
					$submitData['noredir']	= ($submitData['email'] == 'noko' ? true : false);

					// Assign cookies
					setcookie($ninechan['cookieprefix']."name", $submitData['name'], time()+604800, $ninechan['cookiepath'], $_SERVER['SERVER_NAME']);
					setcookie($ninechan['cookieprefix']."email", $submitData['email'], time()+604800, $ninechan['cookiepath'], $_SERVER['SERVER_NAME']); 
					setcookie($ninechan['cookieprefix']."pass", $submitData['password'], time()+604800, $ninechan['cookiepath'], $_SERVER['SERVER_NAME']); 
					
					// Check if title is valid
					if(strlen($submitData['title']) <= $ninechan['titleminlength']) { // Check if too short
						print '<h2>'.L_TITLETOOSHORT.'</h2>';
						print '<meta http-equiv="refresh" content="2; URL='.$_SERVER['PHP_SELF'].'" />';
						break;
					}
					if(strlen($submitData['title']) >= $ninechan['titlemaxlength']) { // Check if too long
						print '<h2>'.L_TITLETOOLONG.'</h2>';
						print '<meta http-equiv="refresh" content="2; URL='.$_SERVER['PHP_SELF'].'" />';
						break;
					}
					
					// Check if comment is valid
					if(strlen($submitData['content']) <= $ninechan['commentminlength']) { // Check if too short
						print '<h2>'.L_COMMENTTOOSHORT.'</h2>';
						print '<meta http-equiv="refresh" content="2; URL='.$_SERVER['PHP_SELF'].'" />';
						break;
					}
					if(strlen($submitData['content']) >= $ninechan['commentmaxlength']) { // Check if too long
						print '<h2>'.L_COMMENTTOOLONG.'</h2>';
						print '<meta http-equiv="refresh" content="2; URL='.$_SERVER['PHP_SELF'].'" />';
						break;
					}
					
					$sqldb->query("INSERT INTO `".$sql['data']."`.`".$sql['table']."` (`title`,`name`,`trip`,`email`,`date`,`content`,`password`,`ip`,`op`,`tid`) VALUES ('".$submitData['title']."','".$submitData['nameNT']."','".$submitData['trip']."','".$submitData['email']."','".$submitData['date']."','".$submitData['content']."','".$submitData['password']."','".$submitData['ip']."','".$submitData['op']."','".$submitData['id']."')");
					
					print '<h1>'.L_POSTED.'</h1>';
					
					print '<meta http-equiv="refresh" content="1; URL='.($submitData['noredir'] ? '?v=index' : '?v=thread&t='.$submitData['id']).'" />';
				break;
				
				case 'del':
					$deletionData = array(); // Assign array to variable so we can store things in it later
					
					if(isset($_POST['id'])) {
						$getData = $sqldb->query("SELECT * FROM `".$sql['data']."`.`".$sql['table']."` WHERE `id`='".$sqldb->real_escape_string(preg_replace('/\D/', '', $_POST['id']))."' ORDER BY `id` LIMIT 1");
					
						if(!$getData->num_rows) {
							header('Location: ./');
							print '<meta http-equiv="refresh" content="0; url=./" />'; // fallback
						}
						
						while($del = $threads->fetch_array(MYSQLI_ASSOC)) {
							if($del['locked']) {
								print('<h2>'.L_LOCKEDMSG_2.'</h2><meta http-equiv="refresh" content="2; URL="?v=index" />');
							} else {
								if($del['password'] == md5($_POST['password'])){
									delPost($del['id'], true);
									print('<h2>'.L_DEL_SUCCEED.'</h2><meta http-equiv="refresh" content="2; URL="?v=index" />');
								} else {
									print('<h2>'.L_DEL_FAILED.'</h2><meta http-equiv="refresh" content="2; URL="?v=del&id='.$del['id'].'" />');
								}
							}
						}
					} elseif(isset($_GET['id'])) {
						if(!is_numeric($_GET['id'])) {
							header('Location: ./');
							print '<meta http-equiv="refresh" content="0; url=./" />'; // fallback
						}
						
						$getData = $sqldb->query("SELECT * FROM `".$sql['data']."`.`".$sql['table']."` WHERE `id`='".$sqldb->real_escape_string(preg_replace('/\D/', '', $_POST['id']))."' ORDER BY `id` LIMIT 1");

						if(!$getData->num_rows) {
							header('Location: ./');
							print '<meta http-equiv="refresh" content="0; url=./" />'; // fallback
						}
						
						print '<form method="post" action="?v=del">';
						
						while($row=$threads->fetch_array(MYSQLI_ASSOC)) { //-- Check if thread isn't locked
							if(isset($_GET['id'])) {
								$deletionData['lock'] = $row['locked'];
							}
							if($deletionData['lock']) {
								print '<h2>'.L_LOCKEDMSG_2.'</h2><meta http-equiv="refresh" content="2; URL="?v=index" />';
								break;
							} else {
								print('<h2>'.L_DELPOST.' '.$row['id'].'</h2><input type="hidden" name="id" value="'.$_GET['id'].'" />');
							}
						}
												
						print '<table id="postForm" class="postForm">';
						
						print '<tr><td>'.L_PASSWORD.'</td><td><input name="password" type="password" placeholder="'.L_PASSWORDCONTEXT.'" value="'.@$_COOKIE[$ninechan['cookieprefix'].'pass'].'" /> <input value="'.L_SUBMIT.'" type="submit" /></td></tr>';
						
						print '</table>';
						print '</form>';
					}
				break;
				
				// Moderator Authentication
				case 'mod':
					if($auth == $ninechan['modpass']) { // Check if authenticated
						if(isset($_POST['modkill'])) { // POST request modkill is set...
							session_destroy(); // ...kill moderator session...
							header('Location: ?v=mod'); // ...and redirect to ?v=mod
							print '<meta http-equiv="refresh" content="0; url=?v=mod" />'; // fallback
						}
						print '<h2>'.L_MODLOGOUT.'</h2>'; // Page title
						
						print '<form method="post" action="'.$_SERVER['PHP_SELF'].'?v=mod">'; // Print logout form
						print L_MODTOOLS.'<br />';
						print '<input type="submit" value="'.L_LOGOUT.'" name="modkill" />';
						print '</form>';
						
						if(isset($_GET['ban'])&&(isset($_GET['id']))&&(isset($_GET['t']))) { // Ban handler
							if($_GET['ban']=="true"){
								banPost($_GET['id'],true);
							} else {
								banPost($_GET['id'],false);
							}
							header('Location: ?v=thread&t='.$_GET['t']);
							print '<meta http-equiv="refresh" content="0; url=?v=thread&t='.$_GET['t'].'" />'; // fallback
						}
						if((isset($_GET['del']))&&(isset($_GET['id']))) { // Deletion handler
							if($_GET['del']=="purge"){
								pruneThread($_GET['id'],true);
								header('Location: ?v=index');
								print '<meta http-equiv="refresh" content="0; url=?v=index" />'; // fallback
							} else {
								if($_GET['del']=="true"){
									delPost($_GET['id'],true);
								}else{
									delPost($_GET['id'],false);
								}
								header('Location: ?v=thread&t='.$_GET['t']);
								print '<meta http-equiv="refresh" content="0; url=?v=thread&t='.$_GET['t'].'" />'; // fallback
							}
						}
						if((isset($_GET['lock']))&&(isset($_GET['id']))){ // Lock handler
							if($_GET['lock']=="true"){
								lockThread($_GET['id'],true);
							}else{
								lockThread($_GET['id'],false);
							}
							header('Location: ?v=thread&t='.$_GET['id']);
							print '<meta http-equiv="refresh" content="0; url=?v=thread&t='.$_GET['id'].'" />'; // fallback
						}
					} else { // Else display login screen
						if(isset($_POST['modpass'])){
							if($_POST['modpass']==$ninechan['modpass']){
								$_SESSION['mod']=$ninechan['modpass'];
							}
							header('Location: ?v=mod');
						}
						print '<h2>'.L_MODLOGIN.'</h2>';
						print '<form method="post" action="'.$_SERVER['PHP_SELF'].'?v=mod">';
						print '<input type="password" name="modpass" /><input type="submit" value="'.L_LOGIN.'" />';
						print '</form>';
					}
				break;
				
				// Default action
				default:
					header('Location: ?v=index'); // If invalid option is set redirect to index
					print '<meta http-equiv="refresh" content="0; url=?v=index" />'; // Fallback because I've had experiences where header() didn't work properly
				break;
			}
		} else {
			header('Location: ?v=index'); // If invalid option is set redirect to index
			print '<meta http-equiv="refresh" content="0; url=?v=index" />'; // Fallback because I've had experiences where header() didn't work properly
		}
		if($ninechan['styles']) { // Check if styles are enabled
			print '<h6>';
			foreach($ninechan['styles'] as $styleUrl => $styleName){ // Get styles from array
				print '[<a href="javascript:;" onclick="setStyle(\''.$styleName.'\');">'.$styleName.'</a>] '; // List every style
			}
			print '</h6>';
		}
		?>
		<!-- Please retain the full copyright notice below including the link to flashii.net. This not only gives respect to the amount of time given freely by the developer but also helps build interest, traffic and use of ninechan. -->
		<h6><a href="http://nine.flashii.net/" target="_blank">ninechan</a> <?=($ninechan['showversion'] ? '1.10 ' : null);?>&copy; <a href="http://flashii.net/" target="_blank">Flashwave</a></h6>
	</body>
</html>
