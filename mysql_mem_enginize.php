<?php
/**
 * This script updates a table to pass all its fields to fixed width and change
 * the engine to MySQL's MEMORY engine.
 * The MEMORY engine is a special engine of MySQL that allows for tables to be
 * put entirely in RAM, which should affect the efficiency of the tables
 * considerably. However, it requires all fields to be of fixed length, and 
 * it is recommended NOT TO use such tables for writing, as the table is
 * volatile and might be destroyed if anything goes wrong with the database
 * server. Even then, there are cases where it might be very convenient to
 * be able to put a table in memory, even temporarily.
 * The present script tries to provide a mechanism by which any relatively
 * simple table (InnoDB or MyISAM) can be "adapted" to be moved to the
 * MEMORY engine. This requires fields that are *not* fixed-length to be
 * measured (max length) and the original table to be backed-up (a copy is
 * taken and given the suffixed configured below).
 * Because of the length of this type of fields, this script doesn't work with
 * TEXT or BLOG field types.
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
 * Initialization of settings - replace with your own settings
 */
$dbHost = 'localhost';
$dbPort = '3306';
$dbUser = 'testmem';
$dbPass = 'testmem';
$dbName = 'testmem';
$backupTableSuffix = 'BKP'; //which suffix to put to the original table, renamed
$tables = array('testmem1','testmem2'); //names of the tables to pass to MEMORY
// Table of translation between numeric types identifiers and litteral types
$types = array(
    3 => 'int',
    252 => 'text', //or blob
    253 => 'varchar',
    254 => 'char',
);
// The following types will be excluded from conversion (tables will be left
// unaffected but will not be converted)
$excludeTypes = array(
    'text',
);
// Only the following field types conversions will occur
$toConvert = array(
    'varchar' => 'char',
);

// For a quick test, you can insert tables in your DB using the .test.php file

/**
 * Try connecting to the database and return on error
 */
$m = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if (mysqli_connect_errno()) {
    printf("DB connect failed: %s\n", mysqli_connect_error());
    exit();
}

/**
 * Scan tables, take a copy, detect variable length fields and update
 */
foreach ($tables as $table) {
    echo "\nAnalysing table $table...\n";
    $query = "SELECT * FROM $table LIMIT 1";
    $exclude = false;
    $tableFields = array();
    if ($result = $m->query($query)) {
        $info = $result->fetch_fields();
        foreach ($info as $field) {
            if (!empty($types[$field->type])) {
                if (in_array($types[$field->type], $excludeTypes)) {
                    $exclude = true;
                    echo 'Field '.$field->name.' is of type '.$types[$field->type].", which is excluded by this script.\n";
                } else {
                    echo 'Field '.$field->name.' is of type '.$types[$field->type].' and has a max length of '.$field->max_length."\n";
                    $tableFields[$field->name] = array ('type' => $types[$field->type], 'max' => $field->max_length);
                }
            } else {
                echo "Field ".$field->name.' has a type that is not managed by this script. Consider editing the $types variable at the beginning of the script to add the type, or update the table to have one of the supported types defined in the $types variable.'."\n";
            }
        }
        mysqli_free_result($result);
        if ($exclude) {
            echo "At least one of the fields from this table cannot be converted. Sorry.\n";
        } else {
            copyTable($m, $table, $table.$backupTableSuffix); 
            echo "Backup taken for table $table as $table$backupTableSuffix\n";
            foreach ($tableFields as $field => $details) {
                $type = $details['type'];
                $max = $details['max'];
                if (!empty($toConvert[$type])) {
                    $convertTo = $toConvert[$type];
                    $alterQuery = "ALTER TABLE $table CHANGE COLUMN `$field` `$field` $convertTo ($max)";
                    echo $alterQuery."\n";
                    $m->query($alterQuery);
                }
            }
            echo "Switching to MEMORY engine\n";
            $alterTable = "ALTER TABLE $table ENGINE=MEMORY";
            $m->query($alterTable);
        }
    } else {
        echo "Could not connect to table $table\n";
    }
}
mysqli_close($m);

echo "Done!\n";

/**
 * Copy table
 */
function copyTable(&$dbh, $table, $copy) {
    $query = 'CREATE TABLE '.$copy.' LIKE '.$table;
    $dbh->query($query);
    $query = 'INSERT '.$copy.' SELECT * FROM '.$table;
    $dbh->query($query);
}
