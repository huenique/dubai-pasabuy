<?php
require_once "../db/connection.php";

$conn = get_db_connection();

$query = "INSERT INTO current_products (id, name) VALUES ('" . substr(md5(rand()), 0, 7) . "', 'Perfume')";
$conn->query($query) or die($conn->error);

$query = "INSERT INTO next_products (id, name) VALUES ('" . substr(md5(rand()), 0, 7) . "', 'Cologne')";
$conn->query($query) or die($conn->error);
?>
