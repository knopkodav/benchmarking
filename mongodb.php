<?php

require 'Benchmark/Timer.php';

$timer = new Benchmark_Timer();

$mongo_cnn = new Mongo();
$mongo = $mongo_cnn->selectDB( "php_db" )->selectCollection( "dmdb" );

$points = array(1000, 10000, 100000, 500000, 1000000);

foreach($points as $val){
  $startmark = $val . ' start';
  $stopmark = $val . ' stop';
  
  $timer->setMarker($startmark);
  cycle($val);
  $timer->setMarker($stopmark);
  
  print $val . ': ' . $timer->timeElapsed($startmark, $stopmark) . "\n";
}

function cycle($c){
  global $mongo;
  
  $mongo->remove();
  
  for($i = 0; $i<$c; $i++){
    $doc = array(
      "text" => "i am any text",
      "count" => $i,
      "coords" => (object)array( "x" => 100, "y" => 200, "z" => $i )
    );
    
    $mongo->insert($doc);
  }
}

?>
