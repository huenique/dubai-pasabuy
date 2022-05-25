<?php
function get_db_connection()
{
    $cleardb_url = parse_url(getenv("CLEARDB_DATABASE_URL"));
    $cleardb_server = $cleardb_url["host"];
    $cleardb_username = $cleardb_url["user"];
    $cleardb_password = $cleardb_url["pass"];
    $cleardb_db = substr($cleardb_url["path"], 1);

    // During deployment, uncomment the line of codes below comment the ones above
    // Replace the values as needed

    // $cleardb_server = "127.0.0.1";
    // $cleardb_username = "root";
    // $cleardb_password = "";
    // $cleardb_db = "database_table_name";

    $conn = new mysqli($cleardb_server, $cleardb_username, $cleardb_password, $cleardb_db);

    if ($conn->connect_error) {
        echo $conn->connect_error;
        return null;
    } else {
        return $conn;
    }
}

$conn = get_db_connection();
