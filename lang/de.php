<?php
// Ninechan german translation file by berserkingyadis <berserkingyadis@gmail.com>
// Board //
define('L_THREADS', 'Threads'); // Threads
define('L_THREAD', 'Thread'); // Thread
define('L_NEWTHREAD', 'Neuer Thread'); // New Thread
define('L_NEWREPLY', 'Neuer Reply'); // New Reply
define('L_LOCKED', 'Gesperrt'); // Locked
define('L_BY', 'von'); // by

// Posting //
define('L_RETO', 'Antworten auf'); // Reply to
define('L_TITLE', 'Titel'); // Title
define('L_NAME', 'Name'); // Name
define('L_EMAIL', 'Email'); // Name
define('L_COMMENT', 'Kommentar'); // Comment
define('L_SUBMIT', 'Abschicken'); // Submit
define('L_VERIFICATION', 'Verifikation'); // Verification
define('L_PASSWORD', 'Passwort'); // Password
define('L_PASSWORDCONTEXT', 'zum post löschen'); // used for post deletion
define('L_DELPOST', 'Post löschen'); // Delete post

// Messages //
define('L_BOARD_CLOSED', 'Die '.$ninechan['title'].' boards sind momentan geschlossen.'); // Boards are closed
define('L_REASON', 'Grund'); // Reason
define('L_BANNED', 'Du wurdest von diesem board gebannt.'); // Text displayed upon being banned
define('L_POSTBANNED', '(USER WURDE FÜR DIESEN POST GEBANNT)'); // Text displayed under banned post
define('L_EMPTY', 'Hier gibt es noch keine Threads.'); // Text displayed when the board is empty
define('L_NONEXISTENT', 'Nicht existerender Thread.'); // Text displayed when the board is empty
define('L_LOCKEDMSG', 'Reply nicht möglich, da der Thread gesperrt ist'); // Text displayed when the thread is locked and replying isn't possible
define('L_LOCKEDMSG_2', 'Löschen nicht möglich, da der Thread gesperrt ist'); // Text displayed when the thread is locked and deleting isn't possible
define('L_POSTED', 'Geposted!'); // Text displayed when the post is successful
define('L_MODTOOLS', 'Die Moderator Tools sollten nun neben den Posts angezeigt werden.'); // Mod tools message
define('L_TITLETOOSHORT', 'Dieser Titel ist zu kurz.'); // The given title is too short.
define('L_TITLETOOLONG', 'Dieser Titel ist zu lang.'); // The given title is too long.
define('L_COMMENTTOOSHORT', 'Dieser Kommentar ist zu kurz.'); // The given comment is too short.
define('L_COMMENTTOOLONG', 'Dieser Kommentar ist zu lang.'); // The given comment is too long.

// Warnings and errors //
define('L_PHP_OUTDATED', 'Deine PHP-Installation ist out of date. Bitte installiere mindestens PHP 5.3.'); // Outdated PHP version
define('L_SQL_FUNCTION', 'Deine PHP-Installation unterstützt kein MSQLi.'); // SQL connect function does not exist
define('L_SQL_CONNECT', 'SQL Connection - Fehler'); // Error while connecting to MySQL
define('L_UDB_EXISTS', 'updatedb.php ist vorhanden, es wurde entweder nicht gelöscht oder die unlink-funktion konnte es nicht.'); // Display if updatedb.php exists
define('L_INVALIDCAPTCHA', 'Falsches Captcha.'); // Message displayed when captcha is wrong
define('L_DEL_SUCCEED', 'Löschen erfolgreich!'); // Message displayed when post is deleted
define('L_DEL_FAILED', 'Löschen fehlgeschlagen.'); // Message displayed when post isn't deleted
define('L_USERBANNEDMSG', 'Du kannst in diesem Board nichts posten, da du gebannt bist.'); // Message displayed on top of the board when IP is banned
define('L_USERBANNED', 'Deine IP ist gebannt.'); // Message displayed when trying to access a restricted page

// Moderator tools //
define('L_DELETE', 'Löschen'); // Delete button
define('L_PURGE', 'Thread löschen'); // Purge button
define('L_LOCK', 'Thread sperren'); // Lock button
define('L_UNLOCK', 'Thread entsperren'); // Unlock button
define('L_BAN', 'Bannen'); // Ban button
define('L_UNBAN', 'Entbannen'); // Unban button
define('L_MODLOGOUT', 'Moderator Logout'); // Moderator Logout
define('L_MODLOGIN', 'Moderator Login'); // Moderator Login
define('L_LOGOUT', 'Logout'); // Logout
define('L_LOGIN', 'Login'); // Login
