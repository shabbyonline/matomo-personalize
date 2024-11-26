<?php
/**
 * This page renders the settings page into wp-admin dashboard
 */
    use Aws\Personalize\PersonalizeClient;
    //Creating Dataset Group
    $personalizeClient = new PersonalizeClient([
        'version' => 'latest',
        'region' => 'ap-south-1',
    ]);
?>
<style>
    h1{
        font-weight: 400;
    }
    .logo{
        width: 150px;
    }
</style>
<img src="<?php echo plugin_dir_url( dirname( __FILE__ ) ).'logo.png'; ?>" alt="Matomo Logo" class="logo"/>
<h1>Matomo Settings:</h1>
    <form action="" method="post">
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <td>
                        <label for="tracking-code">Tracking Code:</label>
                    </td>
                    <td>
                        <textarea id="tracking-code" name="tracking_code" cols="80" rows="20" required><?php if( get_option('tracking-code-matomo') != "" ){ echo str_replace("\\","",get_option('tracking-code-matomo')); } ?></textarea>
                        <?php
                           echo str_replace("\\","",get_option('tracking-code-matomo'));
                        ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php
                            if( get_option('tracking-code-matomo') != "" ||  !empty(get_option('tracking-code-matomo')) ):
                                submit_button( 'Update', '', 'update-tracking-code-matomo');
                            else:
                                submit_button( 'Save', '', 'add-tracking-code-matomo');
                            endif;
                        ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </form>
<?php
    if( get_option('tracking-code-matomo') != "" || !empty(get_option('tracking-code-matomo')) ){
?>
    <h1>Auth Token:</h1>
    <form action="" method="post">
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <td>
                        <label for="token-code">Token Code:</label>
                    </td>
                    <td>
                        <input type="text" class="regular-text" id="token-code" name="auth_token" required value="<?php if( get_option('auth-token-matomo') != "" ){ echo get_option('auth-token-matomo'); } ?>" />
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="matomo-panel-url">Matomo Panel URL:</label>
                    </td>
                    <td>
                        <input type="url" class="regular-text" id="matomo-panel-url" name="matomo_panel_url" required value="<?php if( get_option('panel-url-matomo') != "" ){ echo get_option('panel-url-matomo'); } ?>" />
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php
                            if( get_option('auth-token-matomo') != "" ||  !empty(get_option('auth-token-matomo')) ):
                                submit_button( 'Update', '', 'update-auth-token-matomo');
                            else:
                                submit_button( 'Save', '', 'add-auth-token-matomo');
                            endif;
                        ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </form>
<?php
    }
?>

<?php
    $token_auth    = get_option('auth-token-matomo');
    $url_path      = get_option('panel-url-matomo');
    $urlgetVisits  = $url_path;
    $urlgetVisits .= "/?module=API&method=VisitsSummary.getVisits";
    $urlgetVisits .= "&idSite=1&period=range&date=2022-02-01,2022-03-10";
    $urlgetVisits .= "&format=JSON";
    $urlgetVisits .= "&token_auth=$token_auth";

    $fetchedGetVisits = file_get_contents($urlgetVisits);
    $contentGetVisits = json_decode($fetchedGetVisits,true);
    if($contentGetVisits['value'] > 1000) {
        // Create logic if data is uploaded in s3 interactions folder
?>
    <h2>Dataset is ready to upload!</h2>
    <a href="https://99zooocr13.execute-api.ap-south-1.amazonaws.com/" class="button">Upload Interactions now!</a>
<?php
    }
?>

