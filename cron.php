#!/usr/bin/env php
<?php
error_reporting(E_ALL);
require "vendor/autoload.php";

set_error_handler(function($errno, $errstr, $errfile, $errline){
    $log = new \Uzulla\SLog\SimpleLogger(\Uzulla\SLog\SimpleLogger::DEBUG, __DIR__."/run.log");
    $log->error($errstr. "/" . $errfile . ":". $errline);
    throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
});
date_default_timezone_set('Asia/Tokyo');

use Carbon\Carbon;
use Uzulla\SLog\SimpleLogger as Logger;

$log = new Logger(Logger::DEBUG, __DIR__."/run.log");
$log->debug('start');

// data load from TSV ( see data.tsv.sample )
$raw_lines = explode("\n", file_get_contents(__DIR__.'/data.tsv'));
$data = [];
foreach($raw_lines as $line){
    if(mb_strlen($line)==0) continue;
    list($date, $text) = explode("\t", $line);
    $data[] = [
        'date' => Carbon::createFromFormat('Y/m/d H:i:s', $date, "Asia/Tokyo"), // FIXME. THIS IS NOT INTERNATIONAL
        'text' => $text,
    ];
}

// Check least execute datetime.
$date_needle_file_path = __DIR__ . '/last_run_time.txt';
try {
    if(!file_exists($date_needle_file_path)) {
        file_put_contents($date_needle_file_path, '0');
    }
    $date_needle = file_get_contents($date_needle_file_path);
}catch(\Exception $e){
    $log->error($e->getMessage());
    var_dump($e->getMessage());
    exit;
}
$last_date = Carbon::createFromTimestamp($date_needle);

// update last execute time.
file_put_contents($date_needle_file_path, time());

// create Twitter client.
include(__DIR__. '/config.php');
$t = new \Twitter(
    $token[0],
    $token[1],
    $token[2],
    $token[3]
);

// read data.tsv and post tweet.
$now = Carbon::create();
foreach($data as $item){
    $item_date = $item['date'];
    if(
        $item_date < $now &&
        $item_date > $last_date
    ){
        $log->info("tweet:".$item['text']."\n");
        $t->send($item['text']);
    }
}

$log->debug('done');
