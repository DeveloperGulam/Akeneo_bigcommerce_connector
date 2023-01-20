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
        $productVariantData = [];
        $productOptionInfo = [];
        $productVarientIds = [];
        foreach ($product_ids as $key => $product_id) {

            // Get a list of product from Akeneo
            $response = $client->get($akeneo_base_url . 'api/rest/v1/product-models?page=1&limit=10&search={"parent":[{"operator":"IN","value":["'.$akeneoId[$key].'"]}]}', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $akeneo_access_token,
                ],
            ]);
            $akeneo_products = json_decode($response->getBody())->_embedded->items;

            $option_color_values = [];
            $variantColorData = [];
            $variantSizeData = [];
            $product_model_codes = [];
            $option_size_values = [];
            $check_variants = [];
            $swatches = [];
            $available_sizes = [];
            $variant_flag = false;
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
                    $option_color_values[] = array(
                        "label" => $colorName,
                        "sort_order" => 1,
                        "value_data" => [
                            "colors" => $colorArray
                        ],
                        "is_default" => false
                    );
                }
                $variantColorData[] = [
                    "price" => $product->values->price[0]->data[0]->amount,
                    "weight" => 1,
                    "image_url" => "https://images.squarespace-cdn.com/content/v1/51b025e0e4b0fdd75221071c/1468822070901-AYLQXMS1C9D4K7AJ4615/SMITH+12+cover.jpg?format=300w",
                    "sku" => $product->code
                ];
                //creating product variants from parent product model
                if (!empty($product->code)) {
                    $variant_product_json = $client->get($akeneo_base_url . 'api/rest/v1/products?page=1&limit=10&search={"parent":[{"operator":"IN","value":["' . $product->code . '"]}]}', [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $akeneo_access_token,
                        ],
                    ]);
                    $variant_products = json_decode($variant_product_json->getBody())->_embedded->items;

                    foreach ($variant_products as $variant_product) {
                        //Create Options
                        if (!empty($variant_product->values->Designer_Size_Name)) {
                            $sizeName = $variant_product->values->Designer_Size_Name[0]->data;
                            $available_sizes[$colorName][] = $sizeName;
                            if(!in_array($sizeName, $check_variants)){
                                $check_variants[] = $sizeName;
                                $option_size_values[] = array(
                                    "label" => $sizeName,
                                    "sort_order" => 1,
                                    "value_data" => [
                                        "colors" => []
                                    ],
                                    "is_default" => false
                                );
                                // $variantSizeData[] = [
                                //     "price" => $variant_product->values->price[0]->data[0]->amount,
                                //     "weight" => 1,
                                //     "image_url" => "https://images.squarespace-cdn.com/content/v1/51b025e0e4b0fdd75221071c/1468822070901-AYLQXMS1C9D4K7AJ4615/SMITH+12+cover.jpg?format=300w",
                                //     "sku" => $variant_product->identifier
                                // ];
                                // $variant_flag = true;
                            }
                            $variantSizeData[] = [
                                "color" => $colorName,
                                "size" => $sizeName,
                                "price" => $variant_product->values->price[0]->data[0]->amount,
                                "weight" => 1,
                                "image_url" => "https://images.squarespace-cdn.com/content/v1/51b025e0e4b0fdd75221071c/1468822070901-AYLQXMS1C9D4K7AJ4615/SMITH+12+cover.jpg?format=300w",
                                "sku" => $variant_product->identifier
                            ];
                        }
                    }
                }
            }
            // $variantData = array_merge($variantColorData, $variantSizeData);
            $variantData = $variantColorData;
            $optionDatas = [];
            // echo "<pre>";
            // print_r($available_sizes);
            // print_r($variantSizeData);die;
            //Color Data stored
            if(!empty($option_color_values)){
                $option_type = in_array('no', $swatches) ? 'rectangles' : 'swatch';
                $optionColorData = [
                    "display_name" => 'Color',
                    "type" => $option_type, // radio_buttons,swatch,dropdown,rectangles,product_list,product_list_with_images
                    "option_values" => $option_color_values,
                    "image_url" => ""
                ];
                $optionDatas[] = $optionColorData;
            }
            
            // $option_size_values = array_values(array_unique($option_size_values, SORT_REGULAR));
            //Size Data stored
            if(!empty($option_size_values)){
                $optionSizeData = [
                    "display_name" => 'Size',
                    "type" => 'rectangles', // radio_buttons,swatch,dropdown,rectangles,product_list,product_list_with_images
                    "option_values" => $option_size_values,
                    "image_url" => ""
                ];
                $optionDatas[] = $optionSizeData;
            }
            // echo "<pre>";
            // print_r($variantData);die;

            if(!empty($optionDatas)){
                $optionResponseArray = [];
                $variantresponseData = [];
                $optionValues = [];
                $count = 0;
                foreach($optionDatas as $key => $optionData) {
                    //Create Options
                    $optionUrl = $bigcommerce_base_url . $bigcommerce_store_hash . '/v3/catalog/products/'  . $product_id . '/options';
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
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS => json_encode($optionData),
                        CURLOPT_HTTPHEADER => array(
                            'X-Auth-Token: ' . $bigcommerce_access_token,
                        ),
                    ));

                    $optionResponse = curl_exec($curl);
                    curl_close($curl);
                    $optionResponseData = json_decode($optionResponse, true);
                    if (!empty($optionResponseData['data'])){
                        $optionId = $optionResponseData['data']['id'];
                        // $optionResponseArray[] = $optionResponseData['data'];
                        $optionValuesArray = [];
                        foreach ($optionResponseData['data']['option_values'] as $value) {
                            $productOptionInfo[] = array(
                                'option_id' => $optionId,
                                'id' => $value['id']
                            );
                            $optionValues = [
                                "option_display_name" => $optionResponseData['data']['display_name'],
                                "label" => $value['label'],
                                'id' => $value['id'],
                                'option_id' => $optionId
                            ];
                            $optionValuesArray[] = $optionValues;

                            // $variantData[$count]['option_values'] = $optionValues;
                            // $count++;
                        }
                        $optionResponseArray[] = $optionValuesArray;
                    }

                    // if (!empty($optionResponseData['data']) && $key == 0) {
                        
                    //     $optionId = $optionResponseData['data']['id'];
                    //     foreach ($optionResponseData['data']['option_values'] as $value) {
                    //         $productOptionInfo[] = array(
                    //             'option_id' => $optionId,
                    //             'id' => $value['id']
                    //         );
                    //         $optionValues = [
                    //             [
                    //                 "option_display_name" => "Color",
                    //                 "label" => "Beige",
                    //                 'id' => $value['id'],
                    //                 'option_id' => $optionId
                    //             ]
                    //         ];
                    //         $variantData[$count]['option_values'] = $optionValues;
                    //         $variantUrl = $bigcommerce_base_url . $bigcommerce_store_hash . '/v3/catalog/products/'  . $product_id . '/variants';
                    //         $c = curl_init();
                    //         curl_setopt_array($c, array(
                    //             CURLOPT_URL => $variantUrl,
                    //             CURLOPT_RETURNTRANSFER => true,
                    //             CURLOPT_ENCODING => '',
                    //             CURLOPT_MAXREDIRS => 10,
                    //             CURLOPT_TIMEOUT => 0,
                    //             CURLOPT_FOLLOWLOCATION => true,
                    //             CURLOPT_SSL_VERIFYPEER => false,
                    //             CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    //             CURLOPT_CUSTOMREQUEST => 'POST',
                    //             CURLOPT_POSTFIELDS => json_encode($variantData[$count]),
                    //             CURLOPT_HTTPHEADER => array(
                    //                 'X-Auth-Token: ' . $bigcommerce_access_token,
                    //                 'Content-Type: application/json'
                    //             ),
                    //         ));
                    //         $variantresponse = curl_exec($c);
                    //         curl_close($c);
                    //         $variantArr = json_decode($variantresponse, true);
                    //         $variantresponseData[] = $variantArr;
                    //         if (!empty($variantArr['data'])) {
                    //             $productVarientIds[] = $variantArr['data']['id'];
                    //         }
                    //         $count++;
                    //     }
                    //     $productVariantData[$product_id] = $variantData;
                    // }
                }
                $pairs = array();
                $count_variant = 0;
                foreach ($optionResponseArray[0] as $color) {
                    foreach ($optionResponseArray[1] as $size) {
                        if(in_array($size['label'], $available_sizes[$color['label']])){
                            $pairs[] = array($color, $size);
                            $filtered = array_filter($variantSizeData, function ($item) use ($color, $size) {
                                return $item['color'] == $color['label'] && $item['size'] == $size['label'];
                            });
                            $parent_keys = array_keys($filtered);
                            $variantSizeData[$parent_keys[0]]['option_values'] = array($color, $size);
                            $count_variant++;
                        }
                    }
                }
                $variantData = array_map(function ($item) {
                    unset($item['color']);
                    unset($item['size']);
                    return $item;
                }, $variantSizeData);
                // echo "<pre>";
                // print_r($pairs);
                // print_r($variantData);die;
                $variantresponseData = [];
                if(!empty($variantData)){
                    foreach($variantData as $productVariant){
                        //Create Variant
                        $variantUrl = $bigcommerce_base_url . $bigcommerce_store_hash . '/v3/catalog/products/'  . $product_id . '/variants';
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
                            CURLOPT_CUSTOMREQUEST => 'POST',
                            CURLOPT_POSTFIELDS => json_encode($productVariant),
                            CURLOPT_HTTPHEADER => array(
                                'X-Auth-Token: ' . $bigcommerce_access_token,
                                'Content-Type: application/json'
                            ),
                        ));
                        $variantresponse = curl_exec($c);
                        curl_close($c);
                        $variantArr = json_decode($variantresponse, true);
                        $variantresponseData[] = $variantArr;
                        if (!empty($variantArr['data'])) {
                            $productVarientIds[] = $variantArr['data']['id'];
                        }
                    }
                }
            }
        }
        setcookie(
            "exportOptionData",
            json_encode($productOptionInfo),
            time() + (10 * 365 * 24 * 60 * 60)
        );
        setcookie(
            "exportVariantData",
            json_encode($productVarientIds),
            time() + (10 * 365 * 24 * 60 * 60)
        );
        // echo "<pre>";
        // echo "exportOptionData";
        // print_r($productOptionInfo);
        // echo "exportVariantData";
        // print_r($productVarientIds);
        // echo "variantresponseData";
        // print_r($variantresponseData);
        // print_r($productVariantData);die;
        echo "Done";
    } else {
        echo "Please import the akeneo product!!";
    }
    ?>
</body>

</html>