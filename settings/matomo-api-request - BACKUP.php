<?php
    // this token is used to authenticate your API request.
    // You can get the token from Settings -> Personal -> Security -> scroll down to the section Auth Token and create one
    
    // $token_auth = get_option('auth-token-matomo');
    // $url_path = get_option('panel-url-matomo');
    $url_path = "https://matomo.8thloopdataserver.com";
    $token_auth = "2fd61842664efb536f18f4b3150a01bb";

    $list = [];
    $limit = 100;

    $url = $url_path;
    $url .= "/?module=API&method=UserId.getUsers";
    $url .= "&idSite=1&period=range&date=2022-02-01,2022-03-01";
    $url .= "&format=JSON&filter_limit=$limit";
    $url .= "&token_auth=$token_auth";

    $fetched = file_get_contents($url);
    $content = json_decode($fetched,true);

    // echo "<pre>";
    foreach ($content as $row) {
        if(isset($row['idvisitor'])){
            $visitor_id = $row['idvisitor'];
            $url1 = $url_path;
            $url1 .= "/?module=API&method=Live.getLastVisitsDetails";
            $url1 .= "&idSite=1&period=range&date=2022-02-01,2022-03-01";
            $url1 .= "&format=JSON&filter_limit=$limit";
            $url1 .= "&token_auth=$token_auth";

            $fetched1 = file_get_contents($url1);
            $content1 = json_decode($fetched1,true);
            foreach ($content1 as $row1) {
                $visitor_ids = $row1['visitorId'];
                // $user_ids    = ($row1['userId']=='null') ? 0 : $row1['userId'];
                $user_ids    = $row1['userId'];
                if($visitor_id == $visitor_ids){
                    foreach($row1['actionDetails'] as $actions){
    
                        // Orders
                        if($actions["type"]=="ecommerceOrder"){
                            foreach($actions['itemDetails'] as $act){
                                $list[] = [
                                    [$user_ids, $act['itemSKU'], $actions['timestamp'], "Purchase"],
                                ];
                            }
                        }
                        // Product Views
                        if(isset($actions['eventAction'], $actions['eventValue'])){
                            if($actions['eventAction'] == 'Product Visit'){
                                $list[] = [
                                    [$user_ids, $actions['eventValue'], $actions['timestamp'], "Click"],
                                ];
                            }
                        }
    
                    }
                }
            }
        }
    }
    // echo "</pre>";
    echo "<pre>";
    echo count($list);
    //     foreach ($list as $line) {
    //         var_dump($line[0]);
    //     }
    echo "</pre>";

    // $file = null;
    // $created = null;

    // $date = date('d-m-y');

    // if(file_exists(PLUGIN_PATH."$date.csv")){
    //     $file = fopen(PLUGIN_PATH."$date.csv","a");
    //     foreach ($list as $line) {
    //         $created = fputcsv($file, $line[0]);
    //     }
    // } else {
    //     $file = fopen(PLUGIN_PATH."$date.csv","w");
    //     array_unshift($list[0],["USER_ID","ITEM_ID","TIMESTAMP","EVENT_TYPE"]);
    //     foreach ($list as $line) {
    //         $created = fputcsv($file, $line[0]);
    //     }
    // }

    // fclose($file);
?>