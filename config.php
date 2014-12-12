<?php
// Define configuration arrays
$sql = array();
$ninechan = array();

// Database Connection
$sql['dsn']						= "mysql:host=localhost; dbname=ninechan;";	// PDO DSN
$sql['user']					= "root";									// Database Username
$sql['pass']					= "";										// Database Password
$sql['table']					= "nine";									// Database Table
$sql['host']					= "localhost";				// SQL Host [about to be deprecated]
$sql['data']					= "ninechan";				// SQL Database [about to be deprecated]

// Board Settings
$ninechan['cookiePrefix']		= "nine_";									// Cookie Prefix (for if you have multiple boards and you want to keep the cookies apart).
$ninechan['cookiePath']			= "/";										// Path on the server for the cookies
$ninechan['cookieLifetime']		= 604800;									// The time cookies should live
$ninechan['exposeErrors']		= false;									// Display errors
$ninechan['lang']				= "en";										// Set the language. Must match filename from file in /lang/ folder (excluding .php).
$ninechan['closed']				= false;									// open(false) or close(true) the board
$ninechan['closedReason']		= "Maintenance";							// Set the message displayed when the board is closed
$ninechan['disablePosting']		= true;										// Turn posting on or off
$ninechan['title']				= "ninechan";								// Board title
$ninechan['charset']			= "UTF-8";									// Character set used by the board
$ninechan['desc']				= "best shitty board software";				// Specify the board description (set to null to disable)
$ninechan['styles']				= array(									// Paths to the CSS styles and their names (set to null to disable styles)
									"ninechan.css" => "ninechan blue",
									"ninechan2.css" => "ninechan red"
								);
$ninechan['showVersion']		= false;									// Display version number in the footer, false is recommended
$ninechan['sage']				= false;									// Should threads "disappear" after a certain amount of new threads (true = yes/false = no)
$ninechan['sageLimit']			= 20;										// If "sage" is set to true, how many threads should be displayed
$ninechan['titleMinLength']		= 5;										// Minimum character length of a title
$ninechan['titleMaxLength']		= 40;										// Maximum character length of a title
$ninechan['commentMinLength']	= 5;										// Minimum character length of a comment
$ninechan['commentMaxLength']	= 3000;										// Maximum character length of a comment
$ninechan['modPass']			= "changethis";								// Password to the moderation panel
$ninechan['adminTrip']			= array(									// Hashed tripcodes of the admins (e.g. QphRmfeTkY)
									"first trip",
									"second trip"
								);
$ninechan['modTrip']			= array(									// Hashed tripcodes of moderators (e.g. QphRmfeTkY)
									"first trip",
									"second trip"
								);
$ninechan['anonName']			= "Anonymous";								// Name used for Anonymous posters
$ninechan['adminsAreAnon']		= 0;										// Should Admins be Anonymous (0. No, 1. Fully anon, 2. Only hide trip)
$ninechan['modsAreAnon']		= 0;										// Should Mods be Anonymous (0. No, 1. Fully anon, 2. Only hide trip)
$ninechan['forcedAnon']			= false;									// Force Anonymous posting?
$ninechan['dateFormat']			= 'D Y-m-d H:i T';							// Date and time formatting, should be ordered following the PHP date() function (default: D Y-m-d H:i T)
$ninechan['reCaptcha']			= false;									// Enable or disable reCAPTCHA (reCAPTCHA PHP Library required)
$ninechan['reCaptchaLib']		= "recaptcha.php";							// Path to the reCAPTCHA PHP Library (only required if reCAPTCHA is enabled)
$ninechan['reCaptchaPublic']	= "";										// reCAPTCHA Public key
$ninechan['reCaptchaPrivate']	= "";										// reCAPTCHA Private key
