<?php
require_once dirname(__FILE__) . '/csv-redirects.php';


$redirects = new CSV_Redirects('redirect.csv');
$redirects->run();

echo '<br><br> ======== CSV Redirects =========';