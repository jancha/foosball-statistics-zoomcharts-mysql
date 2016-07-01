<?php

include "base.php";

$m = new Model_Rate($db);

echo "importing data\n";
echo "import done\n";
$m->action('delete')->delete();
$bid = file("data/rates_data_bid.csv");
$ask = file("data/rates_data_ask.csv");
echo "Total bid: " . count($bid) . "\n";
echo "Total ask: " . count($ask) . "\n";
foreach ($bid as $k => $row){
    $row = preg_replace("/\"/", "", trim($row));
    $row = explode(",",$row);
    $date = $row[0];
    $bid = $row[1];
    $row_ask = preg_replace("/\"/", "", trim($ask[$k]));
    $row_ask = explode(",",$row_ask);
    $o = [
        "bid" => (float)$bid,
        "ask" => (float)$row_ask[1],
        "dat" => $date
    ];
    $m->unload()->set($o)->save();
}
echo $m->action('count')->getOne();
