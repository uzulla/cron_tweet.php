# Cron Tweeter

This used in YAPC::Asia Tokyo 2015.

## usage.

### 0. composer install

```
$ composer install
```

see also

* ![Composer] https://getcomposer.org/

### 1. make post data

```
Y/m/d H:i:s[\t]Tweet Text
```

see data.tsv

### 2. set to cron.

```
# example
* * * * * /path/to/php /path/to/cron.php
```

## reset last execute date.

```
$ rm last_run_time.txt
```

## LICENSE

see LICENSE.