<!-- Logic to check if wp_option dataset-import-job-arn is already exist or have value otherwise IF EXIST didn't display -->
<?php
    if(get_option('dataset-import-job-arn') == NULL || empty(get_option('dataset-import-job-arn'))){
?>
    <h1>Personalize Settings:</h1>
    <form action="" method="post">
        <table class="form-table" role="presentation">
            <tr>
                <td>
                    <label for="dataset-name">Dataset Name:</label>
                </td>
                <td>
                    <input type="text" id="dataset-name" class="regular-text" required name="dataset_name">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="interactions_csv_input">Data Location Path e.g; (s3://bucket-name/folder-name/filename):</label>
                </td>
                <td>
                    <input type="text" id="interactions_csv_input" class="regular-text" required name="interactions_csv_input">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="role_arn_input">Role ARN:</label>
                </td>
                <td>
                    <input type="text" id="role_arn_input" class="regular-text" required name="role_arn_input">
                </td>
            </tr>
            <tr>
                <td><?php submit_button( 'Process', '', 'create-personalize-dataset-group'); ?></td>
            </tr>
        </table>
    </form>
<?php
    }
?>

<?php
    //Schema Check
    /*
        // To check all schema
        $result = $personalizeClient->listSchemas([
            'maxResults' => 50,
        ]);
        echo "<pre>";
            var_dump($result);
        echo "</pre>";

        //To fetch specific schema
        // $result = $personalizeClient->describeSchema([
        //     'schemaArn' => $schemaArn, // REQUIRED
        // ]);
        // echo "<pre>";
        //     var_dump($result);
        // echo "</pre>";
    */
    //3. Create Dataset (Interactions)
    /*
    $datasetGroupArn = get_option('dataset-group-arn');
    $getSchemaArn    = get_option('schema-arn');
    
    $createNewDataset = $personalizeClient->createDataset([
        'datasetGroupArn' => $datasetGroupArn, // REQUIRED
        'datasetType' => 'Interactions', // REQUIRED
        'name' => 'interaction-dataset', // REQUIRED
        'schemaArn' => $getSchemaArn, // REQUIRED
    ]);
    if(isset($createNewDataset)){
        add_option('dataset-arn', $createNewDataset['datasetArn']);
        echo "Dataset Created..</br>";
    }
    */
    
        //4. Create Dataset Import Job (Interactions DS)
        $getDatasetArn = get_option('dataset-arn');
        $dataBulkImport = $personalizeClient->createDatasetImportJob([
            'dataSource' => [ // REQUIRED
                'dataLocation' => 's3://interaction-bucket/interactions-csv/interactions.csv',
            ],
            'datasetArn' => $getDatasetArn, // REQUIRED
            'jobName' => 'import-job-one', // REQUIRED
            'roleArn' => 'arn:aws:iam::387092104825:role/S3-Personalize', // REQUIRED
        ]);
        if(isset($dataBulkImport)){
            add_option( 'dataset-import-job-arn', $dataBulkImport['datasetImportJobArn'] );
            echo "Import Job In Progress..</br>";
        }
    
    if(isset($_POST['create-personalize-dataset-group'])){
        $dataset_name         = $_POST['dataset_name'];
        $interactionsLocation = $_POST['interactions_csv_input'];
        $roleArn              = $_POST['role_arn_input'];
        //1. Creating Dataset Group
            $datasetGroupCreation = $personalizeClient->createDatasetGroup([
                'domain' => 'ECOMMERCE',
                // 'kmsKeyArn' => '<string>',
                'name' => $dataset_name, // REQUIRED
                // 'roleArn' => '<string>',
            ]);
            add_option( 'dataset-group-arn', $datasetGroupCreation['datasetGroupArn'] );
            echo "Dataset Group Created..</br>";

        //2. Create Schema ARN
            $schemaCreation = $personalizeClient->createSchema([
                'domain'    => 'ECOMMERCE',
                'name'      => 'interaction-schema'.rand(10,100), // REQUIRED
                'schema'    => '{
                                "type": "record",
                                "name": "Interactions",
                                "namespace": "com.amazonaws.personalize.schema",
                                "fields": [
                                    {
                                        "name": "USER_ID",
                                        "type": "string"
                                    },
                                    {
                                        "name": "ITEM_ID",
                                        "type": "string"
                                    },
                                    {
                                        "name": "EVENT_TYPE",
                                        "type": "string"
                                    },
                                    {
                                        "name": "EVENT_VALUE",
                                        "type": [
                                            "null",
                                            "float"
                                        ]
                                    },
                                    {
                                        "name": "TIMESTAMP",
                                        "type": "long"
                                    }
                                ],
                                "version": "1.0"
                            }', // REQUIRED //A schema in Avro JSON format.
            ]);
            if(isset($schemaCreation)){
                add_option( 'schema-arn', $schemaCreation['schemaArn'] );
                echo "Schema Created..</br>";
            }

        // Check dataset creation status if active then proceed further

        //3. Create Dataset (Interactions)
            $datasetGroupArn = get_option('dataset-group-arn');
            $getSchemaArn    = get_option('schema-arn');
            
            $createNewDataset = $personalizeClient->createDataset([
                'datasetGroupArn' => $datasetGroupArn, // REQUIRED
                'datasetType' => 'Interactions', // REQUIRED
                'name' => 'interaction-dataset', // REQUIRED
                'schemaArn' => $getSchemaArn, // REQUIRED
            ]);
            if(isset($createNewDataset)){
                add_option('dataset-arn',  $createNewDataset['datasetArn']);
                echo "Dataset Created..</br>";
            }

        //4. Create Dataset Import Job (Interactions DS)
            $getDatasetArn = get_option('dataset-arn');
            $dataBulkImport = $personalizeClient->createDatasetImportJob([
                'dataSource' => [ // REQUIRED
                    'dataLocation' => 's3://interaction-bucket/interactions-csv/interactions.csv',
                ],
                'datasetArn' => $getDatasetArn, // REQUIRED
                'jobName' => 'import-job-one', // REQUIRED
                'roleArn' => 'arn:aws:iam::387092104825:role/S3-Personalize', // REQUIRED
            ]);

            if(isset($dataBulkImport)){
                add_option( 'dataset-import-job-arn', $dataBulkImport['datasetImportJobArn'] );
                echo "Import Job In Progress..</br>";
            }
    }

    //Describe dataset group
    /*
        $describeDG = $personalizeClient->describeDatasetGroup([
            'datasetGroupArn' => 'arn:aws:personalize:ap-south-1:387092104825:dataset-group/testing-dataset', // REQUIRED
        ]);
        foreach($describeDG as $dDG){
            echo $dDG['status'];
        }
    */

    //To check dataset import job status
    /*
        $result = $personalizeClient->listDatasetImportJobs([
            'datasetArn' => $getDatasetArn
        ]);
        echo "<pre>";
            var_dump($result);
        echo "</pre>";
    */
