<?php
function get_db_connection() {
    $conn = new mysqli("localhost", "root", "", "dubai_pasabuy");

    if ($err = $conn->connect_error) {
        die($err);
    } else {
        return $conn; 
    }
}

$conn = get_db_connection();
?>
