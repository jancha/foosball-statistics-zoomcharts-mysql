<?php

include "vendor/autoload.php";

$db = new atk4\data\Persistence_SQL("mysql:host=localhost;dbname=test", "root", "root");

class Model_Rate extends atk4\data\Model {
    public $table = "rate";
    function init(){
        parent::init();
        $this->addField("dat");
        $this->addField("bid");
        $this->addField("ask");
    }
}

