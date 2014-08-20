<?php
// Database Connection
$sql=array();
$sql['host']					= "localhost";				// SQL Host
$sql['user']					= "root";					// SQL Username
$sql['pass']					= "";						// SQL Password
$sql['data']					= "ninechan";				// SQL Database
$sql['table']					= "nine";					// SQL Table

// Board Settings
$ninechan=array();
$ninechan['cookieprefix']		= "nine_";									// Cookie Prefix (for if you have multiple boards and you want to keep the cookies apart).
$ninechan['cookiepath']			= "/";										// Path on the server for the cookies
$ninechan['cookielifetime']		= 604800;									// The time cookies should live
$ninechan['exposeerrors']		= false;									// Set if errors should be displayed
$ninechan['lang']				= "en";										// Set the language. Must match filename from file in /lang/ folder (excluding .php).
$ninechan['closed']				= false;									// Define if the board should be open(false) or closed(true)
$ninechan['closedreason']		= "Maintenance";							// Set the message displayed when the board is closed
$ninechan['title']				= "ninechan";								// Board title
$ninechan['charset']			= "UTF-8";									// Character set used by the board
$ninechan['desc']				= "best shitty board software";				// Specify the board description (set to null to disable)
$ninechan['styles']				= array("ninechan.css" => "ninechan blue", "ninechan2.css" => "ninechan red");	// Set the paths to the CSS styles and their names (set to null to disable styles), please refrain from using special characters in titles
$ninechan['showversion']		= false;									// Set if the version number should be displayed, false is recommended
$ninechan['sage']				= false;									// Specify whether the threads should "disappear" after a certain amount of new threads
$ninechan['sagelimit']			= 20;										// If ['sage'] is set to true, how many threads should be displayed
$ninechan['titleminlength']		= 5;										// Set the minimum character length of a title
$ninechan['titlemaxlength']		= 40;										// Set the maximum character length of a title
$ninechan['commentminlength']	= 5;										// Set the minimum character length of a comment
$ninechan['commentmaxlength']	= 3000;										// Set the maximum character length of a comment
$ninechan['modpass']			= "changethis";								// Specify the Mod Password for moderating posts
$ninechan['admintrip']			= array("first trip", "second trip");		// Specify the "encrypted" tripcodes of the admins (e.g. QphRmfeTkY)
$ninechan['modtrip']			= array("first trip", "second trip");		// Specify the "encrypted" tripcodes of mods (e.g. QphRmfeTkY)
$ninechan['anonname']			= "Anonymous";								// Specify the name used for Anonymous posters
$ninechan['adminsareanon']		= 0;										// Specify whether admins should be Anonymous (0. No, 1. Fully anon, 2. Only hide trip)
$ninechan['modsareanon']		= 0;										// Specify whether mods should be Anonymous (0. No, 1. Fully anon, 2. Only hide trip)
$ninechan['forcedanon']			= false;									// Specify whether anonymous posting is forced or not
$ninechan['dateFormat']			= 'D m/d/Y H:i T';							// Specify in what way dates and times should be ordered following PHP's date() function (default: D m/d/Y H:i T)
$ninechan['recaptcha']			= false;									// Set whether reCAPTCHA should be enabled (reCAPTCHA PHP Library required).
$ninechan['recaptchalib']		= "recaptcha.php";							// Path to the reCAPTCHA PHP Library (only required if reCAPTCHA is enabled)
$ninechan['recaptchapublic']	= "";										// reCAPTCHA Public key
$ninechan['recaptchaprivate']	= "";										// reCAPTCHA Private key
