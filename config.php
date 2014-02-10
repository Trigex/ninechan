<?php
//// MySQL Database Connection ////
$mysql=array();
$mysql['host']="localhost"; // MySQL Host
$mysql['user']="root"; // MySQL Username
$mysql['pass']=""; // MySQL Password
$mysql['data']="ninechan"; // MySQL Database
$mysql['table']="posts"; // MySQL Table

//// Board Data ////
$ninechan=array();
$ninechan['closed'] = false; // Define whether the board should be open(false) or closed(true)
$ninechan['closedreason'] = "Maintenance"; // Specify the message displayed when the board is closed
$ninechan['title'] = "ninechan"; // Specify the board title
$ninechan['desc'] = "ninechan best shitty board software"; // Specify the board description
$ninechan['styleenable'] = true; // Choose whether a CSS Stylesheet should be used or not
$ninechan['style'] = "ninechan.css"; // Specify the CSS (can be external)
$ninechan['showversion'] = true; // Specify whether the version number is shown or not
$ninechan['sage'] = false; // Specify whether the threads should "disappear" after a certain amount of new threads
$ninechan['sagelimit'] = 20; // If ['sage'] is set to true, how many threads should be displayed
$ninechan['titleminlength'] = 3; // Set the minimum character length of a title
$ninechan['commentminlength'] = 6; // Set the minimum character length of a comment
$ninechan['bantext'] = "(USER WAS BANNED FOR THIS POST)"; // Text displayed under the post of a banned users (if the post isn't deleted)
$ninechan['modpass'] = "changethis"; // Specify the Mod Password for moderating posts
