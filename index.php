<?php
/*
 * Ninechan Board Script
 * by Flashwave <http://flash.moe>
 * Distributed under the MIT-License
 */

// Set ninechan version, don't change this or it'll probably break things.
define('N_VERSION', '1.11alpha4');

// Configuration files
require 'config.php'; // Include Configuration
include 'lang/' . $ninechan['lang'] . '.php'; // Include language file


// Error Reporting
error_reporting($ninechan['exposeErrors'] ? -1 : 0);


// Check language version
if(LDATA_VERSION != N_VERSION) {
    print '<h2>Your language file [' . $ninechan['lang'] . '] is outdated!</h2>';
    print 'Please update your language file from Version ' . LDATA_VERSION . ' to Version ' . N_VERSION . '.';
    exit;
}

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


// Initialise Database
$sqldb->query( // Indented SQL, WOw !
    "CREATE TABLE IF NOT EXISTS 
    `" . $sql['table'] . "`
    (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` text NOT NULL,
        `name` text,
        `trip` text,
        `email` text,
        `date` text NOT NULL,
        `content` text NOT NULL,
        `password` text NOT NULL,
        `ip` text NOT NULL,
        `op` int(1) NOT NULL,
        `tid` int(11) NOT NULL,
        `locked` int(1) NOT NULL,
        `ban` int(1) NOT NULL,
        `del` int(1) NOT NULL,
        PRIMARY KEY (`id`)
    )
    ENGINE=InnoDB
    DEFAULT
    charset=latin1;"
);


// Cleaning strings
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
    $bbcodecatch    = array(
                        '/\[b\](.*?)\[\/b\]/is',
                        '/\[i\](.*?)\[\/i\]/is',
                        '/\[u\](.*?)\[\/u\]/is',
                        '/\[url\=(.*?)\](.*?)\[\/url\]/is',
                        '/\[url\](.*?)\[\/url\]/is',
                        '/\[spoiler\](.*?)\[\/spoiler\]/is',
                        '/&gt;&gt;(.*[0-9])/i',
                        '/^&gt;(.*?)$/im',
                        '/^.*(youtu.be|youtube.com\/embed\/|watch\?v=|\&v=)([^!<>@&#\/\s]*)/is'
                    );
    
    $bbcodereplace    = array(
                        '<b>$1</b>',
                        '<i>$1</i>',
                        '<u>$1</u>',
                        '<a href="$1" rel="nofollow" title="$2 - $1">$2</a>',
                        '<a href="$1" rel="nofollow" title="$1">$1</a>',
                        '<span class="spoiler">$1</span>',
                        '<a class="lquote" href="#$1">&gt;&gt;$1</a>',
                        '<span class="quote">&gt;$1</span>',
                        '<object type="application/x-shockwave-flash" style="width:425px; height:350px;" data="http://www.youtube.com/v/$2"><param name="movie" value="http://www.youtube.com/v/$2" /></object>'
                    );
    
    $content        = preg_replace(
                        $bbcodecatch,
                        $bbcodereplace,
                        $content
                    );
    
    return nl2br($content);
}

// Generating Random Password
function generatePassword() {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_@#$!*\/[]{}=+';
    
    for($i = 0, $pass = ''; $i < 34; $i++) {
        $index = rand(0, mb_strlen($chars) - 1);
        $pass .= mb_substr($chars, $index, 1);
    }
    
    return $pass;
}


// Moderation
function nMod($mode, $id, $action) {
    global $sql, $sqldb;
    
    switch($mode) {
        // Banning a poster
        case 'ban':
            $prepare = "UPDATE `". $sql['table'] ."` SET `ban` = :action WHERE `id` = :id";
            break;
        
        // Deleting a post
        case 'del':
            $prepare = "UPDATE `". $sql['table'] ."` SET `del` = :action WHERE `id` = :id";
            break;
        
        // Prune an entire thread
        case 'prune':
            $prepare = "UPDATE `". $sql['table'] ."` SET `del` = :action WHERE `tid` = :id";
            break;
        
        // Lock a thread
        case 'lock':
            $prepare = "UPDATE `". $sql['table'] ."` SET `locked` = :action WHERE `tid` = :id";
            break;
        
        // Return false if there's no proper option
        default:
            return false;
    }
    
    $query = $sqldb->prepare($prepare);
    
    $query->bindParam(':id',        $id,                    PDO::PARAM_INT);
    $query->bindParam(':action',    ($action ? '1' : '0'),  PDO::PARAM_INT);
    
    $query->execute();
    
    // Return true if successful
    return true;
}


// reCaptcha
if($ninechan['reCaptcha'])
    require $ninechan['reCaptchaLib'];


// Session
session_start(); // Start a session
$auth = @$_SESSION['mod']; // Set an alias for mod
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-type" content="text/html; charset=<?=$ninechan['charset'];?>" />
        <title><?=$ninechan['title'];?></title>
        <?=($ninechan['desc'] ? '<meta name="description" content="'.$ninechan['desc'].'" />' : null);?>
        <script type="text/javascript" charset="utf-8" src="ninechan.js"></script>
        <script type="text/javascript">
            ninechan.cookiePrefix = "<?=$ninechan['cookiePrefix'];?>";
            
            window.onload = function() {
                ninechan.init();
            }
        </script>
        <?php
        if($ninechan['styles']) { // Check if styles are enabled
            foreach($ninechan['styles'] as $styleUrl => $styleName) { // Get styles from array
                reset($ninechan['styles']); // Reset Array
                $mainStyle = key($ninechan['styles']); // Get first entry
                print '<link rel="'. ($styleUrl == $mainStyle ? null : 'alternate ') .'stylesheet" type="text/css" href="'. $styleUrl .'" title="'. $styleName .'" '. ($styleUrl == $mainStyle ? null : 'disabled ') .'/>'; // List every style
            }
        }
        ?>
    </head>
    <body>
        <h1><a href="./"><?=$ninechan['title'];?></a></h1>
        <?=($ninechan['desc'] ? '&nbsp;<i>'.$ninechan['desc'].'</i>' : null);?>
        <hr />
        <?php
        if($ninechan['closed']) { // Exit if board is set as closed in the config file
            print L_BOARD_CLOSED."<br /><i>".L_REASON.": ".$ninechan['closedReason']."</i>";
            exit;
        }
        
        $banCheck = ($sqldb->query("SELECT * FROM `".$sql['table']."` WHERE `ip`='".base64_encode($_SERVER['REMOTE_ADDR'])."' AND `ban`='1'")->rowCount() ? true : false); // Check if poster IP is banned, using num_rows because COUNT(*) didn't want to work or I did something wrong
        
        if($banCheck)
            print '<div class="banmsg">'.L_USERBANNEDMSG.'</div><hr />';
        
        print '<div class="banmsg">Everything is about to be broken and you can\'t do anything about it.</div><hr />';
        
        if(!isset($_COOKIE[$ninechan['cookiePrefix'].'pass'])) { // Check if pass cookie is set if not set it
            setcookie( // Generate random password and assign it to cookie
                $ninechan['cookiePrefix'] . "pass",
                generatePassword(),
                time() + $ninechan['cookieLifetime'],
                "/",
                $_SERVER['SERVER_NAME']
            );
        }
        
        if(isset($_GET['v'])) {
            switch($_GET['v']) {
                // Main index
                case 'index':
                    print '<h2>'.L_THREADS.'</h2>';    // Section title
                    print '<h3><a href="?v=post">'.L_NEWTHREAD.'</a></h3>'; // New thread link
                    
                    // Query to get OP posts
                    $getThreads = $sqldb->query("SELECT * FROM `".$sql['table']."` WHERE `del`='0' AND `op`='1' ORDER BY `date` desc".($ninechan['sage'] ? " LIMIT ".$ninechan['sageLimit'] : null));
                    
                    // List posts
                    if(!$getThreads->rowCount()) {       // Check if there's more than 1 post
                        print '<h3>'.L_EMPTY.'</h3>';    // Return L_EMPTY otherwise
                    } else {
                        print '<ol>';
                        
                        while($thread = $getThreads->fetch()) {
                            // Need to figure out why the w3c validator does not like the & in a place where it should be...
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
                    
                    // Set Thread ID variable
                     $threadId = preg_replace('/\D/', '', $_GET['t']);
                    
                   // Prepare statement to get the thread data
                    $getThread = $sqldb->prepare("SELECT * FROM `".$sql['table']."` WHERE `tid` = :tid AND `del` = '0' ORDER BY `id`");
                    
                    // Append directives
                    $getThread->bindParam(':tid', $threadId, PDO::PARAM_INT);
                    
                    // Execute Statement
                    $getThread->execute();
                    
                    if(!$getThread->rowCount()) { // Check if requested thread exists
                        print L_NONEXISTENT; // If not return L_NONEXISTENT
                        break;
                    } else {
                        // Get thread data
                        $threadData = $getThread->fetchAll();
                        
                        // Iterate over the thread data
                        foreach($threadData as $postData) {
                            if($postData['op']) { // Assign thread variables if poster is OP
                                print '<h2>'.L_THREAD.': '.$postData['title'].'</h2>'; // Print L_THREAD and the name of the thread
                                
                                if($postData['locked']) // Check if thread is locked and if true display message
                                    print '<h3>'.L_LOCKED.'</h3>';
                                else // otherwise print reply button
                                    print '<h3><a href=?v=post&t='.$postData['tid'].'>'.L_NEWREPLY.'</a></h3>';
                                
                                // Mod tools
                                if($auth == $ninechan['modPass']) {
                                    print '<font size="2">[<a href=?v=mod&del=purge&id='.$postData['id'].'>'.L_PURGE.'</a>]';
                                    if($postData['lock']) {
                                        print ' [<a href="?v=mod&lock=false&id='.$postData['id'].'">'.L_UNLOCK.'</a>]</font>';
                                    } else {
                                        print ' [<a href="?v=mod&lock=true&id='.$postData['id'].'">'.L_LOCK.'</a>]</font>';
                                    }
                                }
                            }
                            
                            // Set names to Anonymous if required in the configuration
                            if($ninechan['forcedAnon']) {
                                $posterName = $ninechan['anonName'];
                                $posterTrip = null;
                            } elseif($ninechan['modsAreAnon'] == 1 && in_array($row['trip'], $ninechan['modTrip'])) { // Check if forced anon for mods is enabled
                                $posterName = $ninechan['anonName'];
                                $posterTrip = null;
                            } elseif($ninechan['modsAreAnon'] == 2 && in_array($row['trip'], $ninechan['modTrip'])) { // Check if forced trip anon for mods is enabled
                                $posterName = $postData['name'];
                                $posterTrip = null;
                            } elseif($ninechan['adminsAreAnon'] == 1 && in_array($row['trip'], $ninechan['adminTrip'])) { // Check if forced anon for admins is enabled
                                $posterName = $ninechan['anonName'];
                                $posterTrip = null;
                            } elseif($ninechan['adminsAreAnon'] == 2 && in_array($row['trip'], $ninechan['adminTrip'])) { // Check if forced trip anon for admins is enabled
                                $posterName = $postData['name'];
                                $posterTrip = null;
                            } else {
                                if(empty($postData['name'])) {
                                    $posterName = $ninechan['anonName'];
                                } else {
                                    $posterName = $postData['name'];
                                }
                                if(!empty($post['trip'])){
                                    $posterTrip = ' <span class="trip">!'.$postData['trip'].'</span>';
                                } else {
                                    $posterTrip = null;
                                }
                            }
                            
                            // Print the regular fieldset for posts
                            print '<fieldset id="'.$postData['id'].'">';
                            print '<legend><b>'.$postData['title'].'</b> <a href="#'.$postData['id'].'">'.L_BY.'</a> <b>';
                            
                            if(!empty($postData['email']))
                                print '<a href="mailto:'.$postData['email'].'">'.$posterName.$posterTrip.'</a>';
                            else
                                print $posterName.$posterTrip;
                            
                            if(in_array($postData['trip'], $ninechan['adminTrip'])) // Check if tripcode is Admin
                                print ' <span class="admincap">## Admin</span>';
                            elseif(in_array($postData['trip'], $ninechan['modTrip'])) // Check if tripcode is Mod
                                print ' <span class="modcap">## Mod</span>';
                        
                            print '</b></legend>';
                            
                            print parseBBcode($postData['content']); // Parse BBcodes on post content
                            print '<br /><br />';
                            
                            print ($postData['ban'] ? '<b><font size="2" class="ban">'.L_POSTBANNED.'</font></b><br />' : null);
                            
                                
                            if($auth == $ninechan['modPass']) {
                                print '<font size=2>[<a href="?v=mod&del=true&id='.$postData['id'].'&t='.$postData['tid'].'">'.L_DELETE.'</a>] [<a href="?v=mod&ban='.($postData['ban'] ? 'false' : 'true').'&id='.$postData['id'].'&t='.$postData['tid'].'">'.($postData['ban'] ? L_UNBAN : L_BAN).'</a>] [IP: '.base64_decode($postData['ip']).']</font><br />'; // Regular mod tools
                            }
                            
                            print '<font size=2><i>'.date($ninechan['dateFormat'], $postData['date']).' <a href="#'.$postData['id'].'">No.</a> <a href="?v=post&t='.$postData['tid'].'&text=>>'.$postData['id'].'">'.$postData['id'].'</a> [<a href="?v=del&id='.$postData['id'].'" title="'.L_DELPOST.'">X</a>]</i></font>';
                            
                            print '</fieldset>';
                        }
                        
                        // Mod tools
                        if($auth == $ninechan['modPass']) {
                            print '<font size="2">[<a href=?v=mod&del=purge&id='.$threadData['id'].'>'.L_PURGE.'</a>]';
                            if($threadData['lock']) {
                                print ' [<a href="?v=mod&lock=false&id='.$threadData['id'].'">'.L_UNLOCK.'</a>]</font>';
                            } else {
                                print ' [<a href="?v=mod&lock=true&id='.$threadData['id'].'">'.L_LOCK.'</a>]</font>';
                            }
                        }
                        
                        if($threadData[0]['locked']) // Check if thread is locked and if true display message
                            print '<h3>'.L_LOCKED.'</h3>';
                        else // otherwise print reply button
                            print '<h3><a href=?v=post&t='.$threadData[0]['tid'].'>'.L_NEWREPLY.'</a></h3>';
                    }
                break;
                
                // Posting
                case 'post':
                    // Check if user is banned and if so don't display the form at all
                    if($banCheck) {
                        print '<h2>'. L_USERBANNED .'</h2>';
                        break;
                    }
                    
                    // Print "global" form elements
                    print '<form method="post" action="'. $_SERVER['PHP_SELF'] .'?v=submit">';                    
                    print '<table id="postForm" class="postForm">';
                    
                    // Check if a thread ID is set
                    if(isset($_GET['t'])) {
                        // If so make sure it's numeric
                        if(!is_numeric($_GET['t'])) {
                            print '<h2>'. L_NOTNUMERIC .'</h2>';
                            print '<a href="'. $_SERVER['PHP_SELF'] .'?v=index">'. L_RETURN .'</a>';
                            break;
                        }
                        
                        // Assign Thread ID
                        $threadId = preg_replace('/\D/', '', $_GET['t']);
                        
                        // Prepare statement to get thread information
                        $getData = $sqldb->prepare("SELECT * FROM `".$sql['table']."` WHERE `tid` = :tid AND `op` = '1' ORDER BY `id` LIMIT 1");
                        
                        // Bind Parameters
                        $getData->bindParam(':tid', $threadId, PDO::PARAM_INT);
                        
                        // Execute statement
                        $getData->execute();
                        
                        // Assign thread data to variable
                        $threadData = $getData->fetch();
                        
                        // Print non-existent thread if nothing was found
                        if($threadData == NULL) {
                            print '<h2>'. L_NONEXISTENT .'</h2>';
                            print '<a href="'. $_SERVER['PHP_SELF'] .'?v=index">'. L_RETURN .'</a>';
                            break;
                        }
                        
                        // Since we're not creating a thread set newThread to false
                        $newThread = false;
                        
                        $locked = $threadData['locked'];
                        
                        // Don't display posting form if thread is locked
                        if($locked) {
                            print '<h2>'. L_LOCKEDMSG .'</h2>';
                            print '<meta http-equiv="refresh" content="2; URL="./?v=thread&t='. $threadData['tid'] .'" />';
                        } else {
                            print '<h2>'. L_RETO .' '. $threadData['title'] .' ['. $threadData['tid'] .']</h2>';
                            print '<input type="hidden" name="tid" value="'. $threadData['tid'] .'" />';
                            
                            $threadTitle = 'Re: '. $threadData['title'];
                        }
                    } else {
                        print '<h2>'. L_NEWTHREAD .'</h2>';
                        
                        $newThread  = true;
                        $locked     = false;
                    }
                    
                    if(isset($_GET['text']))
                        $threadText = $_GET['text']."\r\n";
                    else
                        $threadText = null;
                    
                    if(!$locked) { //-- Only display post page if thread isn't locked
                        print '<tr><td>'. L_NAME .'</td><td><input name="name" type="text" value="'. @$_COOKIE[$ninechan['cookiePrefix'].'name'] .'" /></td></tr>';
                        print '<tr><td>'. L_EMAIL .'</td><td><input name="email" type="text" value="'. @$_COOKIE[$ninechan['cookiePrefix'].'email'] .'" /></td></tr>';
                        print '<tr><td>'. L_TITLE .'</td><td><input name="title" type="text" value="'. @$threadTitle .'" /></td></tr>';
                        print '<tr><td>'. L_COMMENT .'</td><td><textarea name="content" rows="6" cols="48">'. @$threadText .'</textarea></td></tr>';
                        if($ninechan['reCaptcha']) //-- Display reCaptcha if enabled in config
                            print '<tr><td>'. L_VERIFICATION .'</td><td>'. recaptcha_get_html($ninechan['reCaptchaPublic']) .'</td></tr>';
                        print '<tr><td>'. L_PASSWORD .'</td><td><input name="password" type="password" placeholder="'.L_PASSWORDCONTEXT.'" value="'. @$_COOKIE[$ninechan['cookiePrefix'].'pass'] .'" /> <input value="'. L_SUBMIT .'" type="submit" /></td></tr>';
                        print '</table></form>';
                    }
                break;
                
                // Submitting posts
                case 'submit':
                    if($banCheck) {
                        print '<h2>'.L_USERBANNED.'</h2>';
                        break;
                    }
                    
                    $submitData = array(); // Assign array to variable so we can store things in it later
                    
                    // Check reCaptcha
                    $fucked = false;
                    if($ninechan['reCaptcha'])    {
                        $reCaptcha = recaptcha_check_answer($ninechan['reCaptchaPrivate'], $_SERVER['REMOTE_ADDR'], $_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field']); // reCaptcha data
                        
                        if(!$reCaptcha->is_valid) { // If reCaptcha is invalid die and display error message
                            print '<h2>'.L_INVALIDCAPTCHA.'</h2><meta http-equiv="refresh" content="2; URL='.$_SERVER['PHP_SELF'].'" />';
                            $fucked = true;
                        }
                    }
                    
                    if($fucked)
                        break;
                    
                    // Assign variables
                    $submitData['title']    = removeSpecialChars($_POST['title']);
                    $submitData['content']  = removeSpecialChars($_POST['content']);
                    $submitData['name']     = removeSpecialChars($_POST['name']);
                    $submitData['nameNT']   = (strlen(strstr($submitData['name'], "#", true)) ? strstr($submitData['name'], "#", true) : $submitData['name']);
                    $submitData['trip']     = parseTrip($_POST['name']);
                    $submitData['email']    = ($_POST['email'] == 'noko' ? null : removeSpecialChars($_POST['email']));
                    $submitData['date']     = time();
                    $submitData['password'] = md5(strlen($_POST['password']) ? $_POST['password'] : generatePassword());
                    $submitData['ip']       = base64_encode($_SERVER['REMOTE_ADDR']);
                    $submitData['op']       = (isset($_POST['tid']) ? 0 : 1);
                    $submitData['id']       = ($submitData['op'] ? ($sqldb->query("SELECT MAX(tid) AS `tid` FROM `".$sql['table']."` LIMIT 1")->fetch()['tid'] + 1) : removeSpecialChars($_POST['tid']));
                    $submitData['noredir']  = ($submitData['email'] == 'noko' ? true : false);

                    // Assign cookies
                    setcookie(
                        $ninechan['cookiePrefix'] . "name",
                        $submitData['name'],
                        time() + $ninechan['cookieLifetime'],
                        $ninechan['cookiePath'],
                        $_SERVER['SERVER_NAME']
                    );
                    setcookie(    
                        $ninechan['cookiePrefix'] . "email",
                        $submitData['email'],
                        time() + $ninechan['cookieLifetime'],
                        $ninechan['cookiePath'],
                        $_SERVER['SERVER_NAME']
                    ); 
                    setcookie(
                        $ninechan['cookiePrefix'] . "pass",
                        $submitData['password'],
                        time() + $ninechan['cookieLifetime'],
                        $ninechan['cookiePath'],
                        $_SERVER['SERVER_NAME']
                    ); 
                    
                    // Check if title is valid
                    if(empty($submitData['title']) || strlen($submitData['title']) < $ninechan['titleMinLength']) { // Check if too short
                        print '<h2>'. L_TITLETOOSHORT .'</h2>';
                        print '<meta http-equiv="refresh" content="2; URL='.$_SERVER['PHP_SELF'].'" />';
                        break;
                    }
                    if(strlen($submitData['title']) > $ninechan['titleMaxLength']) { // Check if too long
                        print '<h2>'. L_TITLETOOLONG .'</h2>';
                        print '<meta http-equiv="refresh" content="2; URL='.$_SERVER['PHP_SELF'].'" />';
                        break;
                    }
                    
                    // Check if comment is valid
                    if(empty($submitData['title']) || strlen($submitData['content']) < $ninechan['commentMinLength']) { // Check if too short
                        print '<h2>'. L_COMMENTTOOSHORT .'</h2>';
                        print '<meta http-equiv="refresh" content="2; URL='.$_SERVER['PHP_SELF'].'" />';
                        break;
                    }
                    if(strlen($submitData['content']) > $ninechan['commentMaxLength']) { // Check if too long
                        print '<h2>'.L_COMMENTTOOLONG.'</h2>';
                        print '<meta http-equiv="refresh" content="2; URL='.$_SERVER['PHP_SELF'].'" />';
                        break;
                    }
                    
                    $submitPost = $sqldb->prepare("INSERT INTO `". $sql['table'] ."` (`title`, `name`, `trip`, `email`, `date`, `content`, `password`, `ip`, `op`, `tid`) VALUES (:title, :name, :trip, :email, :date, :content, :password, :ipaddr, :op, :threadid)");
                    
                    $submitPost->bindParam(':title',    $submitData['title']);
                    $submitPost->bindParam(':name',     $submitData['nameNT']);
                    $submitPost->bindParam(':trip',     $submitData['trip']);
                    $submitPost->bindParam(':email',    $submitData['email']);
                    $submitPost->bindParam(':date',     $submitData['date']);
                    $submitPost->bindParam(':content',  $submitData['content']);
                    $submitPost->bindParam(':password', $submitData['password']);
                    $submitPost->bindParam(':ipaddr',   $submitData['ip']);
                    $submitPost->bindParam(':op',       $submitData['op']);
                    $submitPost->bindParam(':threadid', $submitData['id']);
                    
                    $submitPost->execute();
                    
                    print '<h1>'.L_POSTED.'</h1>';
                    
                    //print '<meta http-equiv="refresh" content="1; URL='.($submitData['noredir'] ? '?v=index' : '?v=thread&t='.$submitData['id']).'" />';
                break;
                
                case 'del':
                    if($banCheck) {
                        print '<h2>'.L_USERBANNED.'</h2>';
                        break;
                    }
                    
                    $deletionData = array(); // Assign array to variable so we can store things in it later
                    
                    if(isset($_POST['id'])) {
                        $getPostData = $sqldb->prepare("SELECT * FROM `". $sql['table'] ." WHERE `id` = :pid LIMIT 1");
                    
                        $getPostData->bindParam(':pid', $_POST['id'], PDO::PARAM_INT);
                        
                        $getPostData->execute();
                    
                        $getData = $getPostData->fetchAll();
                    
                        if(!count($getData)) {
                            header('Location: ./');
                            print '<meta http-equiv="refresh" content="0; url='. $_SERVER['PHP_SELF'] .'?v=index" />'; // fallback
                        }
                        
                        if($getData[0]['locked']) {
                            print '<h2>'.L_LOCKEDMSG_2.'</h2>';
                            print '<meta http-equiv="refresh" content="2; URL='. $_SERVER['PHP_SELF'] .'?v=index" />';
                        } else {
                            if($getData[0]['password'] == md5($_POST['password'])) {
                                nMod('del', $getData['id'], true);
                                print '<h2>'.L_DEL_SUCCEED.'</h2>';
                                print '<meta http-equiv="refresh" content="2; URL='. $_SERVER['PHP_SELF'] .'?v=index" />';
                            } else {
                                print '<h2>'.L_DEL_FAILED.'</h2>';
                                //print '<meta http-equiv="refresh" content="2; URL='. $_SERVER['PHP_SELF'] .'?v=index" />';
                            }
                        }
                    } elseif(isset($_GET['id'])) {
                        if(!is_numeric($_GET['id'])) {
                            header('Location: ./');
                            print '<meta http-equiv="refresh" content="0; url='. $_SERVER['PHP_SELF'] .'?v=index" />'; // fallback
                        }
                        
                        $getPostData = $sqldb->prepare("SELECT * FROM `". $sql['table'] ." WHERE `id` = :pid LIMIT 1");
                    
                        $getPostData->bindParam(':pid', $_POST['id'], PDO::PARAM_INT);
                        
                        $getPostData->execute();
                    
                        $getData = $getPostData->fetch();
                    
                        if(!count($getData)) {
                            header('Location: ./');
                            print '<meta http-equiv="refresh" content="0; url='. $_SERVER['PHP_SELF'] .'?v=index" />'; // fallback
                        }
                        
                        print '<form method="post" action="'. $_SERVER['PHP_SELF'] .'?v=del">';
                        
                        if($getData['locked']) {
                            print '<h2>'.L_LOCKEDMSG_2.'</h2>';
                            print '<meta http-equiv="refresh" content="2; URL='. $_SERVER['PHP_SELF'] .'?v=index" />';
                        } else {
                            print '<h2>'.L_DELPOST.' '.$getData['id'].'</h2>';
                            print '<input type="hidden" name="id" value="'. $_GET['id'] .'" />';
                        }
                        
                        print '<table id="postForm" class="postForm">';
                        
                        print '<tr><td>'.L_PASSWORD.'</td>';
                        print '<td><input name="password" type="password" placeholder="'.L_PASSWORDCONTEXT.'" value="'.@$_COOKIE[$ninechan['cookiePrefix'].'pass'].'" /> ';
                        print '<input value="'.L_SUBMIT.'" type="submit" /></td></tr>';
                        
                        print '</table>';
                        print '</form>';
                    }
                break;
                
                // Moderator Authentication
                case 'mod':
                    if($auth == $ninechan['modPass']) { // Check if authenticated
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
                        
                        if(isset($_GET['ban']) && isset($_GET['id']) && isset($_GET['t'])) { // Ban handler
                            if($_GET['ban'] == "true")
                                banPost($_GET['id'], true);
                            else
                                banPost($_GET['id'], false);

                            header('Location: ?v=thread&t='.$_GET['t']);
                            print '<meta http-equiv="refresh" content="0; url=?v=thread&t='.$_GET['t'].'" />'; // fallback
                        }
                        if(isset($_GET['del']) && isset($_GET['id'])) { // Deletion handler
                            if($_GET['del'] == "purge") {
                                pruneThread($_GET['id'], true);
                                
                                header('Location: ?v=index');
                                print '<meta http-equiv="refresh" content="0; url=?v=index" />'; // fallback
                            } else {
                                if($_GET['del'] == "true")
                                    delPost($_GET['id'], true);
                                else
                                    delPost($_GET['id'], false);

                                header('Location: ?v=thread&t='.$_GET['t']);
                                print '<meta http-equiv="refresh" content="0; url=?v=thread&t='.$_GET['t'].'" />'; // fallback
                            }
                        }
                        if(isset($_GET['lock']) && isset($_GET['id'])) { // Lock handler
                            if($_GET['lock'] == "true")
                                lockThread($_GET['id'], true);
                            else
                                lockThread($_GET['id'], false);

                            header('Location: ?v=thread&t='.$_GET['id']);
                            print '<meta http-equiv="refresh" content="0; url=?v=thread&t='.$_GET['id'].'" />'; // fallback
                        }
                    } else { // Else display login screen
                        if(isset($_POST['modPass'])) {
                            if($_POST['modPass'] == $ninechan['modPass'])
                                $_SESSION['mod'] = $ninechan['modPass'];

                            header('Location: ?v=mod');
                            print '<meta http-equiv="refresh" content="0; url=?v=mod" />'; // fallback
                        }
                        
                        print '<h2>'.L_MODLOGIN.'</h2>';
                        print '<form method="post" action="'.$_SERVER['PHP_SELF'].'?v=mod">';
                        print '<input type="password" name="modPass" /><input type="submit" value="'.L_LOGIN.'" />';
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
            foreach($ninechan['styles'] as $styleUrl => $styleName) { // Get styles from array
                print '[<a href="javascript:;" onclick="ninechan.setStyle(\''.$styleName.'\');">'.$styleName.'</a>] '; // List every style
            }
            print '</h6>';
        }
        ?>
        <!--
            Please retain the full copyright notice below including the link to flash.moe.
            This not only gives respect to the amount of time given freely by the developer
            but also helps build interest, traffic and use of ninechan.
        -->
        <h6>
            <a href="http://ninechan.flash.moe/" target="_blank">ninechan</a>
            <?=($ninechan['showVersion'] ? N_VERSION : null);?>
            &copy; <a href="http://flash.moe/" target="_blank">Flashwave</a>
        </h6>
    </body>
</html>
