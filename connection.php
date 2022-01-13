<?php
define("DB_HOST", "localhost");
define("DB_NAME", "x7rain");
define("DB_USER", "root");
define("DB_PASS", "password");

function con(){
    try {
        $db_connection = new PDO('mysql:host='. DB_HOST .';dbname='. DB_NAME . ';charset=utf8', DB_USER, DB_PASS);
        return $db_connection;
    } catch (PDOException $e) {
        echo "Sorry, there was a problem connecting to the database." . $e->getMessage();
    }
}
?>