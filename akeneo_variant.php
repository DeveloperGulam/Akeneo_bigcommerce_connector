<?php
require 'vendor/autoload.php';
// use GuzzleHttp\Client;

// Replace these values with your own API credentials
$akeneo_base_url = 'https://zorang-e19b8cc18d.trial.akeneo.cloud/';
$akeneo_client_id = '9_1wsysfycdduswcc0cgwokkwkc48kwos8kw04wo48w0goo40kgw';
$akeneo_client_secret = 'acnknsqf9uwo88w4kg4cgog8scco8ocokw0kcks8sgkkgsg48';
$akeneo_username = 'zorangbigcommerceconnect_8674';
$akeneo_password = '0ab963dac';

$bigcommerce_base_url = 'https://api.bigcommerce.com/stores/';
$bigcommerce_client_id = 'mlbegxbemjdy3a8y9xcqfxuqdtv0p62';
$bigcommerce_access_token = 'rybm5p5owweopk6ryvfc75vovtny0tl';
$bigcommerce_store_hash = 'n2e1yp5rcg';

// Use Guzzle HTTP client to make API requests
$client = new GuzzleHttp\Client([
    'verify' => false
]);

// Get an access token from Akeneo
$response = $client->post($akeneo_base_url . 'api/oauth/v1/token', [
    'form_params' => [
        'grant_type' => 'password',
        'client_id' => $akeneo_client_id,
        'client_secret' => $akeneo_client_secret,
        'username' => $akeneo_username,
        'password' => $akeneo_password,
    ],
]);
$akeneo_access_token = json_decode($response->getBody())->access_token;
$product_code = 'thsirt1_white_l';
// Get a list of products from Akeneo
$response = $client->get($akeneo_base_url . 'api/rest/v1/products?page=1&limit=10&search={"parent":[{"operator":"IN","value":["tshirt1_grey"]}]}', [
    'headers' => [
        'Authorization' => 'Bearer ' . $akeneo_access_token,
    ],
]);
$akeneo_products = json_decode($response->getBody())->_embedded->items;
echo "<pre>";
    print_r($akeneo_products);die;

$product_ids = [];
$identifier = [];
// Export each product to BigCommerce
foreach ($akeneo_products as $product) {
    $identifier[] = $product->identifier;

    if (!empty($product->values->price)) {
        foreach ($product->values->price[0]->data as $key => $val) {
            if ($val->currency == 'USD') {
                $price = $val->amount;
            } else $price = 0;
        }
    } else $price = 0;
    
    // if($product->values->weight[0]->data['unit']){
    //     $weight = $product->values->weight[0]->data['amount'];
    // }
    // $categories = $product->categories;

    // Build the product payload for the BigCommerce API
    $bigcommerce_product = [
        'name' => $product->values->name[0]->data,
        'type' => 'physical',
        'description' => !empty($product->values->description) ? $product->values->description[0]->data : '',
        // 'weight' => $product->values->weight[0]->data['amount'],
        'weight' => 1,
        'price' => $price,
        // "product_tax_code" => $product->values->online_set_code[0]->data,
        'categories' => [23, 18],
        "is_free_shipping" => false,
        "is_visible" => true,
        "order_quantity_minimum" => 1,
        "brand_name or brand_id" => !empty($product->values->brand) ? $product->values->brand[0]->data : '',
        "page_title" => $product->values->name[0]->data,
        "reviews_count" => 4,
        "total_sold" => 0
    ];

    //Download Image
    if (!empty($product->values->image_1)) {
        $save_directory = 'product_images/';
        $imageName = 'image_' . date("dmYhis") . '.jpg';
        $image_file = $product->values->image_1[0]->data;
        $image_url = $product->values->image_1[0]->_links->download->href;

        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => $image_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer ' . $akeneo_access_token
        ),
        ));

        $imageResponse = curl_exec($curl);

        curl_close($curl);
        file_put_contents($save_directory . $imageName, $imageResponse);
        $url = explode('/', $_SERVER['REQUEST_URI']);
        $projectUrl = $url[1];
        $hostUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/" . $projectUrl;
        $newImgUrl = $hostUrl . '/' . $save_directory . $imageName;

        $imgData = [
            [
                "image_file" => $imageName,
                "is_thumbnail" => true,
                // "sort_order" => -1, 
                // "description" => "", 
                "image_url" => $newImgUrl,
                // "id" => 0,
                // "product_id" => 0,
                "url_zoom" => $newImgUrl,
                "url_standard" => $newImgUrl,
                "url_thumbnail" => $newImgUrl,
                "url_tiny" => $newImgUrl,
                // "date_modified" => date('m/d/Y h:i:s a', time()) 
            ]
        ];
        // $bigcommerce_product['images'] = $imgData;
    }
    //end of download Image

    // echo "<pre>";
    // print_r($bigcommerce_product);die;

    // Create the product in BigCommerce
    // $response = $client->post($bigcommerce_base_url . $bigcommerce_store_hash . '/v3/catalog/products', [
    //     'headers' => [
    //         'X-Auth-Client' => $bigcommerce_client_id,
    //         'X-Auth-Token' => $bigcommerce_access_token,
    //         'Content-Type' => 'application/json',
    //     ],
    //     'json' => $bigcommerce_product,
    // ]);
    $url = $bigcommerce_base_url . $bigcommerce_store_hash . '/v3/catalog/products';
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($bigcommerce_product),
        CURLOPT_HTTPHEADER => array(
            'X-Auth-Token: ' . $bigcommerce_access_token,
        ),
    ));

    $response = curl_exec($curl);
    // echo "<pre>";
    // print_r(json_decode($response, true));die;
    if ($response) {
        $result = json_decode($response, true);
        $product_ids[] = !empty($result['data']) ? $result['data']['id'] : null;
    }
}
// die;
$data = array(
    'product_ids' => $product_ids,
    'identifier' => $identifier,
);
setcookie(
    "exportData",
    json_encode($data),
    time() + (10 * 365 * 24 * 60 * 60)
);
echo "done";
