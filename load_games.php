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
class Model_Game extends atk4\data\Model {
    public $table = "game";
    function init(){
        parent::init();
        $this->addField("goals_blue");
        $this->addField("goals_red");
        $this->addField("created_dts");
        $this->addField("starting_ball");
        $this->addField("player_red_def");
        $this->addField("player_red_off");
        $this->addField("player_blue_def");
        $this->addField("player_blue_off");
        $this->addField("is_finished");
        $this->addField("game_end_dts");
        $this->addField("is_red_win");
        $this->addField("winner_team");
        $this->addField("session_id");
        $this->addField("game_length");
    }
}
$mp = new Model_Player($db);
$m = new Model_Game($db);
/*
 * Handle Data request by TimeChart
 * */
header("Content-type: application/json;charset=utf-8");
foreach ($mp as $row){
    $player[$row["id"]] = $row["name"];
}
$fields = [];

$names = [
    "player_red_def",
    "player_red_off",
    "player_blue_def",
    "player_blue_off"
];
foreach ($m as $row){
    if (!$fields){
        $fields = array_keys($m->get());
    }
    $t = $m->get();

    foreach ($names as $name){
        $t[$name . "_name"] = $player[$t[$name]];
        unset($t[$name]);
    }
    $out[] = $t;
}
foreach ($names as $name){
    array_unshift($fields, $name . "_name");
    unset($fields[array_search($name, $fields)]);
}
/* move id to back */
unset($fields["id"]);
$fields[] = "id";

foreach ($fields as $field){
    $meta[$field] = ["generated" => false, "exists" => count($out), "column" => $field, "date" => ($field == "created_dts")?true:false, "hidden" => false, "value" => false];
}

$ret = [
    "meta" => $meta,
    "data" => $out,
    "tableHeader" => preg_replace("/</", "&lt;", "<th>" . implode("</th><th>", $fields) . "</th>"),
    "tableRowTemplate" => preg_replace("/</", "&lt;", '<td>${' . implode('}</td><td>${', $fields) . '}</td>')
];

echo json_encode($ret, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);


