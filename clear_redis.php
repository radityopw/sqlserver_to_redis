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

echo "deleting redis keys with prefix ".$prefix.PHP_EOL;

$redis = new Redis();
$redis->connect($redis_server,$redis_port);

$all_keys = $redis->keys($prefix."*");

$redis->del($all_keys);

$redis->close();

echo "DONE".PHP_EOL;