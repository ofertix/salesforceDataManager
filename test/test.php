<?php
require "../vendor/autoload.php";

$config = [
    'username' => "",
    'password' => "",
    'token' =>  ""
];
$clientFactory = new SalesForceDataManager\ClientFactory($config);
$bulkApiClient = $clientFactory->getBulkApiClient();
$manager = new SalesForceDataManager\DataManager($bulkApiClient);
$results = $manager->query("select Id from Account limit 1", "Account");
var_dump(iterator_to_array($results));
