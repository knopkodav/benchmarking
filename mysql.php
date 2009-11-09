<?php

require 'Benchmark/Timer.php';

$timer = new Benchmark_Timer();

$mysql = new mysqli("localhost", "testuser", "testpass", "test");

$points = array(1000, 10000, 100000, 500000, 1000000);

foreach($points as $val){
  flush_mysql();
  
  $startmark = $val . ' start';
  $stopmark = $val . ' stop';
  
  $timer->setMarker($startmark);
  cycle($val);
  $timer->setMarker($stopmark);
  
  print $val . ': ' . $timer->timeElapsed($startmark, $stopmark) . "\n";
}

$mysql->close();

function flush_mysql(){
  global $mysql;
  
  $mysql->query("DROP TABLE IF EXISTS `php_insert_bm`");
  $mysql->query("DROP TABLE IF EXISTS `php_insert_bm_coords`");
  $mysql->query("CREATE TABLE `php_insert_bm_coords`(
    `id` int unsigned not null auto_increment,
    `x` int not null,
    `y` int not null,
    PRIMARY KEY (`id`)
  )");
  $mysql->query("CREATE TABLE `php_insert_bm`(
    `id` int unsigned not null auto_increment,
    `text` varchar(255) not null,
    `count` int not null,
    `coords_id` int unsigned not null,
    PRIMARY KEY (`id`)
  )");
  # empty init entries
  $mysql->query("INSERT php_insert_bm_coords(x,y) VALUES(0, 0)");
  $mysql->query("INSERT php_insert_bm(text, count, coords_id) VALUES('', 0, " . $mysql->insert_id . ')');
}

function cycle($c){
  global $mysql;

  for($i = 0; $i<$c; $i++){
    $doc = array(
      "text" => "i am any text",
      "count" => $i,
      "coords" => (object)array("x" => 100, "y" => 200, "z" => $i)
    );
    
    $mysql->query('INSERT insert_bm_coords(x,y) VALUES(' . $doc["coords"]->x . ', ' . $doc["coords"]->y . ')');
    $mysql->query('INSERT insert_bm(text, count, coords_id) VALUES(' . $doc["text"] . ', ' . $doc["count"] . ', ' . $mysql->insert_id . ')');
  }
}

?>
