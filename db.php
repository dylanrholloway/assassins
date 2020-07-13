<?php 
require_once 'env.php';
//Connect to the database

try {
    $dbh = new PDO('mysql:host=35.213.155.151:3306;dbname='.$db_name, $user, $pass);
    // $dbh = null;
} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
}
?>