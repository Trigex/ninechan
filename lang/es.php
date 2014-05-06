<?php
//// Ninechan Official Spanish Language File ////
// Board //
define('L_THREADS', 'Hilos'); // Threads
define('L_THREAD', 'Hilo'); // Thread
define('L_NEWTHREAD', 'Nuevo hilo'); // New Thread
define('L_NEWREPLY', 'Nueva respuesta'); // New Reply
define('L_LOCKED', 'Bloqueado'); // Locked
define('L_BY', 'por'); // by

// Posting //
define('L_RETO', 'Responder a'); // Reply to
define('L_TITLE', 'Título'); // Title
define('L_NAME', 'Nombre'); // Name
define('L_EMAIL', 'Email'); // Name
define('L_COMMENT', 'Comentario'); // Comment
define('L_SUBMIT', 'Enviar'); // Submit
define('L_VERIFICATION', 'Verificación'); // Verification
define('L_PASSWORD', 'Contraseña'); // Password
define('L_PASSWORDCONTEXT', 'utilizada para poder eliminar posts'); // used for post deletion
define('L_DELPOST', 'Eliminar post'); // Delete post

// Messages //
define('L_BOARD_CLOSED', 'Los foros '.$ninechan['title'].' están cerrados en estos momentos.'); // Boards are closed
define('L_REASON', 'Razón'); // Reason
define('L_BANNED', 'Has sido baneado de este foro.'); // Text displayed upon being banned
define('L_EMPTY', 'No hay hilos.'); // Text displayed when the board is empty
define('L_NONEXISTENT', 'No existe el hilo.'); // Text displayed when the board is empty
define('L_LOCKEDMSG', 'El hilo al que intentaste responder está bloqueado.'); // Text displayed when the thread is locked and replying isn't possible
define('L_LOCKEDMSG_2', 'No se pueden eliminar posts en hilos bloqueados.'); // Text displayed when the thread is locked and deleting isn't possible
define('L_POSTED', '¡Posteado!'); // Text displayed when the post is successful
define('L_INVALIDTITLE', '¡Título inválido!'); // Text displayed when the name field is empty or invalid
define('L_NOCOMMENT', '¡Comentario inválido!'); // Text displayed when the comment field is empty or invalid
define('L_MODTOOLS', 'Las herramientas de moderación deberían empezar a aparecer al lado de los posts.'); // Mod tools message

// Warnings and errors //
define('L_PHP_OUTDATED', 'Por favor, actualiza tu instalación de PHP a por lo menos 5.3 o superior.'); // Outdated PHP version
define('L_SQL_FUNCTION', 'Tu instalación de PHP no soporta MySQLi.'); // SQL connect function does not exist
define('L_SQL_CONNECT', 'Error de conexión SQL'); // Error while connecting to MySQL
define('L_UDB_EXISTS', 'updatedb.php existe; o no lo has borrado o la función unlink falló.'); // Display if updatedb.php exists
define('L_INVALIDCAPTCHA', 'La verificación falló'); // Message displayed when captcha is wrong
define('L_DEL_SUCCEED', '¡Eliminado satisfactoriamente!'); // Message displayed when post is deleted
define('L_DEL_FAILED', 'No se pudo eliminar.'); // Message displayed when post isn't deleted

// Moderator tools //
define('L_DELETE', 'Eliminar'); // Delete button
define('L_PURGE', 'Purgar hilo'); // Purge button
define('L_LOCK', 'Bloquear hilo'); // Lock button
define('L_UNLOCK', 'Desbloquear hilo'); // Unlock button
define('L_BAN', 'Banear'); // Ban button
define('L_UNBAN', 'Desbanear'); // Unban button
define('L_MODLOGOUT', 'Salir'); // Moderator Logout
define('L_MODLOGIN', 'Entrar como moderador'); // Moderator Login
define('L_LOGOUT', 'Salir'); // Logout
define('L_LOGIN', 'Entrar'); // Login
