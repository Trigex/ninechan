<?php
//// MySQL Database Connection ////
$mysql=array();
$mysql['host']="localhost"; // MySQL Host
$mysql['user']="root"; // MySQL Username
$mysql['pass']=""; // MySQL Password
$mysql['data']="ninechan"; // MySQL Database

//// Board Data ////
$ninechan=array();
$ninechan['closed'] = false; // Define whether the board should be open(false) or closed(true)
$ninechan['title'] = "ninechan"; // Specify the board title
$ninechan['desc'] = "ninechan best shitty board software"; // Specify the board description
$ninechan['styleenable'] = true; // Choose whether a CSS Stylesheet should be used or not
$ninechan['style'] = "ninechan.css"; // Specify the CSS (can be external)
$ninechan['showversion'] = true; // Specify whether the version number is shown or not
