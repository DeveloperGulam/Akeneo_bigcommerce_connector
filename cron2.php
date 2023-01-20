<?php
set_time_limit(0);
$page = $_SERVER['PHP_SELF'];
$sec = "60";
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

$client = new GuzzleHttp\Client([
    'verify' => false
]);
?>
<html>

<head>
    <!-- <meta http-equiv="refresh" content="<?php echo $sec ?>;URL='<?php echo $page ?>'"> -->
</head>

<body>
    <?php

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

    if (isset($_COOKIE['exportData'])) {

        $exportData = json_decode($_COOKIE['exportData']);
        $product_ids = $exportData->product_ids;
        $akeneoId = $exportData->identifier;
        // echo "<pre>";
        //     print_r($exportData);die;
        $productVariantData = [];
        foreach ($product_ids as $key => $product_id) {

            // Get a list of products from Akeneo
            $response = $client->get($akeneo_base_url . 'api/rest/v1/product-models?page=1&limit=10&search={"parent":[{"operator":"IN","value":["' . $akeneoId[$key] . '"]}]}', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $akeneo_access_token,
                ],
            ]);
            $akeneo_products = json_decode($response->getBody())->_embedded->items;

            // echo "<pre>";
            // print_r($akeneo_products);die;

            if (isset($_COOKIE['exportOptionData'])) {

                $exportOptionData = json_decode($_COOKIE['exportOptionData']);
                $option_id = $exportOptionData[0]->option_id;
                // echo "<pre>";
                // print_r($exportOptionData);die;
                $swatches = [];
                // foreach($exportOptionData as $options){
                    // $option_id = $options->option_id;
                    // $id = $options->id;
                    $option_values = [];
                    $count = 0;
                    foreach ($akeneo_products as $product) {
                        if (!empty($product->values->designer_color_name)) {
                            $colorName = $product->values->designer_color_name[0]->data;
                            $firstChar = $colorName[0] ?? null;
                    
                            if($firstChar == '#'){
                                $colorArray = explode(',', $colorName);
                                $swatches[] = "yes";
                            } else {
                                $swatches[] = "no";
                                $colorArray = [];
                            }
                            $option_values[] = array(
                                "id" => $exportOptionData[$count]->id,
                                "label" => $colorName,
                                "sort_order" => 1,
                                "value_data" => [
                                    "colors" => $colorArray
                                ],
                                "is_default" => false
                            );
                            $count++;
                        }
                    }
                // }
                $option_type = in_array('no', $swatches) ? 'rectangles' : 'swatch';
                $optionData = [
                    "display_name" => 'Color',
                    "type" => $option_type, // radio_buttons,swatch,dropdown,rectangles,product_list,product_list_with_images
                    "option_values" => $option_values,
                    "image_url" => ""
                ];
                // echo "<pre> $option_id";
                // echo json_encode($optionData);die;
                // print_r($optionData);die;
                //Update Options
                $optionUrl = $bigcommerce_base_url . $bigcommerce_store_hash . '/v3/catalog/products/'  . $product_id . '/options/' . $option_id;
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $optionUrl,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'PUT',
                    CURLOPT_POSTFIELDS => json_encode($optionData),
                    CURLOPT_HTTPHEADER => array(
                        'X-Auth-Token: ' . $bigcommerce_access_token,
                    ),
                ));

                $optionResponse = curl_exec($curl);
                curl_close($curl);
                $optionResponseData = json_decode($optionResponse, true);
                // echo "<pre>";
                // print_r($optionResponseData);die;
            }

            if (isset($_COOKIE['exportVariantData'])) {
                $variantData = [];
                foreach ($akeneo_products as $product) {
                    $variant_product_json = $client->get($akeneo_base_url . 'api/rest/v1/products?page=1&limit=10&search={"parent":[{"operator":"IN","value":["' . $product->code . '"]}]}', [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $akeneo_access_token,
                        ],
                    ]);
                    $variant_products = json_decode($variant_product_json->getBody())->_embedded->items;
                    foreach ($variant_products as $variant_product) {
                        //Create Options
                        $variantData[] = [
                            "price" => $variant_product->values->price[0]->data[0]->amount,
                            "weight" => 1,
                            "image_url" => "https://images.squarespace-cdn.com/content/v1/51b025e0e4b0fdd75221071c/1468822070901-AYLQXMS1C9D4K7AJ4615/SMITH+12+cover.jpg?format=300w",
                            "sku" => $variant_product->identifier
                        ];
                    }
                    
                    // $variantData[] = [
                    //     "price" => $product->values->price[0]->data[0]->amount,
                    //     "weight" => 1,
                    //     "image_url" => "https://images.squarespace-cdn.com/content/v1/51b025e0e4b0fdd75221071c/1468822070901-AYLQXMS1C9D4K7AJ4615/SMITH+12+cover.jpg?format=300w",
                    //     "sku" => $product->code
                    // ];
                }
                // echo "<pre>";
                // print_r($variantData);die;
                $exportVariantData = json_decode($_COOKIE['exportVariantData']);
                foreach($exportVariantData as $key => $variant_id){
                    //Update Variant
                    $variantUrl = $bigcommerce_base_url . $bigcommerce_store_hash . '/v3/catalog/products/'  . $product_id . '/variants/' . $variant_id;
                    $c = curl_init();
                    curl_setopt_array($c, array(
                        CURLOPT_URL => $variantUrl,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'PUT',
                        CURLOPT_POSTFIELDS => json_encode($variantData[$key]),
                        CURLOPT_HTTPHEADER => array(
                            'X-Auth-Token: ' . $bigcommerce_access_token,
                            'Content-Type: application/json'
                        ),
                    ));
                    $variantresponse = curl_exec($c);
                    curl_close($c);

                    $variantresponseData = json_decode($variantresponse, true);
                    // echo "<pre>";
                    // print_r($variantresponseData);
                    // die;
                }
            }
        }
        echo "Done";
    } else {
        echo "Please import the akeneo product!!";
    }
    ?>
</body>

</html>