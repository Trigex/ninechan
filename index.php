<?php
/*
 * Ninechan Board Script
 * by Flashwave <http://flash.moe>
 * Distributed under the MIT-License
 */

// Set ninechan version, don't change this or it'll probably break things.
$version = '2.1';

// Language file versions this version is compatible with
$langCompat = [
    '2.1'
];

// Error messages
function error($data) {

    global $version;

    print '<!DOCTYPE html><html><head><meta charset="utf-8" /><title>ninechan error</title></head><body>';
    print $data;
    print '<hr />ninechan '. $version .'</body></html>';
    exit;

}

// Include Configuration
if(@!include 'config.php')
    error('Failed to load configuration file.');

// Getting configuration values
function getConfig($key) {

    // Make $ninechan global
    global $ninechan;

    // Check if the key exists and return the proper string
    return array_key_exists($key, $ninechan) ? $ninechan[$key] : null;

}

// Include language file
if(@!include 'lang/'. getConfig('lang') .'.php')
    error('Failed to load language file.');

// Error Reporting
error_reporting(getConfig('exposeErrors') ? -1 : 0);

// Getting language string
function getLang($key) {

    // Make $language global
    global $language;

    // Check if the key exists and return the proper string
    return array_key_exists($key, $language) ? $language[$key] : 'Undefined index';

}

// Cleaning strings
function cleanString($string, $lower = false, $nospecial = false) {

    // Run common sanitisation function over string
    $string = htmlentities($string, ENT_QUOTES | ENT_IGNORE, getConfig('charset'));
    $string = stripslashes($string);
    $string = strip_tags($string);

    // If set also make the string lowercase
    if($lower)
        $string = strtolower($string);

    // If set remove all characters that aren't a-z or 0-9
    if($nospecial)
        $string = preg_replace('/[^a-z0-9]/', '', $string);

    // Return clean string
    return $string;

}

// Parsing tripcodes
function parseTrip($name) {

    // Match ! or # and everything after it
    if(preg_match("/(#|!)(.*)/", $name, $matches)) {

        // Get the cap code
        $cap = $matches[2];
        $cap = mb_convert_encoding($cap, 'SJIS', 'UTF-8');
        $cap = str_replace('#', '', $cap);
        $cap = str_replace('&', '&amp;', $cap);
        $cap = str_replace('"', '&quot;', $cap);
        $cap = str_replace("'", '&#39;', $cap);
        $cap = str_replace('<', '&lt;', $cap);
        $cap = str_replace('>', '&gt;', $cap);

        // Create the salt
        $salt = substr($cap .'H.', 1, 2);
        $salt = preg_replace('/[^.\/0-9:;<=>?@A-Z\[\\\]\^_`a-z]/', '.', $salt);
        $salt = strtr($salt, ':;<=>?@[\]^_`', 'ABCDEFGabcdef');

        // Generate tripcode
        $trip = substr(crypt($cap, $salt), -10);

        // Return tripcode
        return $trip;

    }

}

// Parsing post tags
function parsePost($content){

    // Get bbcodes file
    $bbcodes = json_decode(file_get_contents(getConfig('bbCodes')), true);

    // Parse bbcodes
    $content = preg_replace(array_flip($bbcodes), $bbcodes, $content);

    // Add newlines and return
    return nl2br($content);

}

// Generating Random Password
function generatePassword() {

    // Set characters to work with
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_@#$!*\/[]{}=+';

    // Generate the string
    for($i = 0, $pass = ''; $i < 34; $i++) {
        $index = rand(0, mb_strlen($chars) - 1);
        $pass .= mb_substr($chars, $index, 1);
    }

    // Return it
    return $pass;

}

// Moderation
function nMod($mode, $id, $action) {

    // Make SQL variables global
    global $sql, $sqldb;

    // Switch to the proper mode
    switch($mode) {

        // Banning a poster
        case 'ban':
            $prepare = "UPDATE `". $sql['table'] ."` SET `ban` = :action WHERE `id` = :id";
            break;

        // Deleting a post
        case 'del':
            $prepare = "DELETE FROM `". $sql['table'] ."` WHERE `id` = :id";
            break;

        // Prune an entire thread
        case 'prune':
            $prepare = "DELETE FROM `". $sql['table'] ."` WHERE `tid` = :id";
            break;

        // Lock a thread
        case 'lock':
            $prepare = "UPDATE `". $sql['table'] ."` SET `locked` = :action WHERE `tid` = :id";
            break;

        // Return false if there's no proper option
        default:
            return false;

    }

    $query  = $sqldb->prepare($prepare);
    $action = $action ? '1' : '0';

    $query->bindParam(':id',        $id,        PDO::PARAM_INT);
    $query->bindParam(':action',    $action,    PDO::PARAM_INT);

    $query->execute();

    // Return true if successful
    return true;

}

