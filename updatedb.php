<?php
include("config.php");
$sqldb = new mysqli($sql['host'],$sql['user'],$sql['pass'],$sql['data']);
if($sqldb->connect_errno){die("SQL error");}
print("<h1>Welcome to the ninechan database updater</h1><i>For updating to ninechan 1.8 to 1.9</i><hr>");
if(isset($_POST['ready'])){
	print("<form method='post' action=".$_SERVER['PHP_SELF'].">Database update successful! Now delete updatedb.php for security reasons, ninechan won't work while this file is present!<br /><input type='submit' name='kill' value='Delete updatedb.php' /></form>");
	$sqldb->query("ALTER TABLE `".$sql['table']."` ADD COLUMN `password` text NOT NULL AFTER `content`;");
	$sqldb->query("ALTER TABLE `".$sql['table']."` ADD COLUMN `del` int(11) NOT NULL AFTER `ban`;");
	$sqldb->query("ALTER TABLE `".$sql['table']."` ADD COLUMN `trip` text NOT NULL AFTER `name`;");
	$sqldb->query("ALTER TABLE `".$sql['table']."` CHANGE `lock` `locked` int(11) NOT NULL;");
	$convertdate = $sqldb->query("SELECT * FROM ".$sql['table']." ORDER BY id");
	while($row = $convertdate->fetch_array(MYSQLI_ASSOC)){
		$date=$row['date'];
		$date=str_replace('@ ','',$date);
		$date=str_replace('/','-',$date);
		$date=strtotime($date);
		$sqldb->query("UPDATE `".$sql['table']."` SET `date`=".$date." WHERE `id`=".$row['id']);
	}
}elseif(isset($_POST['kill'])){
	print("Deleted updatedb.php, you will now be redirected to the board.");
	unlink("updatedb.php");
	print("<meta http-equiv=\"refresh\" content=\"1; URL=./?v=index\">");
}else{
	print("<form method='post' action=".$_SERVER['PHP_SELF']."><input type='submit' name='ready' value=\"Click this button once you're ready\" /> (note that this might take a while depending on how many posts your board has)</form>");
}
