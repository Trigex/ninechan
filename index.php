<?php
require('config.php');
$connection=mysql_connect($mysql['host'], $mysql['user'], $mysql['pass']);
if (!($connection)){die("SQL Connection Error.");}
$databasetest=mysql_select_db($mysql['data']);
if(!($databasetest)){die("Database Error.");}
$dbinit=mysql_query("CREATE TABLE IF NOT EXISTS `posts` (`id` int(11) NOT NULL AUTO_INCREMENT,`title` text NOT NULL,`name` text NOT NULL,`email` text NOT NULL,`date` text NOT NULL,`content` text NOT NULL,`ip` text NOT NULL,`op` int(11) NOT NULL,`tid` int(11) NOT NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=13 ;")
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
if($ninechan['closed']){die("The ".$ninechan['title']." boards are closed right now.");}
if($_GET['v']=="index") {
	print("<h2>Threads</h2>");
	print("<h3><a href=?v=post>New Thread</a></h3>");
	print("<ul>");
	$threads=mysql_query("SELECT * FROM posts WHERE op='1' ORDER BY id DESC");
	$num_rows=mysql_num_rows($threads);
	if(!$num_rows){print("There are no threads.");}
	while($row=mysql_fetch_array($threads)) {
		print("<li><a href=\"?v=thread&t=".$row['tid']."\">".$row['title']."</a></li>");
	}
	print("</ul>");
	print("<h3><a href=?v=post>New Thread</a></h3>");
} elseif(($_GET['v']=="thread")&&(isset($_GET['t']))) {
	if(!is_numeric($_GET['t'])){header('Location: ./');}
	$threads=mysql_query("SELECT * FROM posts WHERE tid='".mysql_real_escape_string(preg_replace('/\D/', '', $_GET['t']))."' ORDER BY id");
	$num_rows=mysql_num_rows($threads);
	if(!$num_rows){print("Non-existent thread.");}
	$tid="";
	while($row=mysql_fetch_array($threads)) {
		if($row['op']==1){print("<h2>Thread: ".$row['title']."</h2><h3><a href=?v=post&t=".$row['tid'].">New Reply</a></h3>");$tid=$row['tid'];}
		print("<fieldset id=".$row['id'].">");
		if(!$row['email']==""){print("<legend><b>".$row['title']."</b> <a href=\"#".$row['id']."\">by</a> <b><a href=\"mailto:".$row['email']."\">".$row['name']."</a></b></legend>");}else{print("<legend><b>".$row['title']."</b> <a href=\"#".$row['id']."\">by</a> <b>".$row['name']."</b></legend>");}
		print($row['content']);
		print("<br /><br /><i><font size=2>".$row['date']."</font></i></fieldset>");
	}
	print("<h3><a href=?v=post&t=".$tid.">New Reply</a></h3>");
} elseif($_GET['v']=="post") {
	print("<form method=post action=?v=submit>");
	if(isset($_GET['t'])){
		if(!is_numeric($_GET['t'])){header('Location: ./');}
		$threads=mysql_query("SELECT * FROM posts WHERE tid='".mysql_real_escape_string(preg_replace('/\D/', '', $_GET['t']))."' and op='1' ORDER BY id");
		$num_rows=mysql_num_rows($threads);
		if(!$num_rows){header('Location: ./');}
		while($row=mysql_fetch_array($threads)) {
			print("<h2>Reply to ".$row['title']." (ID: ".$row['tid'].")</h2>");
			print("<input type=hidden name=tid value='".$_GET['t']."'/>title*: <input type=text name=title value='Re: ".$row['title']."' /><br />");
		}
	}else{
		print("<h2>New Thread</h2>");
		print("Title*: <input type=text name=title /><br />");
	}
	print("Name: <input type=text name=name /><br />Email: <input type=text name=email /><br />Comment*:<br /><textarea name=content rows=6 cols=48></textarea><br /><font size=2>* = Required</font><br /><input type=submit value=Submit /></form>");
} elseif($_GET['v']=="submit") {
	if((isset($_POST['title']))&&(!$_POST['title']=="")){$title = htmlentities($_POST['title'], ENT_QUOTES | ENT_IGNORE, "UTF-8");$title = stripslashes($title);}else{die("<h2>no title entered</h2><meta http-equiv=\"refresh\" content=\"2; URL=".$_SERVER['PHP_SELF']."\">");}
	if((isset($_POST['name']))&&(!$_POST['name']=="")){$name = htmlentities($_POST['name'], ENT_QUOTES | ENT_IGNORE, "UTF-8");$name = stripslashes($name);}else{$name="Anonymous";}
	if(isset($_POST['email'])){$email = htmlentities($_POST['email'], ENT_QUOTES | ENT_IGNORE, "UTF-8");$email = stripslashes($email);}
	$date=date('d/m/Y @ g:iA T');
	if((isset($_POST['content']))&&(!$_POST['content']=="")){$content = htmlentities($_POST['content'], ENT_QUOTES | ENT_IGNORE, "UTF-8");$content = nl2br($content);$content = stripslashes($content);}else{die("<h2>no comment entered</h2><meta http-equiv=\"refresh\" content=\"2; URL=".$_SERVER['PHP_SELF']."\">");}
	$ip=base64_encode($_SERVER['REMOTE_ADDR']);
	if(!isset($_POST['tid'])){$op=1;}else{$op=0;}
	if(isset($_POST['tid'])){$tid = htmlentities($_POST['tid'], ENT_QUOTES | ENT_IGNORE, "UTF-8");$tid = stripslashes($tid);}else{$tidget=mysql_query("SELECT MAX(tid) AS tid FROM posts LIMIT 1");$num_rows=mysql_num_rows($tidget);$tid=0;while($row=mysql_fetch_array($tidget)){$tid=$row['tid'];}++$tid;}
	mysql_query("INSERT INTO `".$mysql['data']."`.`posts` (`title`,`name`,`email`,`date`,`content`,`ip`,`op`,`tid`) VALUES ('$title','$name','$email','$date','$content','$ip','$op','$tid')");
	print("<h1>Posted!</h1><meta http-equiv=\"refresh\" content=\"2; URL=".$_SERVER['PHP_SELF']."?v=thread&t=".$tid."\">");
} else {header('Location: ?v=index');}
?>
<h6>Powered by ninechan <?php if($ninechan['showversion']){print("v1.3&nbsp;");} ?>&copy; <a href="http://flashwave.pw/">Flashwave</a> 2014</h6>
</body>
</html>
