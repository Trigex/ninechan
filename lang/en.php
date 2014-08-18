<?php
//// Ninechan Official English Language File ////
// Board //
define('L_THREADS', 'Threads'); // Threads
define('L_THREAD', 'Thread'); // Thread
define('L_NEWTHREAD', 'New Thread'); // New Thread
define('L_NEWREPLY', 'New Reply'); // New Reply
define('L_LOCKED', 'Locked'); // Locked
define('L_BY', 'by'); // by

// Posting //
define('L_RETO', 'Reply to'); // Reply to
define('L_TITLE', 'Title'); // Title
define('L_NAME', 'Name'); // Name
define('L_EMAIL', 'Email'); // Name
define('L_COMMENT', 'Comment'); // Comment
define('L_SUBMIT', 'Submit'); // Submit
define('L_VERIFICATION', 'Verification'); // Verification
define('L_PASSWORD', 'Password'); // Password
define('L_PASSWORDCONTEXT', 'used for post deletion'); // used for post deletion
define('L_DELPOST', 'Delete post'); // Delete post

// Messages //
define('L_BOARD_CLOSED', 'The '.$ninechan['title'].' boards are closed right now.'); // Boards are closed
define('L_REASON', 'Reason'); // Reason
define('L_BANNED', 'You have been banned from this board.'); // Text displayed upon being banned
define('L_POSTBANNED', '(USER WAS BANNED FOR THIS POST)'); // Text displayed under banned post
define('L_EMPTY', 'There are no threads.'); // Text displayed when the board is empty
define('L_NONEXISTENT', 'Non-existent thread.'); // Text displayed when the board is empty
define('L_LOCKEDMSG', 'The thread you\'re trying to reply to is locked.'); // Text displayed when the thread is locked and replying isn't possible
define('L_LOCKEDMSG_2', 'The thread your post was in is locked and deletion isn\'t possible.'); // Text displayed when the thread is locked and deleting isn't possible
define('L_POSTED', 'Posted!'); // Text displayed when the post is successful
define('L_MODTOOLS', 'The moderator tools should now appear next to posts.'); // Mod tools message
define('L_TITLETOOSHORT', 'The given title is too short.') // The given title is too short.
define('L_TITLETOOLONG', 'The given title is too long.') // The given title is too long.
define('L_COMMENTTOOSHORT', 'The given comment is too short.') // The given comment is too short.
define('L_COMMENTTOOLONG', 'The given comment is too long.') // The given comment is too long.

// Warnings and errors //
define('L_PHP_OUTDATED', 'Please upgrade your PHP installation to at least 5.3 or higher.'); // Outdated PHP version
define('L_SQL_FUNCTION', 'Your PHP installation does not support MySQLi.'); // SQL connect function does not exist
define('L_SQL_CONNECT', 'SQL Connection Error'); // Error while connecting to MySQL
define('L_UDB_EXISTS', 'updatedb.php exists, either you didn\'t remove it or the unlink function failed to.'); // Display if updatedb.php exists
define('L_INVALIDCAPTCHA', 'Verification failed'); // Message displayed when captcha is wrong
define('L_DEL_SUCCEED', 'Successfully deleted!'); // Message displayed when post is deleted
define('L_DEL_FAILED', 'Failed to deleted.'); // Message displayed when post isn't deleted

// Moderator tools //
define('L_DELETE', 'Delete'); // Delete button
define('L_PURGE', 'Purge Thread'); // Purge button
define('L_LOCK', 'Lock Thread'); // Lock button
define('L_UNLOCK', 'Unlock Thread'); // Unlock button
define('L_BAN', 'Ban'); // Ban button
define('L_UNBAN', 'Unban'); // Unban button
define('L_MODLOGOUT', 'Moderator Logout'); // Moderator Logout
define('L_MODLOGIN', 'Moderator Login'); // Moderator Login
define('L_LOGOUT', 'Logout'); // Logout
define('L_LOGIN', 'Login'); // Login
