<?php

require_once "header.php";
require_once "navbar.php";
require_once __DIR__ . "/../db/connection.php";
require_once __DIR__ . "/../utils/session.php";
require_once __DIR__ . "/../vendor/autoload.php";

$username = get_session_user();

/** Generate a dynamic configuration for Adyen payment session. */
function generate_config()
{
    $clientKey = getenv("AYDEN_CLIENT_KEY");
    echo <<<CONFIG
   <link rel="stylesheet"
      href="https://checkoutshopper-live.adyen.com/checkoutshopper/sdk/5.15.0/adyen.css"
      integrity="sha384-Dm1w8jaVOWA8rxpzkxA41DIyw5VlBjpoMTPfAijlfepYGgLKF+hke3NKeU/KTX7t"
      crossorigin="anonymous">

   <script src="https://checkoutshopper-live.adyen.com/checkoutshopper/sdk/5.15.0/adyen.js"
      integrity="sha384-vMZOl6V83EY2UXaXsPUxH5Pt5VpyLeHpSFnANBVjcH5l7yZmJO0QBl3s6XbKwjiN"
      crossorigin="anonymous"></script>

   <script>
   const configuration = {
      environment: 'test',
      clientKey: "$clientKey",
      session: {
         id: "{$_SESSION["adyen_session_id"]}", // Unique identifier for the payment session.
         sessionData: "{$_SESSION["adyen_session_data"]}" // The payment session data.
      },
      onPaymentCompleted: (result, component) => {
            console.info(result, component);
      },
      onError: (error, component) => {
            console.error(error.name, error.message, error.stack, component);
      },

      // Any payment method specific configuration.
      // Find the configuration specific to each payment method:  https://docs.adyen.com/payment-methods
      // For example, this is 3D Secure configuration for cards:
      paymentMethodsConfiguration: {
         card: {
            hasHolderName: true,
            holderNameRequired: true,
            billingAddressRequired: true
         }
      }
   };

   async function createAdyenInstance(configuration) {
      // Create an instance of AdyenCheckout using the configuration object.
      const checkout = await AdyenCheckout(configuration);
      // Access the available payment methods for the session.
      console.log(checkout.paymentMethodsResponse); // => { paymentMethods: [...], storedPaymentMethods: [...] }

      // Create an instance of the Component and mount it to the container you created.

      const _gcashComponent = checkout.create('gcash').mount('#gcash-container');
   }

   function resolveAsyncFn(arg) {
      return new Promise(resolve => {
         setTimeout(() => {
            createAdyenInstance(arg);
         }, 0);
      });
   }

   resolveAsyncFn(configuration)
   </script>
   CONFIG;
}

function get_cart_cost(mysqli $conn, string $username): void
{
    $merchandiseSubtotal = 0;
    $shippingFeeSubtotal = 50;
    $orderAmount = 0;
    $totalItems = 0;

    // Fetch customer cart
    $stmt = $conn->prepare("SELECT cart FROM customers WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $results = $stmt->get_result()->fetch_assoc();

    if ($results) {
        $cart = json_decode($results["cart"], true);
    } else {
        $cart = array();
    }

    if (!$cart) {
        header("Location: cart");
    }

    foreach ($cart as $productId => $amount) {
        $product = $conn->query("SELECT `name`,cost_php FROM products WHERE id='$productId'")->fetch_assoc();
        $merchandiseSubtotal += $product["cost_php"] * $amount;
        $totalItems += $amount;
    }

    $orderAmount = $merchandiseSubtotal + $shippingFeeSubtotal;

    echo <<<SUMMARY
    <div class="my-5">
        <div class="d-flex flex-row">
            <p class="flex-grow-1 fw-bold">Merchandise Subtotal ($totalItems)</p>
            <p class="fw-bold">₱$merchandiseSubtotal</p>
        </div>
        <div class="d-flex flex-row">
            <p class="flex-grow-1 fw-bold">Shipping Fee Subtotal</p>
            <p class="fw-bold">₱$shippingFeeSubtotal</p>
        </div>
        <div class="d-flex flex-row border-top border-dark">
            <p class="flex-grow-1 fw-bold mt-2">Order Amount</p>
            <p class="fw-bold mt-2">₱$orderAmount</p>
        </div>
    </div>
    SUMMARY;
}

if (isset($_GET["checkout"])) {
    try {
        // Set up the client as a singleton resource
        $client = new \Adyen\Client();
        $client->setXApiKey(getenv("AYDEN_API_KEY"));
        $client->setEnvironment(\Adyen\Environment::TEST);
        $client->setTimeout(30);

        // Create a payment resource
        // We will receive the payment outcome asynchronously, in an AUTHORISATION webhook.
        $service = new \Adyen\Service\Checkout($client);
        $json = '{
            "merchantAccount": "MSEUFAccountECOM",
            "amount": {
                "value": 100,
                "currency": "PHP"
            },
            "returnUrl": "http://dubai-pasabuy.heroku.com/checkout?order=randstr",
            "reference": "PAYMENT_REFERENCE",
            "countryCode": "PH"
        }';
        $params = json_decode($json, true);
        $result = $service->sessions($params);

        $_SESSION["adyen_session_id"] = $result["id"];
        $_SESSION["adyen_session_data"] = $result["sessionData"];

        generate_config();
        header("Location: cart");
    } catch (Exception $err) {}
}

if (isset($_POST["pay"])) {
    // get customer id
    $result = $conn->query("SELECT id,cart FROM customers WHERE username='" . $_SESSION["sessionUser"] . "'");
    $customer = $result->fetch_assoc();
    $customerId = $customer["id"];
    $items = array();
    $collection = array();
    $fee = 50;
    $delivered = false;
    $paid = true;

    foreach (json_decode($customer["cart"], true) as $productId => $amount) {
        $product = $conn->query("SELECT `name`,cost_php FROM products WHERE id='$productId'")->fetch_assoc();
        $itemCostPhp = $product["cost_php"];
        $productName = $product ? $product["name"] : "";

        array_push($items, array($productName => array("Cost: " . $itemCostPhp, "Amount: " . $amount)));
        array_push($collection, $product ? $itemCostPhp * $amount : null);
    }

    $total = array_sum($collection);
    $totalCollection = $total + $fee;
    $orderedItems = json_encode($items);

    $query = <<<STMT
    INSERT INTO orders (customer_id,item,pasabuy_fee,total_value,total_collection,delivered,paid)
    VALUES (?,?,?,?,?,?,?)
    STMT;
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isdddbb", $customerId, $orderedItems, $fee, $total, $totalCollection, $delivered, $paid);
    $stmt->execute();
}
?>
<title>My Cart – Checkout</title>
<?php display_navbar();?>
<div class="container mt-5 pt-5">
    <?php get_cart_cost($conn, $username);?>
    <h3 id="">Select Payment Method</h3>
    <div class="w-25 mt-5" id="gcash-container"></div>
    <form method="post">
        <input class="input-default" name="pay">
        <button type="submit" class="btn btn-primary">
            <i class="feather-default me-2" data-feather="credit-card"></i>
            Previous Payment Information
        </button>
    </form>
</div>

<script>
    feather.replace();
</script>