// Board HTML header
function nHead() {

    $header = '<!DOCTYPE html>
<html>
    <head>
        <meta charset="'. getConfig('charset') .'" />
        <title>'. getConfig('title') .'</title>'.
        (getConfig('desc') ? '<meta name="description" content="'. getConfig('desc') .'" />' : '')
        .'<script type="text/javascript" charset="'. getConfig('charset') .'" src="ninechan.js"></script>
        <script type="text/javascript">

            ninechan.cookiePrefix = "'. getConfig('cookiePrefix') .'";

            window.onload = function() {
                ninechan.init();
            }

        </script>';

        // Check if styles are enabled
        if(getConfig('styles')) {

            // Take the array
            $styles = getConfig('styles');

            // Iterate over all styles
            foreach($styles as $url => $name) {

                // Get the key of the first entry
                $main = key($styles); 

                // Append styles to the header
                $header .= '<link rel="'. ($url == $main ? null : 'alternate ') .'stylesheet" type="text/css" href="'. $url .'" title="'. $name .'" '. ($url == $main ? null : 'disabled ') .'/>';

            }

        }

    $header .= '</head>
    <body>
        <h1><a href="./">'. getConfig('title') .'</a></h1>'.
        (getConfig('desc') ? '&nbsp;<i>'. getConfig('desc') .'</i>' : '')
        .'<hr /><h6>[<a href="?v=index">'. getLang('INDEX') .'</a>] [<a href="?v=post">'. getLang('NEWTHREAD') .'</a>] [<a href="?v=mod">'. getLang('MANAGE') .'</a>]</h6><hr />';

    return $header;

}

// Board HTML footer
function nFoot() {

    // Make $version global
    global $version;

    $footer = '<hr />';

    // Check if styles are enabled
    if(getConfig('styles')) {

        // Take the array
        $styles = getConfig('styles');

        $footer .= '<h6>';

        // List every style
        foreach($styles as $name)
            $footer .= '[<a href="javascript:void(0);" onclick="ninechan.setStyle(\''.$name.'\');">'.$name.'</a>]'."\r\n";

        $footer .= '</h6>';

    }

    /*
        Please retain the full copyright notice below including the link to flash.moe.
        This not only gives respect to the amount of time given freely by the developer
        but also helps build interest, traffic and use of ninechan and other projects.
    */

    $footer .= '<h6>
            <a href="http://ninechan.flash.moe/" target="_blank">ninechan</a>
            '. (getConfig('showVersion') ? $version : '') .'
            &copy; <a href="http://flash.moe/" target="_blank">Flashwave</a>
        </h6>
    </body>
</html>';

    // Return the footer
    return $footer;

}

// Setting cookies
function nCookie($name, $content) {

    // Execute setcookie()
    setcookie(
        getConfig('cookiePrefix') . $name,
        $content,
        time() + getConfig('cookieLifetime'),
        getConfig('cookiePath'),
        $_SERVER['SERVER_NAME']
    );

}

// Check if the IP is banned
function checkBan($ip) {

    // Make SQL variables global
    global $sql, $sqldb;

    // Base64 encode IP
    $ip = base64_encode($ip);

    // Prepare the statement
    $banCheck = $sqldb->prepare("SELECT * FROM `". $sql['table'] ."` WHERE `ip` = :ip AND `ban` = '1'");

    // Append ip the statement
    $banCheck->bindParam(':ip', $ip, PDO::PARAM_STR);

    // Execute statement
    $banCheck->execute();

    // Fetch array
    $return = $banCheck->fetchAll(PDO::FETCH_ASSOC);

    // Return the array
    return $return;

}

// Getting thread data
function getPosts($id = 0) {

    // Make SQL variables global
    global $sql, $sqldb;

    // Prepare the statement
    $getPosts = $sqldb->prepare("SELECT * FROM `". $sql['table'] ."` WHERE ". ($id ? "`tid` = :tid" : "`op` = '1'") ." ORDER BY `". ($id ? "id`" : "lastreply` DESC"));

    // Bind the ID to the statement
    if($id)
        $getPosts->bindParam(':tid', $id, PDO::PARAM_INT);

    // Execute statement
    $getPosts->execute();

    // Fetch array
    $return = $getPosts->fetchAll(PDO::FETCH_BOTH);

    // Return the array
    return $return;

}

function verifyCaptcha($response) {

    // Attempt to get the response
    $resp = @file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='. getConfig('reCaptchaPrivate') .'&response='. $response);

    // In the highly unlikely case that it failed to get anything forge a false
    if(!$resp)
        error('Could not connect to the ReCAPTCHA server.');

    // Decode the response JSON from the servers
    $resp = json_decode($resp, true);

    // Return shit
    return $resp;

}

// Check language version
if(!in_array($langVersion, $langCompat)) {

    print '<h2>The selected language file is incompatible with this version!</h2>';
    print 'The version your language file was created for is <b>'. $langVersion .'</b>.<br />';
    print 'Your version of ninechan is compatible with the following language file versions:<ul>';
    foreach($langCompat as $ver) print '<li>'. $ver;
    print '</ul>';
    exit;

}

