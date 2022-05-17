<?php
require_once "header.php";
require_once "navbar.php";
require_once __DIR__ . "/../db/connection.php";
require_once __DIR__ . "/../utils/session.php";

$username = get_session_user();

// prepared stmts
$selectStmt = $conn->prepare("SELECT cart FROM users WHERE username=?");

// helper func to conver sql json to associative arr
function _json_to_assoc(mysqli_stmt $stmt, string $username) {
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $results = $stmt->get_result()->fetch_assoc();
    if ($results) return json_decode($results["cart"], true);
}

// display items in cart
function display_cart_items(mysqli $conn, string $username, mysqli_stmt $stmt) {
    $cartDec = _json_to_assoc($stmt, $username);

    if ($cartDec) {
        foreach ($cartDec as $itemId => $value) {
            $item = $conn->query("SELECT `name` FROM current_items WHERE id='$itemId'");
    
            if ($item->num_rows == 0) {
                $item = $conn->query("SELECT `name` FROM next_items WHERE id='$itemId'");
            }
    
            $itemName = $item->fetch_assoc()["name"];
            echo <<<CART_ITEMS
            <div class="d-flex">
                <div class="card item-card me-5">
                    <img src="static/img/item.png" class="img-thumbnail" alt="..." />
                </div>
                <div class="d-flex flex-column">
                    <p>$itemName</p>
                    <div class="d-flex flex-row">
                        <div class="btn-container">
                            <button class="btn decrease$itemId"><i data-feather="minus"></i></button>
                            <span id="value$itemId">{$cartDec[$itemId]}</span>
                            <button class="btn increase$itemId"><i data-feather="plus"></i></button>
                        </div>
                        <form method="post">
                            <input
                                id="input-value$itemId"
                                style="display: none"
                                value="$value"
                                name="itemQuantity"
                            />
                            <input style="display: none" value="$itemId" name="itemId"/>
                            <button type="submit" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Save amount">
                                <i data-feather="check"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
    
            <script>
            const value$itemId = document.querySelector("#value$itemId");
            const btn$itemId = document.querySelectorAll(".btn");
            let count$itemId = value$itemId.textContent;
            
            btn$itemId.forEach(function (btn$itemId) {
                btn$itemId.addEventListener("click", function (e) {
                    const styles = e.currentTarget.classList;
            
                    if (styles.contains("decrease$itemId")) {
                        if (count$itemId > 0) {
                            count$itemId--;
                        }
                    } else if (styles.contains("increase$itemId")) {
                        count$itemId++;
                    }
            
                    if (count$itemId > 0) {
                        value$itemId.style.color = "green";
                    }
                    // if (count$itemId < 0) {
                    //     value$itemId.style.color = "red";
                    // }
                    if (count$itemId === 0) {
                        value$itemId.style.color = "#222";
                    }
                    value$itemId.textContent = count$itemId;
                    document.getElementById("input-value$itemId").value = count$itemId;
              });
            });
            </script>
            CART_ITEMS;
        }
    }
}

// slider: quantity 
function set_item_quantity(mysqli $conn, string $username, string $itemId, string $quantity, mysqli_stmt $stmt) {
    $cartDec = _json_to_assoc($stmt, $username);
    $cartDec[$itemId] = $quantity;
    $cart = json_encode($cartDec);
    $updateStmt = $conn->prepare("UPDATE users SET cart=? WHERE username=?");
    $updateStmt->bind_param("ss", $cart, $username);
    $updateStmt->execute();
    echo "<script>alert('cart updated')</script>";
}

// checkout -> record to database
function checkout() {}

// remove -> delete from db
function remove_from_cart() {}

// map to allow different html page interactions to call php functions.
if (array_key_exists("itemQuantity", $_POST)) {
    set_item_quantity($conn, $username, $_POST["itemId"], $_POST["itemQuantity"], $selectStmt);
}

?>
<?php display_navbar() ?>
<div class="container">
    <h1>My Cart</h1>
    <div class="d-flex flex-column justify-content-center">
        <?php
        display_cart_items($conn, $username, $selectStmt);
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
