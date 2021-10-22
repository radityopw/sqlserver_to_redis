<?php 

if(count($argv) != 2){
    echo "usage : php -f clear_redis.php <location config>".PHP_EOL;
    exit;
}

$conf = $argv[1];

require $conf;

if (!isset($table)){
    echo "wrong configuration".PHP_EOL;
    exit;
}

$prefix = $table.":";


echo "synch all redis keys with prefix ".$prefix.PHP_EOL;

$redis = new Redis();
$redis->connect($redis_server,$redis_port);
$dbh = new PDO($dsn, $user, $password);

$sql = "SELECT * FROM ".$schema.".".$table." ".$where_condition;
foreach ($dbh->query($sql,PDO::FETCH_ASSOC) as $row) {
    
    echo "processing ".$pk." = ".$row[$pk].PHP_EOL;
 
    $row_json = json_encode($row);
    
    $redis->set($prefix.$row[$pk],$row_json);
    
}
unset($dbh);
$redis->close();

echo "DONE".PHP_EOL;