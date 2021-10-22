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


echo "synch redis keys with prefix ".$prefix.PHP_EOL;

$redis = new Redis();
$redis->connect($redis_server,$redis_port);
$dbh = new PDO($dsn, $user, $password);

$sql = "SELECT a.* FROM ".$schema_cdc.".".$table." a  WHERE a.in_redis = 0 ORDER BY a.waktu ASC";
foreach ($dbh->query($sql) as $row) {
    
    echo "processing id = ".$row['id']." ( ".$row['parent_id']." ) with mode ".$row['status'].PHP_EOL;
    
    if(strtolower($row['status']) == "delete"){
        
        $redis->del($prefix.$row['parent_id']);
        
    }else{
        $sql = "SELECT * FROM ".$schema.".".$table." WHERE id = '".$row['parent_id']."'";
    
        $stmt = $dbh->query($sql,PDO::FETCH_ASSOC);
        
        if($stmt){
        
            $row2 = $stmt->fetch();
         
            $row_json = json_encode($row2);
            
            $redis->set($prefix.$row2[$pk],$row_json);
            
        }else{
            echo "TERDETEKSI KEMUNGKINAN DIHAPUS, SKIP".PHP_EOL;
        }
    }
    $sql = "UPDATE ".$schema_cdc.".".$table." SET in_redis = 1 WHERE id='".$row['id']."'";
    $dbh->exec($sql);
    
}
unset($dbh);
$redis->close();

echo "DONE".PHP_EOL;