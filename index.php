<?php
$mysql=array();
$mysql['host']="localhost"; // MySQL Host
$mysql['user']="root"; // MySQL Username
$mysql['pass']=""; // MySQL Password
$mysql['data']="ninechan"; // MySQL Database Name
$connection=mysql_connect($mysql['host'], $mysql['user'], $mysql['pass']);
if (!($connection)){die("SQL Connection Error.");}
$databasetest=mysql_select_db($mysql['data']);
if(!($databasetest)){die("Database Error.");}
$dbinit=mysql_query("CREATE TABLE IF NOT EXISTS `posts` (`id` int(11) NOT NULL AUTO_INCREMENT,`title` text NOT NULL,`name` text NOT NULL,`email` text NOT NULL,`date` text NOT NULL,`content` text NOT NULL,`ip` text NOT NULL,`op` int(11) NOT NULL,`tid` int(11) NOT NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=13 ;")
?>
<html>
<head>
<title>ninechan</title>
</head>
<body>
<h1><a href="./">ninechan</a></h1><hr />
<?php
if($_GET['v']=="index") {
	print("<h2>threads</h2>");
	print("<h3><a href=?v=post>new thread</a></h3>");
	print("<ul>");
	$threads=mysql_query("SELECT * FROM posts WHERE op='1' ORDER BY id");
	$num_rows=mysql_num_rows($threads);
	if(!$num_rows){print("there are no threads.");}
	while($row=mysql_fetch_array($threads)) {
		print("<li><a href=\"?v=thread&t=".$row['tid']."\">".$row['title']."</a></li>");
	}
	print("</ul>");
	print("<h3><a href=?v=post>new thread</a></h3>");
} elseif(($_GET['v']=="thread")&&(isset($_GET['t']))) {
	if(!is_numeric($_GET['t'])){header('Location: ./');}
	$threads=mysql_query("SELECT * FROM posts WHERE tid='".mysql_real_escape_string(preg_replace('/\D/', '', $_GET['t']))."' ORDER BY id");
	$num_rows=mysql_num_rows($threads);
	if(!$num_rows){print("non-existent thread.");}
	$tid="";
	while($row=mysql_fetch_array($threads)) {
		if($row['op']==1){print("<h2>thread: ".$row['title']."</h2><h3><a href=?v=post&t=".$row['tid'].">new reply</a></h3>");$tid=$row['tid'];}
		print("<fieldset id=".$row['id'].">");
		if(!$row['email']==""){print("<legend><b>".$row['title']."</b> by <b><a href=\"mailto:".$row['email']."\">".$row['name']."</a></b></legend>");}else{print("<legend><b>".$row['title']."</b> by <b>".$row['name']."</b></legend>");}
		print($row['content']);
		print("<br /><br /><i><font size=2>".$row['date']."</font></i></fieldset>");
	}
	print("<h3><a href=?v=post&t=".$tid.">new reply</a></h3>");
} elseif(($_GET['v']=="post")) {
	print("<form method=post action=?v=submit>");
	if(isset($_GET['t'])){
		if(!is_numeric($_GET['t'])){header('Location: ./');}
		$threads=mysql_query("SELECT * FROM posts WHERE tid='".mysql_real_escape_string(preg_replace('/\D/', '', $_GET['t']))."' and op='1' ORDER BY id");
		$num_rows=mysql_num_rows($threads);
		if(!$num_rows){header('Location: ./');}
		while($row=mysql_fetch_array($threads)) {
			print("<h2>reply to ".$row['title']." (id: ".$row['tid'].")</h2>");
			print("<input type=hidden name=tid value='".$_GET['t']."'/>title*: <input type=text name=title value='re: ".$row['title']."' /><br />");
		}
	}else{
		print("<h2>new thread</h2>");
		print("title*: <input type=text name=title /><br />");
	}
	print("name: <input type=text name=name /><br />email: <input type=text name=email /><br />comment*:<br /><textarea name=content></textarea><br /><font size=2>*required</font><br /><input type=submit value=submit /></form>");
} elseif(($_GET['v']=="submit")) {
if((isset($_POST['title']))&&(!$_POST['title']=="")){$title = htmlentities($_POST['title'], ENT_QUOTES | ENT_IGNORE, "UTF-8");$title = stripslashes($title);}else{die("<h2>no title entered</h2><meta http-equiv=\"refresh\" content=\"2; URL=".$_SERVER['PHP_SELF']."\">");}
if((isset($_POST['name']))&&(!$_POST['name']=="")){$name = htmlentities($_POST['name'], ENT_QUOTES | ENT_IGNORE, "UTF-8");$name = stripslashes($name);}else{$name="anonymous";}
if(isset($_POST['email'])){$email = htmlentities($_POST['email'], ENT_QUOTES | ENT_IGNORE, "UTF-8");$email = stripslashes($email);}
$date=date('d/m/Y @ g:iA T');
if((isset($_POST['content']))&&(!$_POST['content']=="")){$content = htmlentities($_POST['content'], ENT_QUOTES | ENT_IGNORE, "UTF-8");$content = nl2br($content);$content = stripslashes($content);}else{die("<h2>no comment entered</h2><meta http-equiv=\"refresh\" content=\"2; URL=".$_SERVER['PHP_SELF']."\">");}
$ip=base64_encode($_SERVER['REMOTE_ADDR']);
if(!isset($_POST['tid'])){$op=1;}else{$op=0;}
if(isset($_POST['tid'])){$tid = htmlentities($_POST['tid'], ENT_QUOTES | ENT_IGNORE, "UTF-8");$tid = stripslashes($tid);}else{$tidget=mysql_query("SELECT MAX(tid) AS tid FROM posts LIMIT 1");$num_rows=mysql_num_rows($tidget);$tid=0;while($row=mysql_fetch_array($tidget)){$tid=$row['tid'];}++$tid;}
mysql_query("INSERT INTO `".$mysql['data']."`.`posts` (`title`,`name`,`email`,`date`,`content`,`ip`,`op`,`tid`) VALUES ('$title','$name','$email','$date','$content','$ip','$op','$tid')");
print("<h1>posted</h1><meta http-equiv=\"refresh\" content=\"2; URL=".$_SERVER['PHP_SELF']."\">");
} else {header('Location: ?v=index');}
?>
<h6>v1.0 // &copy;Flashwave 2014</h6>
</body>
</html>
