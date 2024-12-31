<?php
session_start();
session_unset(); // Șterge toate variabilele de sesiune
session_destroy(); // Distruge sesiunea
header("Location: register.php"); // Redirecționează la formularul de autentificare
exit();
?>
