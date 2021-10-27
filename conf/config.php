<?php 

// konfigurasi tabel
$table = "mahasiswa";
$schema = "dbo";
$schema_cdc = "cdc";
$pk = "id";
$redis_prefix = $table;
$where_condition = "WHERE 1=1";
$select_cols = "*";

// koneksi ke database 
$dsn = 'sqlsrv:Database=sych_redis;Server=127.0.0.1';
$user = 'sync_redis';
$password = 'sync_redis';

// koneksi ke redis 
$redis_server = "127.0.0.1";
$redis_port = "6379";