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
$ninechan['cookiePrefix']		= "nine_";									// Cookie Prefix (for if you have multiple boards and you want to keep the cookies apart).
$ninechan['cookiePath']			= "/";										// Path on the server for the cookies
$ninechan['cookieLifetime']		= 604800;									// The time cookies should live
$ninechan['exposeErrors']		= false;									// Set if errors should be displayed
$ninechan['lang']				= "en";										// Set the language. Must match filename from file in /lang/ folder (excluding .php).
$ninechan['closed']				= false;									// Define if the board should be open(false) or closed(true)
$ninechan['closedReason']		= "Maintenance";							// Set the message displayed when the board is closed
$ninechan['disablePosting']		= true;										// Turn posting on or off
$ninechan['title']				= "ninechan";								// Board title
$ninechan['charset']			= "UTF-8";									// Character set used by the board
$ninechan['desc']				= "best shitty board software";				// Specify the board description (set to null to disable)
$ninechan['styles']				= array("ninechan.css" => "ninechan blue", "ninechan2.css" => "ninechan red");	// Set the paths to the CSS styles and their names (set to null to disable styles), please refrain from using special characters in titles
$ninechan['showVersion']		= false;									// Set if the version number should be displayed, false is recommended
$ninechan['sage']				= false;									// Specify whether the threads should "disappear" after a certain amount of new threads
$ninechan['sageLimit']			= 20;										// If ['sage'] is set to true, how many threads should be displayed
$ninechan['titleMinLength']		= 5;										// Set the minimum character length of a title
$ninechan['titleMaxLength']		= 40;										// Set the maximum character length of a title
$ninechan['commentMinLength']	= 5;										// Set the minimum character length of a comment
$ninechan['commentMaxLength']	= 3000;										// Set the maximum character length of a comment
$ninechan['modPass']			= "changethis";								// Specify the Mod Password for moderating posts
$ninechan['adminTrip']			= array("first trip", "second trip");		// Specify the "encrypted" tripcodes of the admins (e.g. QphRmfeTkY)
$ninechan['modTrip']			= array("first trip", "second trip");		// Specify the "encrypted" tripcodes of mods (e.g. QphRmfeTkY)
$ninechan['anonName']			= "Anonymous";								// Specify the name used for Anonymous posters
$ninechan['adminsAreAnon']		= 0;										// Specify whether admins should be Anonymous (0. No, 1. Fully anon, 2. Only hide trip)
$ninechan['modsAreAnon']		= 0;										// Specify whether mods should be Anonymous (0. No, 1. Fully anon, 2. Only hide trip)
$ninechan['forcedAnon']			= false;									// Specify whether anonymous posting is forced or not
$ninechan['dateFormat']			= 'D Y-m-d H:i T';							// Specify in what way dates and times should be ordered following PHP's date() function (default: D Y-m-d H:i T)
$ninechan['reCaptcha']			= false;									// Set whether reCAPTCHA should be enabled (reCAPTCHA PHP Library required).
$ninechan['reCaptchaLib']		= "recaptcha.php";							// Path to the reCAPTCHA PHP Library (only required if reCAPTCHA is enabled)
$ninechan['reCaptchaPublic']	= "";										// reCAPTCHA Public key
$ninechan['reCaptchaPrivate']	= "";										// reCAPTCHA Private key
