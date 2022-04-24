<?php

$conn = get_db_connection();

function get_db_connection() {
    $conn = new mysqli("localhost", "root", "", "pasabuy");

    if ($err = $conn->connect_error) {
        die($err);
    } else {
        return $conn; 
    }
}
?>
