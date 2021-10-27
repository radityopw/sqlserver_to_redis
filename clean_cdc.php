<?php 

if(count($argv) != 3){
    echo "usage : php -f clean_cdc.php <location config> <days_to_keep>".PHP_EOL;
    exit;
}

$conf = $argv[1];
$days = $argv[2];

require $conf;

if (!isset($table)){
    echo "wrong configuration".PHP_EOL;
    exit;
}


echo "clean cdc table ".$table." in ".$days." days".PHP_EOL;

$dbh = new PDO($dsn, $user, $password);


$sql = "DELETE FROM ".$schema_cdc.".".$table." WHERE in_redis = 1 AND waktu < DATEADD(day,-".$days.",getdate())";

$rows = $dbh->exec($sql);

unset($dbh);

echo $rows." rows deleted".PHP_EOL;

echo "DONE".PHP_EOL;