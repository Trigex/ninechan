<?php
/*
 * Ninechan Board Script SQL Updater for Version 1.11
 * by Flashwave <http://flash.moe>
 * Distributed under the MIT-License
 */

// Require configuration
require_once 'config.php'; 

// Force enable error reporting
error_reporting(-1);
 
// Check dependencies
if(version_compare(phpversion(), '5.3.0', '<')) // PHP 5.3 or higher
    die('<h2>' . L_PHP_OUTDATED . '</h2>');

if(!extension_loaded('PDO')) // Check if PHP Data Objects is available
    die('<h2>' . L_SQL_FUNCTION . '</h2>');


// Connect to SQL using PDO
try {
    $sqldb = new PDO($sql['dsn'], $sql['user'], $sql['pass']);
} catch(PDOException $e) { // Catch connection error
    print '<h2>' . L_SQL_CONNECT . '</h2>';
    die($e->getMessage());
}

print '<html><head><title>ninechan db updater</title><link rel="stylesheet" type="text/css" href="./ninechan.css" /></head><body>';
print '<h1>ninechan updater</h1>&nbsp;<i>For updating your database to ninechan 1.9/1.10 to 1.11</i><hr />';

if(isset($_POST['ready'])) {
	print '<form method="post" action="'. $_SERVER['PHP_SELF'] .'">';
    print 'Database update successful!';
    print 'Now delete updatedb.php for security reasons, <u>unlike ninechan 1.9 this version will continue working while this file is present</u>.<br />';
    print '<input type="submit" name="kill" value="Delete updatedb.php" />';
    print '</form>';
    
	$sqldb->query("ALTER TABLE `".$sql['table']."` CHANGE `name` `name` text;");
	$sqldb->query("ALTER TABLE `".$sql['table']."` CHANGE `trip` `trip` text;");
	$sqldb->query("ALTER TABLE `".$sql['table']."` CHANGE `email` `email` text;");
} elseif(isset($_POST['kill'])) {
	print 'Deleted updatedb.php, you will now be redirected to the board.';
	unlink("updatedb.php");
	print '<meta http-equiv="refresh" content="1; URL=./">';
} else {
	print '<form method="post" action="'. $_SERVER['PHP_SELF'] .'"><input type="submit" name="ready" value="Click this button once you\'re ready" /></form>';
}

print '<h6><a href="http://ninechan.flash.moe/" target="_blank">ninechan</a> updater 1.11 &copy; <a href="http://flash.moe/" target="_blank">Flashwave</a></h6></body></html>';
