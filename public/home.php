<?php
require_once "header.php";
require_once __DIR__ . "/../db/connection.php";

session_start();
$user = $_SESSION["sessionUser"];

echo $user . "<--display besides dashboard profile picture?";

// map to allow different html page interactions to call php functions.
if (array_key_exists("addToCart", $_POST)) {
    add_to_cart($conn, $user, $_POST["itemId"]);
}

/*
Utilities for making transactions with the database.
*/
function add_to_cart(mysqli $conn, string $username, string $itemId) {
    // echo "<script>alert('added to cart!')</script>";
    
    $cart = $conn->query("SELECT cart FROM users WHERE username='$username'");
    // print_r($cart->fetch_assoc());
    $cartItems = json_decode($cart->fetch_assoc()["cart"], true);
    if (array_key_exists($itemId, $cartItems)) {
        echo "<script>alert('item already added to cart')</script>";
    } else {
        echo "<script>alert('added to cart')</script>";
    }
}

/*
Load store items.

NOTE: Ideally, this should be executed on page load.
*/
function display_items(mysqli $conn, string $table): void {
    $items = $conn->query("SELECT * FROM $table");
    foreach ($items->fetch_all(MYSQLI_ASSOC) as $row) {
        echo <<<ITEM
            <div class="card item-card">
                <img src="../pasabuy/static/item.png" class="card-img-top" alt="...">
                <div class="card-body">
                    <p class="card-text">{$row["name"]}</p>
                </div>
            </div>
            <form action="#" method="post">
                <input style="display: none" value="{$row["id"]}" name="itemId">
                <button type="submit" class="btn btn-warning" name="addToCart">ADD TO CART<i data-feather="shopping-cart"></i></button>
            </form>
        ITEM;
    }
    $items->free_result();
};
?>
<title>Dubai Pasabuy</title>

<div class="container">
    <h1>Pasabuy Today</h1>

    <!-- tools for navigating the dashboard -->
    <div class="btn-group btn-group-lg" role="group" aria-label="menu">
        <button type="button" class="btn btn-outline-dark" onclick="displayAll()">All</button>
        <button type="button" class="btn btn-outline-dark" onclick="displayCurrentItems()">on hand</button>
        <button type="button" class="btn btn-outline-dark" onclick="displayNextItems()">next batch</button>
    </div>
    <!-- /tools for navigating the dashboard -->

    <!-- on hand or next batch -->
    <section id="current-items" class="hidden-section"><?php display_items($conn, "current_items") ?></section>
    <section id="next-items" class="hidden-section"><?php display_items($conn, "next_items") ?></section>
    <!-- /on hand or next batch -->
</div>

<script>
if (!document.getElementById("current-items").style.display) {
    displayAll();
}

function displayAll() {
    document.getElementById("next-items").style.display = "none";
    document.getElementById("current-items").style.display = "none";
}

function displayCurrentItems() {
    document.getElementById("next-items").style.display = "none";
    document.getElementById("current-items").style.display = "unset";
}

function displayNextItems() {
    document.getElementById("current-items").style.display = "none"
    document.getElementById("next-items").style.display = "unset";
}
</script>
<script>
  feather.replace()
</script>
