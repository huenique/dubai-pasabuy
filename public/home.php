<?php
require_once "header.php";
require_once "navbar.php";
require_once __DIR__ . "/../db/connection.php";
require_once __DIR__ . "/../utils/session.php";

$user = get_session_user();

// map to allow different html page interactions to call php functions.
if (array_key_exists("addToCart", $_POST)) {
    add_to_cart($conn, $user, $_POST["productId"]);
}

/*
Utilities for making transactions with the database.
*/
function add_to_cart(mysqli $conn, string $username, string $productId) {
    $cartResult = $conn->query("SELECT cart FROM customers WHERE username='$username'");
    $cartDec = json_decode($cartResult->fetch_assoc()["cart"], true);

    if (!$cartDec)
        $cartDec = array();

    if (array_key_exists($productId, $cartDec)) {
        echo "<script>alert('product already added to cart')</script>";
    } else {
        $cartDec += [$productId => "0"];
        $cart = json_encode($cartDec);
        $updateStmt = $conn->prepare("UPDATE customers SET cart=? WHERE username=?");
        $updateStmt->bind_param("ss", $cart, $username);
        $updateStmt->execute();
        echo "<script>alert('added to cart')</script>";
    }
}

/*
Load store products.

NOTE: This should be called on page load for SPA effects.
*/
function display_products(mysqli_result $result): void {
    foreach ($result->fetch_all(MYSQLI_ASSOC) as $row) {
        echo <<<ITEM
        <div class="card item-card">
            <img src="./static/img/product.png" class="card-img-top" alt="..." />
            <div class="card-body">
                <p class="card-text">{$row["name"]}</p>
            </div>
        </div>
        <form method="post">
            <input style="display: none" value="{$row["id"]}" name="productId">
            <button type="submit" class="btn btn-warning" name="addToCart">
                ADD TO CART<i data-feather="shopping-cart"></i>
            </button>
        </form>
        ITEM;
    }
    $result->free_result();
};

function display_onhand(mysqli $conn) {
    display_products($conn->query("SELECT * FROM products WHERE access='onhand'"));
}

function display_nextbatch(mysqli $conn) {
    display_products($conn->query("SELECT * FROM products WHERE access='next'"));
}
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
    <section id="current-products" class="hidden-section"><?php display_onhand($conn) ?></section>
    <section id="next-products" class="hidden-section"><?php display_nextbatch($conn) ?></section>
    <!-- /on hand or next batch -->
</div>

<script>
if (!document.getElementById("current-products").style.display) {
    displayAll();
}

function displayAll() {
    document.getElementById("next-products").style.display = "none";
    document.getElementById("current-products").style.display = "none";
}

function displayCurrentItems() {
    document.getElementById("next-products").style.display = "none";
    document.getElementById("current-products").style.display = "unset";
}

function displayNextItems() {
    document.getElementById("current-products").style.display = "none"
    document.getElementById("next-products").style.display = "unset";
}
</script>
<script>
  feather.replace()
</script>
