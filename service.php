<?php

include "vendor/autoload.php";
include "config.php";

$db = new atk4\data\Persistence_SQL($config["db"]["dsn"], $config["db"]["user"], $config["db"]["pass"]);

class Model_Rate extends atk4\data\Model {
    public $table = "rate";
    function init(){
        parent::init();
        $this->addField("dat");
        $this->addField("bid");
        $this->addField("ask");
    }
}

$m = new Model_Rate($db);
/*
 * Handle Data request by TimeChart
 * */
header("Content-type: application/json");
if (isset($_REQUEST["unit"])){
    $unit = isset($_REQUEST["unit"])?$_REQUEST["unit"]:"d";
    $from = isset($_REQUEST["from"])?$_REQUEST["from"]:null;
    $to = isset($_REQUEST["to"])?$_REQUEST["to"]:null;

    $a = $m->action('select')->order('dat', 'asc');
    if ($unit != "d"){
        $a->field("min(bid) as bid");
        $a->field("max(ask) as ask");
    }
    if ($unit == "y"){
        $a->group($a->expr("date_format(dat, '%Y')"));
    } else if ($unit == "m"){
        $a->group($a->expr("date_format(dat, '%Y%m')"));
    }
    $r = $a->select();
    foreach ($r as $row){
        $out[] = [strtotime($row["dat"])*1000, (float)$row["bid"], (float)$row["ask"]];
    }
    $response = ["unit" => $unit, "values" => $out, "from" => $from, "to" => $to]; 
    echo json_encode($response);
    exit;
}
header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
echo "Invalid request";
