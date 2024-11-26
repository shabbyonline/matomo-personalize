<?php
    // this token is used to authenticate your API request.
    // You can get the token from Settings -> Personal -> Security -> scroll down to the section Auth Token and create one
    
    // $token_auth = get_option('auth-token-matomo');
    // $url_path = get_option('panel-url-matomo');
    $url_path = "https://matomo.8thloopdataserver.com";
    $token_auth = "2fd61842664efb536f18f4b3150a01bb";
    $filter_offset = 0;

    $list = [];
    $count = 0;
    $users = [];

    $url = $url_path;
    $url .= "/?module=API&method=UserId.getUsers";
    $url .= "&idSite=1&period=range&date=2022-02-01,2022-03-08";
    $url .= "&format=JSON&filter_limit=-1";
    $url .= "&token_auth=$token_auth";
    $url .= "&hideColumns=label,bounce_count,nb_visits,nb_visits_converted,max_actions,sum_daily_nb_uniq_visitors,sum_visit_length,sum_daily_nb_users";

    $fetched = file_get_contents($url);
    $content = json_decode($fetched,true);
    
    echo "<pre>";
    if(count($content)>25){
        foreach($content as $row){
            if($row['nb_actions'] >= 2){
                // echo "Visitor ID: ".$row['idvisitor'];
                // echo " -Actions: ".$row['nb_actions']."<br/>";
                $users[] = [
                    "idvisitor" => $row['idvisitor'],
                    "nb_action" => $row['nb_actions']
                ];
            }
        }
        
        foreach($users as $user){
            $visitor_id = $user['idvisitor'];

            $url1 = $url_path;
            $url1 .= "/?module=API&method=Live.getLastVisitsDetails";
            $url1 .= "&idSite=1&period=range&date=2022-02-01,2022-03-08";
            $url1 .= "&format=JSON&filter_limit=100";
            $url1 .= "&token_auth=$token_auth";
            $url1 .= "&filter_offset=$filter_offset";
            $url1 .= "&hideColumns=idSite,idVisit,visitIp,fingerprint,goalConversions,siteCurrency,siteCurrencySymbol,serverDate,visitServerHour,lastActionTimestamp,lastActionDateTime,siteName,serverTimestamp,firstActionTimestamp,serverTimePretty,serverDatePretty,serverDatePrettyFirstAction,serverTimePrettyFirstAction,visitorType,visitorTypeIcon,visitConverted,visitConvertedIcon,visitCount,visitEcommerceStatus,visitEcommerceStatusIcon,daysSinceFirstVisit,secondsSinceFirstVisit,daysSinceLastEcommerceOrder,secondsSinceLastEcommerceOrder,visitDuration,visitDurationPretty,searches,actions,interactions,referrerType,referrerTypeName,referrerName,referrerKeyword,referrerKeywordPosition,referrerUrl,referrerSearchEngineUrl,referrerSearchEngineIcon,referrerSocialNetworkUrl,referrerSocialNetworkIcon,languageCode,language,deviceType,deviceTypeIcon,deviceBrand,deviceModel,operatingSystem,operatingSystemName,operatingSystemIcon,operatingSystemCode,operatingSystemVersion,browserFamily,browserFamilyDescription,browser,browserName,browserIcon,browserCode,browserVersion,totalEcommerceRevenue,totalEcommerceConversions,totalEcommerceItems,totalAbandonedCartsRevenue,totalAbandonedCarts,totalAbandonedCartsItems,events,continent,continentCode,country,countryCode,countryFlag,region,regionCode,city,location,latitude,longitude,visitLocalTime,visitLocalHour,daysSinceLastVisit,secondsSinceLastVisit,resolution,plugins,pluginsIcons";

            $fetched1 = file_get_contents($url1);
            $content1 = json_decode($fetched1,true);
            foreach ($content1 as $row1) {
                if($row1['userId'] != null || $row1['userId'] != '' || !empty($row1['userId'])){
                    $visitor_ids = $row1['visitorId'];
                    $user_ids    = $row1['userId'];
                    if($visitor_id == $visitor_ids){
                        foreach($row1['actionDetails'] as $actions){
                            //Skipping Action coming inside array (useless)
                            if($actions["type"]=="action"){
                                continue;
                            }
                            // Orders
                            if($actions["type"]=="ecommerceOrder"){
                                foreach($actions['itemDetails'] as $action){
                                    $list[] = [
                                        [$user_ids, $action['itemSKU'], $action['timestamp'], "Purchase"],
                                    ];
                                }
                            }
                            //Product Visit
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
            $filter_offset += 100;
        }
    }
    echo "</pre>";

/*
    do{
        $url = $url_path;
        $url .= "/?module=API&method=UserId.getUsers";
        $url .= "&idSite=1&period=range&date=2022-02-01,2022-03-08";
        $url .= "&format=JSON&filter_limit=100";
        $url .= "&token_auth=$token_auth";
        $url .= "&filter_offset=$filter_offset";
        $url .= "&hideColumns=label,bounce_count,nb_visits_converted,max_actions,sum_daily_nb_uniq_visitors,sum_visit_length,sum_daily_nb_users";

        $fetched = file_get_contents($url);
        $content = json_decode($fetched,true);

        foreach ($content as $row) {
            if(isset($row['idvisitor'])){
                $visitor_ids = $row['idvisitor'];

                $url1 = $url_path;
                $url1 .= "/?module=API&method=Live.getLastVisitsDetails";
                $url1 .= "&idSite=1&period=range&date=2022-02-01,2022-03-01";
                $url1 .= "&format=JSON&filter_limit=100";
                $url1 .= "&token_auth=$token_auth";
                $url1 .= "&filter_offset=$filter_offset";
                $url1 .= "&hideColumns=idSite,idVisit,visitIp,fingerprint,goalConversions,siteCurrency,siteCurrencySymbol,serverDate,visitServerHour,lastActionTimestamp,lastActionDateTime,siteName,serverTimestamp,firstActionTimestamp,serverTimePretty,serverDatePretty,serverDatePrettyFirstAction,serverTimePrettyFirstAction,visitorType,visitorTypeIcon,visitConverted,visitConvertedIcon,visitCount,visitEcommerceStatus,visitEcommerceStatusIcon,daysSinceFirstVisit,secondsSinceFirstVisit,daysSinceLastEcommerceOrder,secondsSinceLastEcommerceOrder,visitDuration,visitDurationPretty,searches,actions,interactions,referrerType,referrerTypeName,referrerName,referrerKeyword,referrerKeywordPosition,referrerUrl,referrerSearchEngineUrl,referrerSearchEngineIcon,referrerSocialNetworkUrl,referrerSocialNetworkIcon,languageCode,language,deviceType,deviceTypeIcon,deviceBrand,deviceModel,operatingSystem,operatingSystemName,operatingSystemIcon,operatingSystemCode,operatingSystemVersion,browserFamily,browserFamilyDescription,browser,browserName,browserIcon,browserCode,browserVersion,totalEcommerceRevenue,totalEcommerceConversions,totalEcommerceItems,totalAbandonedCartsRevenue,totalAbandonedCarts,totalAbandonedCartsItems,events,continent,continentCode,country,countryCode,countryFlag,region,regionCode,city,location,latitude,longitude,visitLocalTime,visitLocalHour,daysSinceLastVisit,secondsSinceLastVisit,resolution,plugins,pluginsIcons";

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

        $filter_offset += 100;
    }while($content);
    echo "</pre>";
*/

    $file = null;
    $created = null;

    $date = date('d-m-y');

    $file = fopen(PLUGIN_PATH."$date.csv","w");
    array_unshift($list[0],["USER_ID","ITEM_ID","TIMESTAMP","EVENT_TYPE"]);
    foreach ($list as $line) {
        $created = fputcsv($file, $line[0]);
    }


    fclose($file);
?>