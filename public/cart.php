<?php

require_once "header.php";
require_once "navbar.php";
require_once __DIR__ . "/../db/connection.php";
require_once __DIR__ . "/../utils/session.php";

$username = get_session_user();

/** Private helper func to conver sql json to arr. */
function json_to_assoc(mysqli $conn, string $username)
{
    $stmt = $conn->prepare("SELECT cart FROM customers WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $results = $stmt->get_result()->fetch_assoc();
    if ($results) {
        return json_decode($results["cart"], true);
    }

}

/** Display user added products to cart. */
function display_cart_products(mysqli $conn, string $username)
{
    $cartDec = json_to_assoc($conn, $username);

    if ($cartDec) {
        foreach ($cartDec as $productId => $amount) {
            $product = $conn->query("SELECT `name`,cost_php,media FROM products WHERE id='$productId'")->fetch_assoc();
            $productName = $product ? $product["name"] : "";
            $productImage = $product["media"] ? $product["media"] : "static/img/product.png";
            $productCostPhp = $product ? number_format($product["cost_php"] * $amount, 2) : "";

            echo <<<CART_ITEMS
            <div class="mt-2 d-flex">
                <div class="card item-card me-5">
                    <img src="$productImage" class="card-img-top" alt="..." />
                </div>
                <div class="d-flex flex-column">
                    <h5>$productName</h5>
                    <p><b>Cost: ₱$productCostPhp</b><p>
                </div>
                <div class="ms-auto align-items-center d-flex flex-row">
                    <div class="btn-container">
                        <button type="button" class="btn decrease$productId"><i data-feather="minus"></i></button>
                        <span id="value$productId">{$cartDec[$productId]}</span>
                        <button type="button" class="btn increase$productId"><i data-feather="plus"></i></button>
                    </div>
                    <form method="post">
                        <input
                            class="input-default"
                            id="input-value$productId"
                            value="$amount"
                            name="productQuantity"
                        />
                        <input class="input-default" value="$productId" name="productId"/>
                        <button type="submit" class="btn btn-primary mx-1" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Save amount">
                            <i data-feather="check"></i>
                        </button>
                    </form>
                    <form method="post">
                        <input class="input-default" value="$productId" name="removeFromCart"/>
                        <button type="submit" class="btn btn-warning mx-1" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Remove from cart">
                            <i data-feather="trash-2"></i>
                        </button>
                    </form>
                </div>
            </div>

            <script>
            const value$productId = document.querySelector("#value$productId");
            const btn$productId = document.querySelectorAll(".btn");
            let count$productId = value$productId.textContent;

            btn$productId.forEach(function (btn$productId) {
                btn$productId.addEventListener("click", function (e) {
                    const styles = e.currentTarget.classList;

                    if (styles.contains("decrease$productId")) {
                        if (count$productId > 1) {
                            count$productId--;
                        }
                    } else if (styles.contains("increase$productId")) {
                        count$productId++;
                    }

                    if (count$productId > 1) {
                        value$productId.style.color = "green";
                    }

                    if (count$productId === 0) {
                        value$productId.style.color = "#222";
                    }
                    value$productId.textContent = count$productId;
                    document.getElementById("input-value$productId").value = count$productId;
              });
            });
            </script>
            CART_ITEMS;
        }
    }
}

/** Update the cart item quantity. */
function set_product_quantity(mysqli $conn, string $username, string $productId, string $quantity)
{
    $cartDec = json_to_assoc($conn, $username);
    $cartDec[$productId] = $quantity;
    $cart = json_encode($cartDec);
    $updateStmt = $conn->prepare("UPDATE customers SET cart=? WHERE username=?");
    $updateStmt->bind_param("ss", $cart, $username);
    $updateStmt->execute();
    echo "<script>alert('cart updated')</script>";
}

function remove_cart_item(mysqli $conn, string $username, string $productId)
{
    $cartDec = json_to_assoc($conn, $username);
    unset($cartDec["$productId"]);
    $cart = json_encode($cartDec);
    $updateStmt = $conn->prepare("UPDATE customers SET cart=? WHERE username=?");
    $updateStmt->bind_param("ss", $cart, $username);
    $updateStmt->execute();
    echo "<script>alert('cart updated')</script>";
}

/** Remove specified item from cart. */
function remove_from_cart()
{}

// map to allow different html page interactions to call php functions.
if (array_key_exists("productQuantity", $_POST)) {
    set_product_quantity($conn, $username, $_POST["productId"], $_POST["productQuantity"]);
} elseif (array_key_exists("removeFromCart", $_POST)) {
    remove_cart_item($conn, $username, $_POST["removeFromCart"]);
}

?>
<title>My Cart</title>
<?php display_navbar()?>
<div class="container">
    <div class="my-3 d-flex">
        <h1 class="my-3">My Cart</h1>
        <div class="ms-auto">
            <form action="checkout" method="get">
                <input class="input-default" name="checkout" id="checkout"></input>
                <label for="checkout"></label>
                <button type="submit" class="btn btn-info btn-lg" style="margin-top: 10%">Checkout</button>
            </form>
        </div>
    </div>
    <div class="d-flex flex-column justify-content-center">
        <?php display_cart_products($conn, $username);?>
    </div>

</div>

<script>
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
    })
</script>
<script>
    feather.replace()
</script>
