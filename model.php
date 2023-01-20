<?php

// Load the required libraries and namespaces
require_once 'vendor/autoload.php';

use Akeneo\Pim\ApiClient\AkeneoPimClientBuilder;

// Set the API client configuration options
// $clientBuilder = new AkeneoPimClientBuilder('https://zorang-e19b8cc18d.trial.akeneo.cloud/');
// $clientBuilder->setClientId('9_1wsysfycdduswcc0cgwokkwkc48kwos8kw04wo48w0goo40kgw');
// $clientBuilder->setClientSecret('acnknsqf9uwo88w4kg4cgog8scco8ocokw0kcks8sgkkgsg48');
// $clientBuilder->setUsername('zorangbigcommerceconnect_8674');
// $clientBuilder->setPassword('0ab963dac');
$clientBuilder = new \Akeneo\Pim\ApiClient\AkeneoPimClientBuilder('https://zorang-e19b8cc18d.trial.akeneo.cloud/');
$client = $clientBuilder->buildAuthenticatedByPassword('9_1wsysfycdduswcc0cgwokkwkc48kwos8kw04wo48w0goo40kgw', 'acnknsqf9uwo88w4kg4cgog8scco8ocokw0kcks8sgkkgsg48', 'zorangbigcommerceconnect_8674', '0ab963dac');

// Build the Akeneo PIM API client
// $client = $clientBuilder->build();
$options = [
    'verify' => false
];

$clientBuilder->setHttpClientOptions($options);

// Get the Product Model from Akeneo
$product = $client->getProductApi()->get('my-product-code');

// Get the variant information (e.g. size and color) for the product
$variantInformation = $product['values'];

// Access the size and color attributes of the product
$size = $variantInformation['size'][0]['data'];
$color = $variantInformation['color'][0]['data'];

echo "Size: $size\n";
echo "Color: $color\n";

?>
