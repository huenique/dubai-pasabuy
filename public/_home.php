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
        <div class="box">
            <img src="$itemMedia" alt="">
            <h3>{$item["name"]}</h3>
            <h2>₱{$item["cost_php"]}</h2>
            <form method="post">
                <input class="input-default" value="{$item["id"]}" name="productId">
                <button type="submit" class="btn" name="addToCart">
                    Add To Cart<i class="cart-ico ms-2 mb-1" data-feather="shopping-cart"></i>
                </button>
            </form>
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
<title>Home</title>

<!-- header section starts  -->
<?php display_navbar();?>
<div class="w3-content" style="max-width:800px">
    <img class="mySlides" src="static/img/pastle.png" style="width:100%;">
    <img class="mySlides" src="static/img/vic.png" style="width:100%;">
    <img class="mySlides" src="static/img/sweet.png" style="width:100%;">
    <img class="mySlides" src="static/img/perfumes.png" style="width:100%;">
</div>
<div class="w3-center">
    <button class="w3-button demo" onclick="currentDiv(1)">1</button>
    <button class="w3-button demo" onclick="currentDiv(2)">2</button>
    <button class="w3-button demo" onclick="currentDiv(3)">3</button>
    <button class="w3-button demo" onclick="currentDiv(4)">4</button>
</div>
<!-- home section ends -->
<!-- features section starts  -->
<section class="features" id="onhand">
    <h1 class="heading"> ON <span style="background-color: #68A7AD;">HAND</span>
    </h1>
    <div class="box-container">
        <?php display_onhand($conn);?>
    </div>
</section>

<!-- products section ends -->
<!-- categories section starts  -->
<section class="categories" id="nextbatch">
    <h1 class="heading"> Next <span style="background-color: #68A7AD; text-transform: uppercase;">Batch </span>
    </h1>
    <div class="box-container">
        <?php display_nextbatch($conn);?>
    </div>
</section>
<script>
    let slideIndex = 1;
    showDivs(slideIndex);

    function plusDivs(n) {
        showDivs(slideIndex += n);
    }

    function currentDiv(n) {
        showDivs(slideIndex = n);
    }

    function showDivs(n) {
        let i;
        let x = document.getElementsByClassName("mySlides");
        let dots = document.getElementsByClassName("demo");

        if (n > x.length) {
            slideIndex = 1
        }

        if (n < 1) {
            slideIndex = x.length
        }

        for (i = 0; i < x.length; i++) {
            x[i].style.display = "none";
        }

        for (i = 0; i < dots.length; i++) {
            dots[i].className = dots[i].className.replace(" w3-red", "");
        }

        x[slideIndex - 1].style.display = "block";
        dots[slideIndex - 1].className += " w3-red";
    }
</script>
<script>
    const searchForm = document.querySelector('.search-form');
    document.querySelector('#search-btn').onclick = () => {
    	searchForm.classList.toggle('active');
    	shoppingCart.classList.remove('active');
    	loginForm.classList.remove('active');
    	navbar.classList.remove('active');
    }
    let navbar = document.querySelector('.navbar');
    document.querySelector('#menu-btn').onclick = () => {
    	navbar.classList.toggle('active');
    	searchForm.classList.remove('active');
    	shoppingCart.classList.remove('active');
    	loginForm.classList.remove('active');
    }
    window.onscroll = () => {
    	searchForm.classList.remove('active');
    	shoppingCart.classList.remove('active');
    	loginForm.classList.remove('active');
    	navbar.classList.remove('active');
    }
    let swiper = new Swiper(".product-slider", {
    	loop: true,
    	spaceBetween: 20,
    	autoplay: {
    		delay: 7500,
    		disableOnInteraction: false,
    	},
    	centeredSlides: true,
    	breakpoints: {
    		0: {
    			slidesPerView: 1,
    		},
    		768: {
    			slidesPerView: 2,
    		},
    		1020: {
    			slidesPerView: 3,
    		},
    	},
    });
    let swiper = new Swiper(".review-slider", {
    	loop: true,
    	spaceBetween: 20,
    	autoplay: {
    		delay: 7500,
    		disableOnInteraction: false,
    	},
    	centeredSlides: true,
    	breakpoints: {
    		0: {
    			slidesPerView: 1,
    		},
    		768: {
    			slidesPerView: 2,
    		},
    		1020: {
    			slidesPerView: 3,
    		},
    	},
    });
</script>