// Check dependencies
if(version_compare(phpversion(), '5.3.0', '<')) // PHP 5.3 or higher
    error('<h2>'. getLang('PHP_OUTDATED') .'</h2>');

if(!extension_loaded('PDO')) // Check if PHP Data Objects is available
    error('<h2>'. getLang('SQL_FUNCTION') .'</h2>');

// Connect to SQL using PDO
try {
    $sqldb = new PDO($sql['dsn'], $sql['user'], $sql['pass']);
} catch(PDOException $e) { // Catch connection error
    error('<h2>'. getLang('SQL_CONNECT') .'</h2>'. $e->getMessage());
}

// Initialise Database
$sqldb->query("
CREATE TABLE IF NOT EXISTS `". $sql['table'] ."` (
    `id`        int(16)                 unsigned NOT NULL AUTO_INCREMENT,
    `title`     varchar(255) COLLATE    utf8_bin NOT NULL,
    `name`      varchar(255) COLLATE    utf8_bin DEFAULT NULL,
    `trip`      varchar(255) COLLATE    utf8_bin DEFAULT NULL,
    `email`     varchar(255) COLLATE    utf8_bin DEFAULT NULL,
    `date`      int(11)                 unsigned NOT NULL,
    `content`   text COLLATE            utf8_bin NOT NULL,
    `password`  varchar(255) COLLATE    utf8_bin NOT NULL,
    `ip`        varchar(255) COLLATE    utf8_bin NOT NULL,
    `op`        tinyint(1)              unsigned NOT NULL DEFAULT '1',
    `tid`       int(16)                 unsigned NOT NULL DEFAULT '0',
    `locked`    tinyint(1)              unsigned NOT NULL DEFAULT '0',
    `ban`       tinyint(1)              unsigned NOT NULL DEFAULT '0',
    `lastreply` int(11)                 unsigned NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
");

// Session
session_start(); // Start a session
$auth = @$_SESSION['mod']; // Set an alias for mod

// Print the header
print nHead();

// Exit if board is set as closed in the config file
if($ninechan['closed']) {

    print '<h3>'. getLang('BOARD_CLOSED') .'</h3>'. getLang('REASON') .': '. getConfig('closedReason') .'';
    print nFoot();
    exit;

}

// Catch &t= and redirect it properly
if(isset($_GET['t'])) {

    // Get the url and replace &t= with &id=
    $newUrl = str_replace('t=', 'id=', $_SERVER['REQUEST_URI']);

    // Redirect and exit
    header('Location: '. $newUrl);
    print '<meta http-equiv="refresh" content="2; URL="'. $newUrl .'" />';
    exit;

}

// Check if the current IP is banned
if(checkBan($_SERVER['REMOTE_ADDR']))
    print '<div class="banmsg">'. getLang('USERBANNEDMSG') .'</div><hr />';

// Check if pass cookie is set and set it if it isn't
if(!isset($_COOKIE[getConfig('cookiePrefix') .'pass']))
    nCookie('pass', generatePassword());

// Check if view variable is set
if(isset($_GET['v'])) {

    // Switch to proper view
    switch($_GET['v']) {

        // Main index
        case 'index':
            // Section title
            print '<h2>'. getLang('THREADS') .'</h2>';

            // New thread link
            print '<h3><a href="'. $_SERVER['PHP_SELF'] .'?v=post">'. getLang('NEWTHREAD') .'</a></h3>';

            // Get threads
            $threads = array_chunk(getPosts(), getConfig('threadsPerPage'), true);

            // If at least one post was returned print the list
            if($threads) {

                print '<ol>';

                foreach($threads[(isset($_GET['p']) && ($_GET['p'] - 1) >= 0 && array_key_exists(($_GET['p'] - 1), $threads)) ? $_GET['p'] - 1 : 0] as $thread)
                    print '<li><a href="'. $_SERVER['PHP_SELF'] .'?v=thread&amp;id='. $thread['tid'] .'">'. $thread['title'] .'</a> <span style="font-size: x-small;">[<b title="'. date(getConfig('dateFormat'), $thread['lastreply']) .'">'. (count(getPosts($thread['tid'])) - 1) .'</b>]</span>';

                print '</ol>';

            } else // Else return EMPTY
                print '<h3>'. getLang('EMPTY') .'</h3>';

            // Pagination
            if(count($threads) > 1) {

                print '<h5 style="margin-bottom: 10px;">[';

                foreach($threads as $page => $pthreads)
                    print '<a href="?v=index&p='. ($page + 1) .'"> '. ($page + 1) .' </a>'. ($page == key(array_reverse($threads, true)) ? '' : '/');

                print ']</h5>';

            }

            // New thread link
            print '<h3><a href="'. $_SERVER['PHP_SELF'] .'?v=post">'. getLang('NEWTHREAD') .'</a></h3>';
            break;

        // Thread view
        case 'thread':
            // Just return NONEXISTENT if t is invalid
            if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {

                print('<h3>'. getLang('NONEXISTENT') .'</h3>');
                break;

            }

            // Strip all non-numeric characters from the string and assign it to $threadId
            $threadId = preg_replace('/\D/', '', $_GET['id']);

            // Get thread data
            $thread = getPosts($threadId);

            // Check if the thread exists
            if($thread) {

                // Print post title
                print '<h2>'. getLang('THREAD') .': '. $thread[0]['title'] .'</h2>';

                // Check if thread is locked and if true display message
                if($thread[0]['locked'])
                    print '<h3>'. getLang('LOCKED') .'</h3>';
                else
                    print '<h3><a href='. $_SERVER['PHP_SELF'] .'?v=post&amp;id='. $thread[0]['tid'] .'>'. getLang('NEWREPLY') .'</a></h3>';

                // Moderator tools
                if($auth == $ninechan['modPass']) {

                    print '<h6>';

                    // Purge button
                    print '[<a href="'. $_SERVER['PHP_SELF'] .'?v=mod&amp;del=purge&amp;id='. $thread[0]['tid'] .'">'. getLang('PURGE') .'</a>]'."\r\n";

                    if($thread[0]['locked'])
                        print '[<a href="'. $_SERVER['PHP_SELF'] .'?v=mod&amp;lock=false&amp;id='. $thread[0]['tid'] .'">'. getLang('UNLOCK') .'</a>]';
                    else
                        print '[<a href="'. $_SERVER['PHP_SELF'] .'?v=mod&amp;lock=true&amp;id='. $thread[0]['tid'] .'">'. getLang('LOCK') .'</a>]';

                    print '</h6>';

                }

                // Print every post
                foreach($thread as $post) {

                    // Set poster name
                    if(getConfig('forcedAnon')) {

                        $posterName = getConfig('anonName');
                        $posterTrip = null;

                    // Check if forced anon for mods or admins is enabled
                    } elseif((getConfig('modsAreAnon') === 1 && in_array($post['trip'], getConfig('modTrip'))) || (getConfig('adminsAreAnon') === 1 && in_array($post['trip'], getConfig('adminTrip')))) {

                        $posterName = getConfig('anonName');
                        $posterTrip = null;

                    // Check if forced trip anon for mods or admins is enabled
                    } elseif((getConfig('modsAreAnon') === 2 && in_array($post['trip'], getConfig('modTrip'))) || (getConfig('adminsAreAnon') === 2 && in_array($post['trip'], getConfig('adminTrip')))) {

                        $posterName = $post['name'];
                        $posterTrip = null;

                    } else {

                        // Check if name is set
                        $posterName = (empty($post['name']) ? getConfig('anonName') : $post['name']);

                        // Check if trip isset
                        $posterTrip = (empty($post['trip']) ? '' : ' <span class="trip">!'. $post['trip'] .'</span>');

                    }

                    print '<fieldset id="'. $post['id'] .'">';

                    print '<legend><b>'. $post['title'] .'</b> <a href="#'. $post['id'].'">'. getLang('BY') .'</a> <b>';

                    if(empty($post['email']))
                        print $posterName . $posterTrip;
                    else
                        print '<a href="mailto:'. $post['email'] .'">'. $posterName . $posterTrip .'</a>';

                    if(in_array($post['trip'], getConfig('adminTrip'))) // Check if tripcode is Admin
                        print ' <span class="admincap">## Admin</span>';
                    elseif(in_array($post['trip'], getConfig('modTrip'))) // Check if tripcode is Mod
                        print ' <span class="modcap">## Mod</span>';

                    print '</b></legend>';

                    // Parse BBcode and other things in the post content
                    print '<div class="postContent">'. parsePost($post['content']) .'</div><br />';

                    // Check if (USER WAS BANNED FOR THIS POST)
                    if($post['ban'])
                            print '<h6 class="ban">'. getLang('POSTBANNED') .'</h6>';

                    // Moderator tools
                    if($auth == $ninechan['modPass']) {

                        print '<h6>';
                        print '[<a href="'. $_SERVER['PHP_SELF'] .'?v=mod&amp;del=true&id='. $post['id'] .'&amp;id='. $post['tid'] .'">'. getLang('DELETE') .'</a>]'."\r\n";
                        print '[<a href="'. $_SERVER['PHP_SELF'] .'?v=mod&amp;ban='. ($post['ban'] ? 'false' : 'true') .'&amp;id='. $post['id'] .'&amp;id='. $post['tid'] .'">'. getLang($post['ban'] ? 'UNBAN' : 'BAN') .'</a>]'."\r\n";
                        print '[IP: '.base64_decode($post['ip']).']';
                        print '</h6>';

                    }

                    // Date and ID
                    print '<h6><i>'. date(getConfig('dateFormat'), $post['date']) .' <a href="#'. $post['id'] .'">No.</a> <a href="'. $_SERVER['PHP_SELF'] .'?v=post&amp;id='. $post['tid'] .'&amp;text=>>'. $post['id'] .'">'. $post['id'] .'</a> [<a href="'. $_SERVER['PHP_SELF'] .'?v=del&amp;id='. $post['id'] .'" title="'. getLang('DELPOST') .'">X</a>]</i></h6>';

                    print '</fieldset>';

                }

                // Moderator tools
                if($auth == $ninechan['modPass']) {

                    print '<h6>';

                    // Purge button
                    print '[<a href="'. $_SERVER['PHP_SELF'] .'?v=mod&amp;del=purge&amp;id='. $thread[0]['tid'] .'">'. getLang('PURGE') .'</a>]'."\r\n";

                    if($thread[0]['locked'])
                        print '[<a href="'. $_SERVER['PHP_SELF'] .'?v=mod&amp;lock=false&amp;id='. $thread[0]['tid'] .'">'. getLang('UNLOCK') .'</a>]';
                    else
                        print '[<a href="'. $_SERVER['PHP_SELF'] .'?v=mod&amp;lock=true&amp;id='. $thread[0]['tid'] .'">'. getLang('LOCK') .'</a>]';

                    print '</h6>';

                }

                // Check if thread is locked and if true display message
                if($thread[0]['locked'])
                    print '<h3>'. getLang('LOCKED') .'</h3>';
                else
                    print '<h3><a href="'. $_SERVER['PHP_SELF'] .'?v=post&amp;id='. $thread[0]['tid'] .'">'. getLang('NEWREPLY') .'</a></h3>';

            } else {

                // If not return NONEXISTENT and stop
                print getLang('NONEXISTENT');
                break;

            }
            break;

        // Posting
        case 'post':

            // Check if user is banned and if so don't display the form at all
            if(checkBan($_SERVER['REMOTE_ADDR'])) {
                print '<h2>'. getLang('USERBANNED') .'</h2>';
                break;
            }

            // Print "global" form elements
            print '<form method="post" action="'. $_SERVER['PHP_SELF'] .'?v=submit">';                    
            print '<table id="postForm" class="postForm">';

            // Predefine that we're creating a new thread
            $newThread = true;

            // Predefine that the thread isn't locked
            $locked = false;

            // Predefine $threadTitle to avoid E_WARNINGs
            $threadTitle = '';

            // Check if a thread ID is set
            if(isset($_GET['id'])) {

                // If so make sure it's numeric
                if(!is_numeric($_GET['id'])) {

                    print '<h2>'. getLang('NOTNUMERIC') .'</h2>';
                    print '<a href="'. $_SERVER['PHP_SELF'] .'?v=index">'. getLang('RETURN') .'</a>';
                    break;

                }

                // Strip non-numerical characters from the string
                $threadId = preg_replace('/\D/', '', $_GET['id']);

                // Get the thread data
                $thread = getPosts($threadId);

                if(!$thread) {
                    print '<h2>'. getLang('NONEXISTENT') .'</h2>';
                    print '<a href="'. $_SERVER['PHP_SELF'] .'?v=index">'. getLang('RETURN') .'</a>';
                    break;
                }

                // Reassign $thread
                $thread = $thread[0];

                // Since we're not creating a thread set newThread to false
                $newThread = false;

                // Reassign $locked
                $locked = $thread['locked'];

                // Don't display posting form if thread is locked
                if($locked) {

                    print '<h2>'. getLang('LOCKEDMSG') .'</h2>';
                    print '<meta http-equiv="refresh" content="2; URL="'. $_SERVER['PHP_SELF'] .'?v=thread&amp;id='. $thread['tid'] .'" />';

                } else {

                    print '<h2>'. getLang('RETO') .' '. $thread['title'] .' ['. $thread['tid'] .']</h2>';
                    print '<input type="hidden" name="tid" value="'. $thread['tid'] .'" />';

                    $threadTitle = 'Re: '. $thread['title'];

                }
            } else
                print '<h2>'. getLang('NEWTHREAD') .'</h2>';

            // Predefine the comment
            $comment = isset($_GET['text']) ? $_GET['text']."\r\n" : '';

            if(!$locked) { //-- Only display post page if thread isn't locked
                print '<tr><td>'. getLang('NAME') .'</td><td><input name="name" type="text" value="'. @$_COOKIE[getConfig('cookiePrefix') .'name'] .'" /></td></tr>';
                print '<tr><td>'. getLang('EMAIL') .'</td><td><input name="email" type="text" value="'. @$_COOKIE[getConfig('cookiePrefix') .'email'] .'" /></td></tr>';
                print '<tr><td>'. getLang('TITLE') .'</td><td><input name="title" type="text" value="'. $threadTitle .'" /></td></tr>';
                print '<tr><td>'. getLang('COMMENT') .'</td><td><textarea name="content" rows="6" cols="48">'. $comment .'</textarea></td></tr>';

                if(getConfig('reCaptcha')) { // Display reCaptcha if enabled in config

                    print '<tr><td>'. getLang('VERIFICATION') .'</td><td>
                    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
                    <div class="g-recaptcha" data-sitekey="'. getConfig('reCaptchaPublic') .'" style="margin: auto; display: inline-block;"></div>
                    <noscript>
                        <div style="width: 302px; height: 352px; margin: auto; display: inline-block;">
                            <div style="width: 302px; height: 352px; position: relative;">
                                <div style="width: 302px; height: 352px; position: absolute;">
                                    <iframe src="https://www.google.com/recaptcha/api/fallback?k='. getConfig('reCaptchaPublic') .'" frameborder="0" scrolling="no" style="width: 302px; height:352px; border-style: none;"></iframe>
                                </div>
                                <div style="width: 250px; height: 80px; position: absolute; border-style: none; bottom: 21px; left: 25px; margin: 0px; padding: 0px; right: 25px;">
                                <textarea id="g-recaptcha-response" name="g-recaptcha-response" class="g-recaptcha-response" style="width: 250px; height: 80px; border: 1px solid #c1c1c1; margin: 0px; padding: 0px; resize: none;" value=""></textarea>
                            </div>
                        </div>
                      </div>
                    </noscript></td></tr>';

                }

                print '<tr><td>'. getLang('PASSWORD') .'</td><td><input name="password" type="password" placeholder="'. getLang('PASSWORDCONTEXT') .'" value="'. @$_COOKIE[getConfig('cookiePrefix') .'pass'] .'" /> <input value="'. getLang('SUBMIT') .'" type="submit" /></td></tr>';
                print '</table></form>';

            }
            break;

        // Submitting posts
        case 'submit':

                // Check if IP banned
            if(checkBan($_SERVER['REMOTE_ADDR'])) {
                print '<h2>'. getLang('USERBANNED') .'</h2>';
                break;
            }

            // Check reCaptcha
            if(getConfig('reCaptcha')) {

                // Verify the captcha
                $reCaptcha = verifyCaptcha($_POST['g-recaptcha-response']);

                // If reCaptcha is invalid die and display error message
                if(!$reCaptcha['success']) {

                    print '<h2>'. getLang('INVALIDCAPTCHA') .'</h2><meta http-equiv="refresh" content="2; URL='. $_SERVER['PHP_SELF'] .'" />';
                    print nFoot();
                    exit;

                }

            }

            // Assign variables
            $submitData = [

                'title'     => cleanString($_POST['title']),
                'content'   => cleanString($_POST['content']),
                'name'      => ($_N_NAME = cleanString($_POST['name'])),
                'nameNT'    => (strlen(strstr($_N_NAME, "#", true)) ? strstr($_N_NAME, "#", true) : $_N_NAME),
                'trip'      => parseTrip($_POST['name']),
                'email'     => ($_N_MAIL = ($_POST['email'] == 'noko' ? null : cleanString($_POST['email']))),
                'date'      => time(),
                'password'  => md5(strlen($_POST['password']) ? $_POST['password'] : generatePassword()),
                'ip'        => base64_encode($_SERVER['REMOTE_ADDR']),
                'op'        => ($_N_OP = (isset($_POST['tid']) ? 0 : 1)),
                'id'        => ($_N_OP ? ($sqldb->query("SELECT MAX(tid) AS `tid` FROM `". $sql['table'] ."` LIMIT 1")->fetch()['tid'] + 1) : cleanString($_POST['tid'], true, true)),
                'noredir'   => ($_N_MAIL == 'noko' ? true : false)

            ];

            // Update last time replied
            if(isset($_POST['tid'])) {

                // Prepare the statement
                $updateLast = $sqldb->prepare("UPDATE `". $sql['table'] ."` SET `lastreply` = :lastreply WHERE `tid` = :tid AND `op` = 1");

                // Bind the parameters
                $updateLast->bindParam(':lastreply',    $submitData['date'],    PDO::PARAM_INT);
                $updateLast->bindParam(':tid',          $_POST['tid'],          PDO::PARAM_INT);

                // Execute statement
                $updateLast->execute();

            }

            // Assign cookies
            nCookie('name',     $submitData['name']);
            nCookie('email',    $submitData['email']);
            nCookie('pass',     $submitData['password']);

            // Check if title is too short
            if(empty($submitData['title']) || strlen($submitData['title']) < getConfig('titleMinLength')) {
                print '<h2>'. getLang('TITLETOOSHORT') .'</h2>';
                print '<meta http-equiv="refresh" content="2; URL='. $_SERVER['PHP_SELF'] .'" />';
                break;
            }

            // Check if title is too long
            if(strlen($submitData['title']) > getConfig('titleMaxLength')) {
                print '<h2>'. getLang('TITLETOOLONG') .'</h2>';
                print '<meta http-equiv="refresh" content="2; URL='. $_SERVER['PHP_SELF'] .'" />';
                break;
            }

            // Check if comment is too short
            if(empty($submitData['title']) || strlen($submitData['content']) < getConfig('commentMinLength')) {
                print '<h2>'. getLang('COMMENTTOOSHORT') .'</h2>';
                print '<meta http-equiv="refresh" content="2; URL='. $_SERVER['PHP_SELF'] .'" />';
                break;
            }

            // Check if comment is too long
            if(strlen($submitData['content']) > getConfig('commentMaxLength')) {
                print '<h2>'. getLang('COMMENTTOOLONG') .'</h2>';
                print '<meta http-equiv="refresh" content="2; URL='. $_SERVER['PHP_SELF'] .'" />';
                break;
            }

            $submitPost = $sqldb->prepare("INSERT INTO `". $sql['table'] ."` (`title`, `name`, `trip`, `email`, `date`, `content`, `password`, `ip`, `op`, `tid`, `lastreply`) VALUES (:title, :name, :trip, :email, :date, :content, :password, :ipaddr, :op, :threadid, :lastrep)");

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
            $submitPost->bindParam(':lastrep',  $submitData['date']);

            $submitPost->execute();

            print '<h1>'. getLang('POSTED') .'</h1>';

            print '<meta http-equiv="refresh" content="1; URL='. $_SERVER['PHP_SELF'] . ($submitData['noredir'] ? '?v=index' : '?v=thread&amp;id='. $submitData['id']) .'" />';
            break;

        case 'del':

            // Check if IP banned
            if(checkBan($_SERVER['REMOTE_ADDR'])) {
                print '<h2>'. getLang('USERBANNED') .'</h2>';
                break;
            }

            // Assign array to variable so we can store things in it later
            $deletionData = array();

            // If we're in _POST mode begin deletion preparation
            if(isset($_POST['id'])) {

                // Prepare statement to get the post's data
                $getPostData = $sqldb->prepare("SELECT * FROM `". $sql['table'] ." WHERE `id` = :pid LIMIT 1");

                // Bind the ID
                $getPostData->bindParam(':pid', $_POST['id'], PDO::PARAM_INT);

                // Execute the statement
                $getPostData->execute();

                // Fetch the data
                $getData = $getPostData->fetch();

                // Error if the post couldn't be found
                if(!count($getData))
                    error(getLang('NONEXISTENT') .'<meta http-equiv="refresh" content="0; url='. $_SERVER['PHP_SELF'] .'?v=index" />');

                // Check if the post is locked
                if($getData['locked']) {

                    print '<h2>'. getLang('LOCKEDMSGDEL') .'</h2>';
                    print '<meta http-equiv="refresh" content="2; URL='. $_SERVER['PHP_SELF'] .'?v=index" />';

                } else {

                    // Match the password
                    if($getData['password'] == md5($_POST['password'])) {

                        // Use the moderator function to delete the post
                        nMod('del', $getData['id'], true);
                        print '<h2>'. getLang('DEL_SUCCEED') .'</h2>';
                        print '<meta http-equiv="refresh" content="2; URL='. $_SERVER['PHP_SELF'] .'?v=index" />';

                    } else {

                        // Else display an error message
                        print '<h2>'. getLang('DEL_FAILED') .'</h2>';
                        print '<meta http-equiv="refresh" content="2; URL='. $_SERVER['PHP_SELF'] .'?v=index" />';

                    }

                }

            // Else enter _GET mode
            } elseif(isset($_GET['id'])) {

                // Check if the post exists
                if(!is_numeric($_GET['id']))
                    error(getLang('NONEXISTENT') .'<meta http-equiv="refresh" content="0; url='. $_SERVER['PHP_SELF'] .'?v=index" />');

                // Prepare statement to get post data
                $getPostData = $sqldb->prepare("SELECT * FROM `". $sql['table'] ." WHERE `id` = :pid LIMIT 1");

                // Bind ID
                $getPostData->bindParam(':pid', $_GET['id'], PDO::PARAM_INT);

                // Execute statement
                $getPostData->execute();

                // Fetch data
                $getData = $getPostData->fetch();

                // Error if the post couldn't be found
                if(!count($getData))
                    error(getLang('NONEXISTENT') .'<meta http-equiv="refresh" content="0; url='. $_SERVER['PHP_SELF'] .'?v=index" />');

                // Create form
                print '<form method="post" action="'. $_SERVER['PHP_SELF'] .'?v=del">';

                if($getData['locked']) {
                    print '<h2>'. getLang('LOCKEDMSGDEL') .'</h2>';
                    print '<meta http-equiv="refresh" content="2; URL='. $_SERVER['PHP_SELF'] .'?v=index" />';
                } else {
                    print '<h2>'. getLang('DELPOST') .' '. $getData['id'] .'</h2>';
                    print '<input type="hidden" name="id" value="'. $_GET['id'] .'" />';
                }

                print '<table id="postForm" class="postForm">';

                print '<tr><td>'. getLang('PASSWORD') .'</td>';
                print '<td><input name="password" type="password" placeholder="'. getLang('PASSWORDCONTEXT') .'" value="'.@$_COOKIE[getConfig('cookiePrefix') .'pass'].'" /> ';
                print '<input value="'. getLang('SUBMIT') .'" type="submit" /></td></tr>';

                print '</table>';
                print '</form>';

            }
            break;

        // Moderator Authentication
        case 'mod':
            if($auth == getConfig('modPass')) { // Check if authenticated

                // Page title
                print '<h2>'. getLang('MODLOGOUT') .'</h2>';

                if(isset($_POST['modkill'])) { // POST request modkill is set...

                    session_destroy(); // ...kill moderator session...
                    header('Location: '. $_SERVER['PHP_SELF'] .'?v=mod'); // ...and redirect to ?v=mod
                    print '<meta http-equiv="refresh" content="0; url='. $_SERVER['PHP_SELF'] .'?v=mod" />'; // fallback
                    exit;

                }

                // Print logout form
                print '<form method="post" action="'.$_SERVER['PHP_SELF'].'?v=mod">';
                print getLang('MODTOOLS') .'<br />';
                print '<input type="submit" value="'. getLang('LOGOUT') .'" name="modkill" />';
                print '</form>';

                // Ban handler
                if(isset($_GET['ban']) && isset($_GET['id']) && isset($_GET['id'])) {

                    if($_GET['ban'] == "true")
                        nMod('ban', $_GET['id'], true);
                    else
                        nMod('ban', $_GET['id'], false);

                    header('Location: '. $_SERVER['PHP_SELF'] .'?v=thread&amp;id='.$_GET['id']);
                    print '<meta http-equiv="refresh" content="0; url='. $_SERVER['PHP_SELF'] .'?v=thread&amp;id='.$_GET['id'].'" />'; // fallback
                    exit;

                }

                // Deletion handler
                if(isset($_GET['del']) && isset($_GET['id'])) {

                    if($_GET['del'] == "purge") {

                        nMod('prune', $_GET['id'], true);

                        header('Location: '. $_SERVER['PHP_SELF'] .'?v=index');
                        print '<meta http-equiv="refresh" content="0; url='. $_SERVER['PHP_SELF'] .'?v=index" />'; // fallback
                            exit;

                    } else {

                        if($_GET['del'] == "true")
                            nMod('del', $_GET['id'], true);
                        else
                            nMod('del', $_GET['id'], false);

                        header('Location: '. $_SERVER['PHP_SELF'] .'?v=thread&amp;id='.$_GET['id']);
                        print '<meta http-equiv="refresh" content="0; url='. $_SERVER['PHP_SELF'] .'?v=thread&amp;id='.$_GET['id'].'" />'; // fallback
                        exit;

                    }

                }

                // Lock handler
                if(isset($_GET['lock']) && isset($_GET['id'])) {

                    if($_GET['lock'] == "true")
                        nMod('lock', $_GET['id'], true);
                    else
                        nMod('lock', $_GET['id'], false);

                    header('Location: '. $_SERVER['PHP_SELF'] .'?v=thread&amp;id='.$_GET['id']);
                    print '<meta http-equiv="refresh" content="0; url='. $_SERVER['PHP_SELF'] .'?v=thread&amp;id='.$_GET['id'].'" />'; // fallback
                    exit;

                }

            } else {

                // Else display login screen
                if(isset($_POST['modPass'])) {

                    if($_POST['modPass'] == $ninechan['modPass'])
                            $_SESSION['mod'] = $ninechan['modPass'];

                        header('Location: '. $_SERVER['PHP_SELF'] .'?v=mod');
                        print '<meta http-equiv="refresh" content="0; url='. $_SERVER['PHP_SELF'] .'?v=mod" />'; // fallback
                        exit;

                    }

                    print '<h2>'. getLang('MODLOGIN') .'</h2>';
                    print '<form method="post" action="'. $_SERVER['PHP_SELF'] .'?v=mod">';
                    print '<input type="password" name="modPass" /><input type="submit" value="'. getLang('LOGIN') .'" />';
                    print '</form>';

            }
            break;

        // Default action
        default:
            header('Location: '. $_SERVER['PHP_SELF'] .'?v=index'); // If invalid option is set redirect to index
            print '<meta http-equiv="refresh" content="0; url='. $_SERVER['PHP_SELF'] .'?v=index" />'; // Fallback because I've had experiences where header() didn't work properly
            break;

    }

} else {

    header('Location: '. $_SERVER['PHP_SELF'] .'?v=index'); // If invalid option is set redirect to index
    print '<meta http-equiv="refresh" content="0; url='. $_SERVER['PHP_SELF'] .'?v=index" />'; // Fallback because I've had experiences where header() didn't work properly
    exit;

}

// Print footer
print nFoot();
