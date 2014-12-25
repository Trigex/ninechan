<?php
/**
	Ninechan Polish Translation
	By Kamil Rakowski <kamilrakowski1@interia.pl>
**/

// Meta //
define('LDATA_VERSION', '1.11alpha2'); // Version of ninechan language file is made for

// Board //
define('L_THREADS', 'Wątki'); // Threads
define('L_THREAD', 'Wątek'); // Thread
define('L_NEWTHREAD', 'Nowy Wątek'); // New Thread
define('L_NEWREPLY', 'Nowa Odpowiedz'); // New Reply
define('L_LOCKED', 'Zamknięty'); // Locked
define('L_BY', 'z'); // by

// Posting //
define('L_RETO', 'Odpowiedzi do'); // Reply to
define('L_TITLE', 'Tytuł'); // Title
define('L_NAME', 'Nazwa'); // Name
define('L_EMAIL', 'Email'); // Name
define('L_COMMENT', 'Komentarz'); // Comment
define('L_SUBMIT', 'Prześlij'); // Submit
define('L_VERIFICATION', 'Weryfikacja'); // Verification
define('L_PASSWORD', 'Hasło'); // Password
define('L_PASSWORDCONTEXT', 'Służy do usuwania postów'); // used for post deletion
define('L_DELPOST', 'Usun Post'); // Delete post

// Messages //
define('L_BOARD_CLOSED', $ninechan['title'].' forumy są teraz zamknięte.'); // Boards are closed
define('L_REASON', 'Powód'); // Reason
define('L_BANNED', 'Ty zostałeś zbanowany z tego forum.'); // Text displayed upon being banned
define('L_POSTBANNED', '(UŻYTKOWNIK ZOSTAŁ ZBANOWANY ZA TEN POST)'); // Text displayed under banned post
define('L_EMPTY', 'Istnieją nie ma wątków.'); // Text displayed when the board is empty
define('L_NONEXISTENT', 'Wątek nie istnieje.'); // Text displayed when the board is empty
define('L_LOCKEDMSG', 'Wątek który próbowano odpowiedzieć jest zamknięta.'); // Text displayed when the thread is locked and replying isn't possible
define('L_LOCKEDMSG_2', 'Twój post w wątku jest zablokowany i nie jest już możliwe jest usunięcie.'); // Text displayed when the thread is locked and deleting isn't possible
define('L_POSTED', 'Wysłany pomyślnie!'); // Text displayed when the post is successful
define('L_MODTOOLS', 'Narzędzia moderatorów powinien pojawić się obok postów.'); // Mod tools message
define('L_TITLETOOSHORT', 'Dany tytuł jest za krótki.'); // The given title is too short.
define('L_TITLETOOLONG', 'Dany tytuł jest za długi.'); // The given title is too long.
define('L_COMMENTTOOSHORT', 'Dany komentarz jest za krótki.'); // The given comment is too short.
define('L_COMMENTTOOLONG', 'Dany komentarz jest za długi.'); // The given comment is too long.

// Warnings and errors //
define('L_PHP_OUTDATED', 'Uaktualnij PHP do wersji 5.3 lub wyższej.'); // Outdated PHP version
define('L_SQL_FUNCTION', 'PDO nie jest włączona w twoje instalacji PHP.'); // SQL connect function does not exist
define('L_SQL_CONNECT', 'Błąd Połączenia SQL'); // Error while connecting to MySQL
define('L_INVALIDCAPTCHA', 'Weryfikacja nieudany!'); // Message displayed when captcha is wrong
define('L_DEL_SUCCEED', 'Usunięcie udane!'); // Message displayed when post is deleted
define('L_DEL_FAILED', 'Usunięcie nieudany!'); // Message displayed when post isn't deleted
define('L_USERBANNEDMSG', 'Są zbanowany od księgowania na tym forum.'); // Message displayed on top of the board when IP is banned
define('L_USERBANNED', 'Nie można ukończyć funkcji, ponieważ adres IP jest zbanowany.'); // Message displayed when trying to access a restricted page

// Moderator tools //
define('L_DELETE', 'Usuń'); // Delete button
define('L_PURGE', 'Oczyścić Wątek'); // Purge button
define('L_LOCK', 'Zamknij Wątek'); // Lock button
define('L_UNLOCK', 'Otwórz Wątek'); // Unlock button
define('L_BAN', 'Zbanuj'); // Ban button
define('L_UNBAN', 'Anuluj Zbanów'); // Unban button
define('L_MODLOGOUT', 'Moderatora Wyloguj'); // Moderator Logout
define('L_MODLOGIN', 'Moderatora Zaloguj'); // Moderator Login
define('L_LOGOUT', 'Wyloguj'); // Logout
define('L_LOGIN', 'Zaloguj'); // Login
