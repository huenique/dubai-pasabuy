<?php
require_once "header.php";
require_once "navbar.php";
require_once __DIR__ . "/../db/connection.php";
require_once __DIR__ . "/../utils/session.php";

$user = get_session_user();

echo $user . "<--display besides dashboard profile picture?";

// map to allow different html page interactions to call php functions.
if (array_key_exists("addToCart", $_POST)) {
    add_to_cart($conn, $user, $_POST["itemId"]);
}

/*
Utilities for making transactions with the database.
*/
function add_to_cart(mysqli $conn, string $username, string $itemId) {
    $cartResult = $conn->query("SELECT cart FROM users WHERE username='$username'");
    $cartDec = json_decode($cartResult->fetch_assoc()["cart"], true);

    if (!$cartDec)
        $cartDec = array();

    if (array_key_exists($itemId, $cartDec)) {
        echo "<script>alert('item already added to cart')</script>";
    } else {
        $cartDec += [$itemId => "0"];
        $cart = json_encode($cartDec);
        $updateStmt = $conn->prepare("UPDATE users SET cart=? WHERE username=?");
        $updateStmt->bind_param("ss", $cart, $username);
        $updateStmt->execute();
        echo "<script>alert('added to cart')</script>";
    }
}

/*
Load store items.

NOTE: This should be called on page load for SPA effects.
*/
function display_items(mysqli $conn, string $table): void {
    $items = $conn->query("SELECT * FROM $table");
    foreach ($items->fetch_all(MYSQLI_ASSOC) as $row) {
        echo <<<ITEM
        <div class="card item-card">
            <img src="./static/img/item.png" class="card-img-top" alt="..." />
            <div class="card-body">
                <p class="card-text">{$row["name"]}</p>
            </div>
        </div>
        <form method="post">
            <input style="display: none" value="{$row["id"]}" name="itemId">
            <button type="submit" class="btn btn-warning" name="addToCart">
                ADD TO CART<i data-feather="shopping-cart"></i>
            </button>
        </form>
        ITEM;
    }
    $items->free_result();
};
?>
<title>Dubai Pasabuy</title>
<?php display_navbar() ?>
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
