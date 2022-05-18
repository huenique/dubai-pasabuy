<?php
require_once "header.php";
require_once "navbar.php";
require_once __DIR__ . "/../db/connection.php";
require_once __DIR__ . "/../utils/session.php";

$username = get_session_user();

// prepared stmts
$selectStmt = $conn->prepare("SELECT cart FROM customers WHERE username=?");

// helper func to conver sql json to associative arr
function _json_to_assoc(mysqli_stmt $stmt, string $username) {
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $results = $stmt->get_result()->fetch_assoc();
    if ($results) return json_decode($results["cart"], true);
}

// display products in cart
function display_cart_products(mysqli $conn, string $username, mysqli_stmt $stmt) {
    $cartDec = _json_to_assoc($stmt, $username);

    if ($cartDec) {
        foreach ($cartDec as $productId => $value) {
            $product = $conn->query("SELECT `name` FROM products WHERE id='$productId'");
    
            $productName = $product->fetch_assoc()["name"];
            echo <<<CART_ITEMS
            <div class="d-flex">
                <div class="card item-card me-5">
                    <img src="static/img/product.png" class="img-thumbnail" alt="..." />
                </div>
                <div class="d-flex flex-column">
                    <p>$productName</p>
                    <div class="d-flex flex-row">
                        <div class="btn-container">
                            <button class="btn decrease$productId"><i data-feather="minus"></i></button>
                            <span id="value$productId">{$cartDec[$productId]}</span>
                            <button class="btn increase$productId"><i data-feather="plus"></i></button>
                        </div>
                        <form method="post">
                            <input
                                id="input-value$productId"
                                style="display: none"
                                value="$value"
                                name="productQuantity"
                            />
                            <input style="display: none" value="$productId" name="productId"/>
                            <button type="submit" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Save amount">
                                <i data-feather="check"></i>
                            </button>
                        </form>
                    </div>
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
                        if (count$productId > 0) {
                            count$productId--;
                        }
                    } else if (styles.contains("increase$productId")) {
                        count$productId++;
                    }
            
                    if (count$productId > 0) {
                        value$productId.style.color = "green";
                    }
                    // if (count$productId < 0) {
                    //     value$productId.style.color = "red";
                    // }
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

// slider: quantity 
function set_product_quantity(mysqli $conn, string $username, string $productId, string $quantity, mysqli_stmt $stmt) {
    $cartDec = _json_to_assoc($stmt, $username);
    $cartDec[$productId] = $quantity;
    $cart = json_encode($cartDec);
    $updateStmt = $conn->prepare("UPDATE customers SET cart=? WHERE username=?");
    $updateStmt->bind_param("ss", $cart, $username);
    $updateStmt->execute();
    echo "<script>alert('cart updated')</script>";
}

// checkout -> record to database
function checkout() {}

// remove -> delete from db
function remove_from_cart() {}

// map to allow different html page interactions to call php functions.
if (array_key_exists("productQuantity", $_POST)) {
    set_product_quantity($conn, $username, $_POST["productId"], $_POST["productQuantity"], $selectStmt);
}

?>
<?php display_navbar() ?>
<div class="container">
    <h1>My Cart</h1>
    <div class="d-flex flex-column justify-content-center">
        <?php
        display_cart_products($conn, $username, $selectStmt);
        ?>
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
