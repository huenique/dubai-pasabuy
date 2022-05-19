<?php

require_once "header.php";
require_once "navbar.php";
require_once __DIR__ . "/../utils/session.php";
require_once __DIR__ . "/../vendor/autoload.php";

$_ = get_session_user();

display_navbar();

/** Generate a dynamic configuration for Adyen payment session. */
function generate_config() {
   $clientKey = getenv("AYDEN_CLIENT_KEY");
   echo <<<CONFIG
   <link rel="stylesheet"
      href="https://checkoutshopper-live.adyen.com/checkoutshopper/sdk/5.15.0/adyen.css"
      integrity="sha384-Dm1w8jaVOWA8rxpzkxA41DIyw5VlBjpoMTPfAijlfepYGgLKF+hke3NKeU/KTX7t"
      crossorigin="anonymous">

   <div class="container mt-5 pt-5">
      <h3 id="page-title"></h3>
      <div class="w-25 mt-5" id="gcash-container"></div>
   </div>

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
      const _pageTitle = document.getElementById("page-title").innerText = "Please choose a payment option";
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

if (isset($_GET["checkout"])) {
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
}
?>
<title>My Cart – Checkout</title>
