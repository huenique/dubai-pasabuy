<?php
require_once "path.php";

// echo join_paths(dirname(__DIR__, 1), "uploads");
echo dirname($_SERVER['REQUEST_URI']);
