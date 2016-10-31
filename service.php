<?php
ini_set("display_errors","on");
include "vendor" . DIRECTORY_SEPARATOR . "autoload.php";
include "config.php";

$db = new atk4\data\Persistence_SQL($config["db"]["dsn"], $config["db"]["user"], $config["db"]["pass"]);

class Model_Result extends atk4\data\Model {
    public $table = "result";
    function init(){
        parent::init();
        $this->addField("player_id");
        $this->addField("game_id");
        $this->addField("win");
        $this->addField("color");
        $this->addField("dts");
    }
}
class Model_Player extends atk4\data\Model {
    public $table = "player";
    function init(){
        parent::init();
        $this->addField("name");
    }
}

class Model_Game extends atk4\data\Model {
    public $table = "game";
    function init(){
        parent::init();
    }
}
$m = new Model_Result($db);
$m->addCondition("player_id", (int)$_REQUEST["player_id"]);
if (isset($_REQUEST["color"])){
    $m->addCondition("color", $_REQUEST["color"]);
}
/*
 * Handle Data request by TimeChart
 * */
header("Content-type: application/json");
if (isset($_REQUEST["unit"])){
    $unit = isset($_REQUEST["unit"])?$_REQUEST["unit"]:"d";
    $from = isset($_REQUEST["from"])?(int)$_REQUEST["from"]:null;
    $to = isset($_REQUEST["to"])?(int)$_REQUEST["to"]:null;

    $a = $m->action('select')->order('dts', 'asc');
    if ($unit != "z"){
        $a->field("count(win) as cnt");
        $a->field("sum(win) as win");
    }
    if ($unit == "y"){
        $a->group($a->expr("date_format(dts, '%Y')"));
    } else if ($unit == "m"){
        $a->group($a->expr("date_format(dts, '%Y%m')"));
    } else if ($unit == "d"){
        $a->group($a->expr("date_format(dts, '%Y%m%d')"));
    } else if ($unit == "h"){
        $a->group($a->expr("date_format(dts, '%Y%m%d %h')"));
    }
    $r = $a->select();
    $out = [];
    foreach ($r as $row){
        $cnt = ($unit !== "z")?(float)$row["cnt"]:1;
        $coef = (float)$row["win"] / $cnt;
        $out[] = [(int)strtotime($row["dts"]), (float)$row["win"], $cnt, $coef, (float)$cnt-$row["win"]];
    }
    $response = ["unit" => $unit, "values" => $out, "from" => $from, "to" => $to]; 
    echo json_encode($response);
    exit;
}
header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
echo "Invalid request";
