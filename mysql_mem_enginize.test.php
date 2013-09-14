<?php
/**
 * Filler script for mysql_mem_enginize.php
 * @author Yannick Warnier <yannick.warnier@beeznest.com>
 * @license GNU/AGPL
 */
/**
 * Block execution from web
 */
if (strcmp(PHP_SAPI,'cli')!=0) {
    die("This script cannot be executed from anywhere else than the command line, sorry!\n");
}
/**
 * DB connection
 */
$dbHost = 'localhost';
$dbPort = '3365';
$dbUser = 'testmem';
$dbPass = 'testmem';
$dbName = 'testmem';
$records = 100000;
$m = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if (mysqli_connect_errno()) {
    printf("DB connect failed: %s\n", mysqli_connect_error());
    exit();
}
$m->query("create table testmem1 (id int unsigned not null AUTO_INCREMENT PRIMARY KEY, testname varchar(200) default '', testtext text default '', testint int default 0, testchar char(10) default '', testblob blob default '') ENGINE InnoDB");
$m->query("create table testmem2 (id int unsigned not null AUTO_INCREMENT PRIMARY KEY, testname varchar(200) default '', testint int default 0, testchar char(10) default '') ENGINE InnoDB");
// Now run a lot of inserts in the second table
for ($i = 0; $i < $records; $i+30) {
    $value = chr(($i%30)+40).chr((($i+1)%30)+40).chr((($i+2)%30)+40);
    $q = "insert into testmem2 (testname, testint, testchar) values ".
        "('$value', $i, '$value$value$value'),".
        "('".$value."0', ".$i++.", '$value$value$value'),".
        "('".$value."a', ".$i++.", '$value$value$value'),".
        "('".$value."b', ".$i++.", '$value$value$value'),".
        "('".$value."c', ".$i++.", '$value$value$value'),".
        "('".$value."d', ".$i++.", '$value$value$value'),".
        "('".$value."e', ".$i++.", '$value$value$value'),".
        "('".$value."f', ".$i++.", '$value$value$value'),".
        "('".$value."g', ".$i++.", '$value$value$value'),".
        "('".$value."h', ".$i++.", '$value$value$value'),".
        "('".$value."i', ".$i++.", '$value$value$value'),".
        "('".$value."j', ".$i++.", '$value$value$value'),".
        "('".$value."k', ".$i++.", '$value$value$value'),".
        "('".$value."l', ".$i++.", '$value$value$value'),".
        "('".$value."m', ".$i++.", '$value$value$value'),".
        "('".$value."n', ".$i++.", '$value$value$value'),".
        "('".$value."o', ".$i++.", '$value$value$value'),".
        "('".$value."p', ".$i++.", '$value$value$value'),".
        "('".$value."q', ".$i++.", '$value$value$value'),".
        "('".$value."r', ".$i++.", '$value$value$value'),".
        "('".$value."s', ".$i++.", '$value$value$value'),".
        "('".$value."t', ".$i++.", '$value$value$value'),".
        "('".$value."u', ".$i++.", '$value$value$value'),".
        "('".$value."v', ".$i++.", '$value$value$value'),".
        "('".$value."w', ".$i++.", '$value$value$value'),".
        "('".$value."x', ".$i++.", '$value$value$value'),".
        "('".$value."y', ".$i++.", '$value$value$value'),".
        "('".$value."z', ".$i++.", '$value$value$value'),".
        "('".$value."1', ".$i++.", '$value$value$value'),".
        "('".$value."2', ".$i++.", '$value$value$value')";
    $m->query($q);
}
echo "Done inserting $records records in testmem2\n";

