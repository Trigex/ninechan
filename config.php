<?php
//// SQL Database Connection ////
$sql=array();
$sql['host']="localhost"; // MySQL Host
$sql['user']="root"; // MySQL Username
$sql['pass']=""; // MySQL Password
$sql['data']="ninechan"; // MySQL Database
$sql['table']="posts"; // MySQL Table

//// Board Data ////
$ninechan=array();
$ninechan['cookieprefix'] = "nine_"; // Specify a prefix for the cookies (e.g. if you have multiple boards and you want to keep them apart).
$ninechan['cookiepath'] = "/"; // Specify the path on the server for the cookies
$ninechan['lang'] = "en"; // Define the language. Must match filename from file in /lang/ folder (excluding .php).
$ninechan['closed'] = false; // Define whether the board should be open(false) or closed(true)
$ninechan['closedreason'] = "Maintenance"; // Specify the message displayed when the board is closed
$ninechan['title'] = "ninechan"; // Specify the board title
$ninechan['charset'] = "UTF-8"; // Specify the board character set (e.g. UTF-8, Shift_JIS)
$ninechan['descenable'] = true; // Choose whether a description should be displayed or not
$ninechan['desc'] = "ninechan best shitty board software"; // Specify the board description
$ninechan['styleenable'] = true; // Choose whether a CSS Stylesheet should be used or not
$ninechan['defaultstyle'] = "ninechan.css"; // Select the default stylesheet
$ninechan['styles'] = array("ninechan.css","ninechan2.css"); // Specify the CSS stylesheets (can be external)
$ninechan['showversion'] = false; // Specify whether the version number is shown or not
$ninechan['sage'] = false; // Specify whether the threads should "disappear" after a certain amount of new threads
$ninechan['sagelimit'] = 20; // If ['sage'] is set to true, how many threads should be displayed
$ninechan['titlemaxlength'] = 40; // Set the maximum character length of a title
$ninechan['commentmaxlength'] = 3000; // Set the maximum character length of a comment
$ninechan['bantext'] = "(USER WAS BANNED FOR THIS POST)"; // Text displayed under the post of a banned users (if the post isn't deleted)
$ninechan['modpass'] = "changethis"; // Specify the Mod Password for moderating posts
$ninechan['admintrip'] = array("first trip","second trip"); // Specify the "encrypted" tripcodes of the admins (e.g. QphRmfeTkY)
$ninechan['modtrip'] = array("first trip","second trip"); // Specify the "encrypted" tripcodes of mods (e.g. QphRmfeTkY)
$ninechan['anonname'] = "Anonymous"; // Specify the name used for Anonymous posters
$ninechan['adminsareanon'] = 0; // Specify whether admins should be Anonymous (0. No, 1. Fully anon, 2. Only hide trip)
$ninechan['modsareanon'] = 0; // Specify whether mods should be Anonymous (0. No, 1. Fully anon, 2. Only hide trip)
$ninechan['forcedanon'] = false; // Specify whether anonymous posting is forced or not
$ninechan['dateFormat'] = 'D m/d/Y H:i T'; // Specify in what way dates and times should be ordered following PHP's date() function (default: D m/d/Y H:i T)
$ninechan['recaptcha'] = false; // Set whether reCAPTCHA should be enabled (reCAPTCHA PHP Library required).
$ninechan['recaptchalib'] = "recaptcha.php"; // Path to the reCAPTCHA PHP Library (only required if reCAPTCHA is enabled)
$ninechan['recaptchapublic'] = ""; // reCAPTCHA Public key
$ninechan['recaptchaprivate'] = ""; // reCAPTCHA Private key
