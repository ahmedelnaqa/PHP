<?php

$servername = "localhost";
$username = "infihfyb_urisu";
$password = "M7J-Ws1QmM[2";
$dbname = "infihfyb_risu";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "Connected successfully";

#mysqli_close($conn);


?>
