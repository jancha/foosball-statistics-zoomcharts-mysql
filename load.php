<?php
ini_set("display_errors","on");
include "vendor" . DIRECTORY_SEPARATOR . "autoload.php";
include "config.php";

$db = new atk4\data\Persistence_SQL($config["db"]["dsn"], $config["db"]["user"], $config["db"]["pass"]);

class Model_Player extends atk4\data\Model {
    public $table = "player";
    function init(){
        parent::init();
        $this->addField("name");
    }

}
class Model_Result extends atk4\data\Model {
    public $table = "result";
    function init(){
        parent::init();
        $this->hasOne("player_id", "Player");
        $this->addField("game_id");
        $this->addField("win");
        $this->addField("color");
        $this->addField("dts");
        $this->addField("goals");
        $this->addField("goals_opponent");
    }
}
$mp = new Model_Player($db);
$m = new Model_Result($db);
/*
 * Handle Data request by TimeChart
 * */
header("Content-type: application/json;charset=utf-8");
foreach ($mp as $row){
    $player[$row["id"]] = $row["name"];
}
$fields = [];
foreach ($m as $row){
    if (!$fields){
        $fields = array_keys($m->get());
    }
    $out[] = array_merge($m->get(), ["name" => $player[$row["player_id"]]]);
}
$fields[] = "name";
foreach ($fields as $field){
    $meta[$field] = ["generated" => false, "exists" => count($out), "column" => $field, "date" => ($field == "dts")?true:false, "hidden" => false, "value" => false];
}

$ret = [
    "meta" => $meta,
    "data" => $out,
    "tableHeader" => preg_replace("/</", "&lt;", "<th>" . implode("</th><th>", $fields) . "</th>"),
    "tableRowTemplate" => preg_replace("/</", "&lt;", '<td>${' . implode('}</td><td>${', $fields) . '}</td>')
];

echo json_encode($ret, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);


