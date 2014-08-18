<?php
//// Ninechan Official Dutch Language File ////
// Board //
define('L_THREADS', 'Threads'); // Threads
define('L_THREAD', 'Thread'); // Thread
define('L_NEWTHREAD', 'Nieuwe Thread'); // New Thread
define('L_NEWREPLY', 'Nieuwe Reactie'); // New Reply
define('L_LOCKED', 'Gesloten'); // Locked
define('L_BY', 'door'); // by

// Posting //
define('L_RETO', 'Reactie naar'); // Reply to
define('L_TITLE', 'Titel'); // Title
define('L_NAME', 'Naam'); // Name
define('L_EMAIL', 'Email'); // Name
define('L_COMMENT', 'Bericht'); // Comment
define('L_SUBMIT', 'Stuur'); // Submit
define('L_VERIFICATION', 'Verificatie'); // Verification
define('L_PASSWORD', 'Wachtwoord'); // Password
define('L_PASSWORDCONTEXT', 'gebruikt voor post verwijdering'); // used for post deletion
define('L_DELPOST', 'Verwijder post'); // Delete post

// Messages //
define('L_BOARD_CLOSED', 'Het '.$ninechan['title'].' boord is gesloten op het moment.'); // Boards are closed
define('L_REASON', 'Reden'); // Reason
define('L_BANNED', 'U bent verbannen van dit boord.'); // Text displayed upon being banned
define('L_POSTBANNED', '(GEBRUIKER WAS GEBAND VOOR DEZE POST)'); // Text displayed under banned post
define('L_EMPTY', 'Er zijn geen threads.'); // Text displayed when the board is empty
define('L_NONEXISTENT', 'Niet bestaande thread.'); // Text displayed when the board is empty
define('L_LOCKEDMSG', 'De thread waarop u probeert te reageren is gesloten.'); // Text displayed when the board is empty
define('L_LOCKEDMSG_2', 'De thread waarin uw post was is gelockt en u kunt uw post niet verwijderen.'); // Text displayed when the thread is locked and deleting isn't possible
define('L_POSTED', 'Gepost!'); // Text displayed when the post is successful
define('L_INVALIDTITLE', 'Foutieve title!'); // Text displayed when the name field is empty or invalid
define('L_NOCOMMENT', 'Geen bericht ingevoerd!'); // Text displayed when the comment field is empty or invalid
define('L_MODTOOLS', 'De moderator hulp middelen zouden nu bij de posts staan.'); // Mod tools message

// Warnings and errors //
define('L_PHP_OUTDATED', 'Upgrade uw PHP versie naar ten minste 5.3.'); // Outdated PHP version
define('L_SQL_FUNCTION', 'Uw PHP installatie mist de MySQLi plugin.'); // SQL connect function does not exist
define('L_SQL_CONNECT', 'SQL Verbindings Fout'); // Error while connecting to MySQL
define('L_UDB_EXISTS', 'updatedb.php bestaat, of u heeft het niet verwijdered of de unlink functie mislukte.'); // Display if updatedb.php exists
define('L_INVALIDCAPTCHA', 'Verification mislukt'); // Message displayed when captcha is wrong
define('L_DEL_SUCCEED', 'Successvol verwijdert!'); // Message displayed when post is deleted
define('L_DEL_FAILED', 'Verwijderen mislukt.'); // Message displayed when post isn't deleted

// Moderator tools //
define('L_DELETE', 'Verwijder'); // Delete button
define('L_PURGE', 'Verwijder Thread'); // Purge button
define('L_LOCK', 'Sluit Thread'); // Lock button
define('L_UNLOCK', 'Heropen Thread'); // Unlock button
define('L_BAN', 'Ban'); // Ban button
define('L_UNBAN', 'Ontban'); // Unban button
define('L_MODLOGOUT', 'Moderator Logout'); // Moderator Logout
define('L_MODLOGIN', 'Moderator Login'); // Moderator Login
define('L_LOGOUT', 'Uitloggen'); // Logout
define('L_LOGIN', 'Inloggen'); // Login
