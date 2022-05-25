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

/**
 * Utilities for making transactions with the database.
 */
function add_to_cart(mysqli $conn, string $username, string $productId)
{
    $cartResult = $conn->query("SELECT cart FROM customers WHERE username='$username'");

    // The cart items are stored as a JSON object, hence the need to parse the query result.
    $cartDec = json_decode($cartResult->fetch_assoc()["cart"], true);

    if (!$cartDec) {
        $cartDec = array();
    }

    if (array_key_exists($productId, $cartDec)) {
        echo "<script>alert('product already added to cart')</script>";
    } else {
        $cartDec += [$productId => "1"];
        $cart = json_encode($cartDec);
        $updateStmt = $conn->prepare("UPDATE customers SET cart=? WHERE username=?");
        $updateStmt->bind_param("ss", $cart, $username);
        $updateStmt->execute();
        echo "<script>alert('added to cart')</script>";
    }
}

/**
 * Load store products.
 * NOTE: This should be called on page load for SPA effects.
 */
function display_store_products(mysqli_result $result): void
{
    foreach ($result->fetch_all(MYSQLI_ASSOC) as $item) {
        $media = $item["media"];
        $itemMedia = $media ? $media : "./static/img/product.png";
        echo <<<ITEM
        <div class="card item-card m-2">
            <img src="$itemMedia" class="card-img-top" alt="..." />
            <div class="card-body d-flex flex-column">
                <p class="card-text mt-auto">{$item["name"]}</p>
                <p class="card-text mt-auto">₱{$item["cost_php"]}</p>
                <form method="post">
                    <input class="input-default" value="{$item["id"]}" name="productId">
                    <button type="submit" class="btn btn-warning" name="addToCart">
                        ADD TO CART<i class="cart-ico ms-2 mb-1" data-feather="shopping-cart"></i>
                    </button>
                </form>
            </div>
        </div>
        ITEM;
    }
    $result->free_result();
};

function display_onhand(mysqli $conn)
{
    display_store_products($conn->query("SELECT * FROM products WHERE access='onhand'"));
}

function display_nextbatch(mysqli $conn)
{
    display_store_products($conn->query("SELECT * FROM products WHERE access='next'"));
}
?>
<style>
    .cart-ico {
        width: 18px;
        height: 18px;
    }
</style>
<title>Dubai Pasabuy</title>
<?php display_navbar()?>
<div class="container">
    <div class="my-3 d-flex">
        <h1 class="my-3">Pasabuy Today</h1>
        <div class="ms-auto"></div>
    </div>

    <!-- tools for navigating the dashboard -->
    <div class="btn-group btn-group-lg" role="group" aria-label="menu">
        <button class="btn btn-outline-dark" type="button" onclick="displayAll()">All</button>
        <button class="btn btn-outline-dark" type="button" onclick="displayCurrentItems()">on hand</button>
        <button class="btn btn-outline-dark" type="button" onclick="displayNextItems()">next batch</button>
    </div>
    <!-- /tools for navigating the dashboard -->

    <!-- on hand or next batch -->
    <div class="section-default" id="current-products" >
        <div class="d-flex align-content-start flex-wrap">
            <?php display_onhand($conn)?>
        </div>
    </div>
    <div class="section-default" id="next-products">
        <div class="d-flex align-content-start flex-wrap">
            <?php display_nextbatch($conn)?>
        </div>
    </div>
    <!-- /on hand or next batch -->
</div>

<script>
function resetDisplay() {
    let sections = document.querySelectorAll(".section-default");
    sections.forEach(section => {
        section.style.display = "none";
    });
}

function displayAll() {
    resetDisplay();
    document.getElementById("current-products").style.display = "unset";
    document.getElementById("next-products").style.display = "unset";
}

function displayCurrentItems() {
    resetDisplay();
    document.getElementById("current-products").style.display = "unset";
}

function displayNextItems() {
    resetDisplay();
    document.getElementById("next-products").style.display = "unset";
}
</script>
<script>
  feather.replace()
</script>
