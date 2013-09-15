convert2mysqlmem
================

Convert MySQL tables (InnoDB or MyISAM) to the MEMORY engine, switching fields to fixed length first.

The MySQL's MEMORY engine is a special engine that puts the whole table in memory, to possibly boost its efficiency. You should not blindly adopt the MEMORY engine to optimize your tables. Sometimes it might just not be appropriate (or more efficient).
The MEMORY engine puts your table in a volatile state, which means that anything written to it will be deleted if either the database server (software) or the physical machine are shut down. As such, it is mostly useful for static tables, not tables to which you write frequently, unless those records are not important.

See http://dev.mysql.com/doc/refman/5.5/en/memory-storage-engine.html

This very small script doesn't require any installation.

Requirements
------------

You only need php-cli and php-mysqli (and obviously a MySQL or MariaDB database) to run the script.

Configuration
-------------

To run your script, you will first need to update the database access details (you'll have to repeat that if you want to use the test script) at the beginning of the script. By default, it looks like this:

```php
$dbHost = 'localhost';
$dbPort = '3306';
$dbUser = 'testmem';
$dbPass = 'testmem';
$dbName = 'testmem';
$backupTableSuffix = 'BKP'; //which suffix to put to the original table, renamed
$tables = array('testmem1','testmem2'); //names of the tables to pass to MEMORY
```

Just replace those with your own values and run the script

Execution
---------

Once configured, just launch

```
  php mysql_mem_enginize.php
```

This will:
- save a backup copy of your table with a suffix (as configured, defaults to 'BKP' suffix)
- update all variable-length fields to fixed-length fields (except TEXT & BLOB)
- convert the table to MEMORY engine, ready for you to run some stress tests

Roadmap
-------

One issue at the moment is that the table that has been copied away (as backup) will not be available with its original name when rebooting the server. Somehow this script should implement a mechanism to recover the tables at startup.

One solution would be to ask for some clever SQL command to be executed by the init_file directive of my.cnf, but it is unlikely it would be possible at all (kind of "change engine for all tables ending with $suffix").

Another solution is to run an additional init script on your system (after mysql is started) to check those suffixed tables and see if there is a non-suffixed equivalent, and if there isn't, just remove the suffix... or something like that :-p