?>

<?php
    //Adding Tracking Code
    if(isset($_POST['add-tracking-code-matomo'])):
        $tracking_code = $_POST['tracking_code'];
        $add = add_option( 'tracking-code-matomo', $tracking_code );
        if($add){
            v8_notice_success("Tracking Code Added..!");
            ?>
                <script>
                    location.reload();
                </script>
            <?php
        }else{
            v8_notice_error("Tracking Code Not Added, Some Error Occurred..!");
            ?>
                <script>
                    location.reload();
                </script>
            <?php
        }
    endif;

    //Updating Tracking Code
    if(isset($_POST['update-tracking-code-matomo'])):
        $tracking_code = $_POST['tracking_code'];
        $update_tracking_code = update_option( 'tracking-code-matomo', $tracking_code );
        if($update_tracking_code){
            v8_notice_success("Tracking Code Updated..!");
            ?>
                <script>
                    location.reload();
                </script>
            <?php
        }else{
            v8_notice_error("Mentioned Tracking Code Already Exist..!");
            ?>
                <script>
                    location.reload();
                </script>
            <?php
        }
    endif;
?>

<?php
    //Adding Auth Token
    if(isset($_POST['add-auth-token-matomo'])):
        $auth_token = $_POST['auth_token'];
        $matomo_panel_url = $_POST['matomo_panel_url'];
        add_option( 'auth-token-matomo', $auth_token );
        add_option( 'panel-url-matomo', $matomo_panel_url );
        v8_notice_success("Auth Token Added..!");
        ?>
            <script>
                location.reload();
            </script>
        <?php
    endif;

    //Updating Auth Token
    if(isset($_POST['update-auth-token-matomo'])):
        $auth_token = $_POST['auth_token'];
        $matomo_panel_url = $_POST['matomo_panel_url'];
        update_option( 'auth-token-matomo', $auth_token );
        update_option( 'panel-url-matomo', $matomo_panel_url );
        v8_notice_success("Updated Successfully..!");
        ?>
            <script>
                location.reload();
            </script>
        <?php
    endif;
?>