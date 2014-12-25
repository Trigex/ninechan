<?php
/*
  Semi-Correct Indonesian translation file for ninechan
  Flappyzor - flappy@goat.si
*/

// Meta //
define('LDATA_VERSION', '1.11alpha2'); // Version of ninechan language file is made for

// Board //
define('L_THREADS', 'Threads'); // Threads
define('L_THREAD', 'Thread'); // Thread
define('L_NEWTHREAD', 'Thread Baru'); // New Thread
define('L_NEWREPLY', 'Membalas Baru'); // New Reply
define('L_LOCKED', 'Terkunci'); // Locked
define('L_BY', 'oleh'); // by

// Posting //
define('L_RETO', 'Membalas Ke'); // Reply to
define('L_TITLE', 'Judul'); // Title
define('L_NAME', 'Nama'); // Name
define('L_EMAIL', 'Email'); // Name
define('L_COMMENT', 'Komentar'); // Comment
define('L_SUBMIT', 'Menyerahkan'); // Submit
define('L_VERIFICATION', 'Verifikasi'); // Verification
define('L_PASSWORD', 'Password'); // Password
define('L_PASSWORDCONTEXT', 'digunakan untuk posting penghapusan'); // used for post deletion
define('L_DELPOST', 'Hapus pos'); // Delete post

// Messages //
define('L_BOARD_CLOSED', 'Papan '.$ninechan['title'].' sedang ditutup.'); // Boards are closed
define('L_REASON', 'Alasan'); // Reason
define('L_BANNED', 'Anda telah dilarang dari forum ini.'); // Text displayed upon being banned
define('L_POSTBANNED', '(PENGGUNA ADALAH DILARANG UNTUK POST INI)'); // Text displayed under banned post
define('L_EMPTY', 'Tidak ada threads.'); // Text displayed when the board is empty
define('L_NONEXISTENT', 'Tidak ada thread.'); // Text displayed when the board is empty
define('L_LOCKEDMSG', 'Thread Anda mencoba untuk membalas terkunci.'); // Text displayed when the thread is locked and replying isn't possible
define('L_LOCKEDMSG_2', 'Thread posting Anda di terkunci dan penghapusan tidak mungkin.'); // Text displayed when the thread is locked and deleting isn't possible
define('L_POSTED', 'Menyampaikan!'); // Text displayed when the post is successful
define('L_MODTOOLS', 'Alat moderator seharusnya sekarang muncul di samping tulisan.'); // Mod tools message
define('L_TITLETOOSHORT', 'Judul yang diberikan terlalu singkat.'); // The given title is too short.
define('L_TITLETOOLONG', 'Judul yang diberikan terlalu panjang.'); // The given title is too long.
define('L_COMMENTTOOSHORT', 'Komentar yang diberikan terlalu singkat.'); // The given comment is too short.
define('L_COMMENTTOOLONG', 'Komentar yang diberikan terlalu panjang.'); // The given comment is too long.

// Warnings and errors //
define('L_PHP_OUTDATED', 'Silakan meng-upgrade instalasi PHP Anda untuk setidaknya 5,3 atau lebih tinggi.'); // Outdated PHP version
define('L_SQL_FUNCTION', 'Instalasi PHP Anda tidak mendukung PDO.'); // SQL connect function does not exist
define('L_SQL_CONNECT', 'Kesalahan Connection SQL'); // Error while connecting to MySQL
define('L_INVALIDCAPTCHA', 'Verifikasi gagal'); // Message displayed when captcha is wrong
define('L_DEL_SUCCEED', 'Berhasil dihapus!'); // Message displayed when post is deleted
define('L_DEL_FAILED', 'Gagal menghapus.'); // Message displayed when post isn't deleted
define('L_USERBANNEDMSG', 'Anda dilarang posting di forum ini.'); // Message displayed on top of the board when IP is banned
define('L_USERBANNED', 'Tidak bisa menyelesaikan tindakan ini karena IP dilarang.'); // Message displayed when trying to access a restricted page

// Moderator tools //
define('L_DELETE', 'Hapus'); // Delete button
define('L_PURGE', 'Bersihkan Thread'); // Purge button
define('L_LOCK', 'Lock Thread'); // Lock button
define('L_UNLOCK', 'Membuka Thread'); // Unlock button
define('L_BAN', 'Departemen'); // Ban button
define('L_UNBAN', 'Unban'); // Unban button
define('L_MODLOGOUT', 'Moderator Logout'); // Moderator Logout
define('L_MODLOGIN', 'Moderator Login'); // Moderator Login
define('L_LOGOUT', 'Logout'); // Logout
define('L_LOGIN', 'Login'); // Login
