<?php
// Define configuration arrays
$sql=array();$ninechan=array();

/// Database Connection ///
// PDO DSN http://php.net/manual/en/pdo.drivers.php
$sql['dsn'] = "mysql:host=localhost;dbname=ninechan;";

// Database Username
$sql['user']    = "ninechan";

// Database Password
$sql['pass']    = "";

// Database Table
$sql['table']   = "";


/// Board Settings ///
// Cookie Prefix (for if you have multiple boards and you want to keep the cookies apart).
$ninechan['cookiePrefix'] = "nine_";

// Path on the server for the cookies
$ninechan['cookiePath'] = "/";

// The time cookies should live
$ninechan['cookieLifetime'] = 604800;

// Set if errors should be displayed
$ninechan['exposeErrors'] = true;

// Set the language. Must match filename from file in /lang/ folder (excluding .php).
$ninechan['lang'] = "en";

// Define if the board should be open(false) or closed(true)
$ninechan['closed'] = false;

// Set the message displayed when the board is closed
$ninechan['closedReason'] = "Maintenance";

// Set if posting should be disable (true is disabled, false is enabled)
$ninechan['disablePosting'] = false;

// Set if the board should only be accessible after entering a password (null disables this feature)
$ninechan['boardPassword'] = null;

// Board title
$ninechan['title'] = "ninechan board";

// Character set used by the board
$ninechan['charset'] = "utf-8";

// Specify the board description (set to null to disable)
$ninechan['desc'] = "Live development board";

// Rewrite urls (requires mod_rewrite or compatible and commenting the section in .htaccess)
$ninechan['modRewrite'] = true;

// Set the paths to the CSS styles and their names (set to null to disable styles), please refrain from using special characters in titles
$ninechan['styles'] = array(
    "ninechan.css"  => "ninechan blue",
    "ninechan2.css" => "ninechan red"
);

// Set if the version number should be displayed, false is recommended
$ninechan['showVersion'] = true;

// Amount of threads that should be displayed on a page of the index
$ninechan['threadsPerPage'] = 20;

// Set the minimum character length of a title
$ninechan['titleMinLength'] = 5;

// Set the maximum character length of a title
$ninechan['titleMaxLength'] = 40;

// Set the minimum character length of a comment
$ninechan['commentMinLength'] = 5;

// Set the maximum character length of a comment
$ninechan['commentMaxLength'] = 3000;

// Specify the Mod Password for moderating posts
$ninechan['modPass'] = "change this if you don't want your board to get hacked";

// Specify the "encrypted" tripcodes of the admins (e.g. QphRmfeTkY)
$ninechan['adminTrip'] = array(
    "QphRmfeTkY"
);

// Specify the "encrypted" tripcodes of mods (e.g. QphRmfeTkY)
$ninechan['modTrip'] = array(
    "QphRmfeTkY"
);

// Specify the name used for Anonymous posters
$ninechan['anonName'] = "Anonymous";

// Specify whether admins should be Anonymous (0. No, 1. Fully anon, 2. Only hide trip)
$ninechan['adminsAreAnon'] = 0;

// Specify whether mods should be Anonymous (0. No, 1. Fully anon, 2. Only hide trip)
$ninechan['modsAreAnon'] = 0;

// Specify whether anonymous posting is forced or not
$ninechan['forcedAnon'] = false;

// Specify in what way dates and times should be ordered following PHP's date() function (default: D Y-m-d H:i T)
$ninechan['dateFormat'] = 'D Y-m-d H:i T';

// JSON file containing BBcodes
$ninechan['bbCodes'] = 'bbcodes.json';

// Set whether reCAPTCHA should be enabled (reCAPTCHA PHP Library required).
$ninechan['reCaptcha'] = true;

// reCAPTCHA Public key
$ninechan['reCaptchaPublic'] = "6LdQwAYTAAAAAKSW9Q7U6qS6HFTwotccCMr1Ejri";

// reCAPTCHA Private key
$ninechan['reCaptchaPrivate'] = "6LdQwAYTAAAAAJ3NUhUmFyc814B7AM1LGbVwWKp2";
