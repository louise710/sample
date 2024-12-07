
//for database connection
<?php
$host = "localhost";
$user = "sisig";
$password = "scs_department";
$database = "sisig";

$mysqli = mysqli_connect('localhost', 'sisig', 'scs_department', 'sisig');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
?>
