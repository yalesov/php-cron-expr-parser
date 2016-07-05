# Yalesov\CronExprParser

[![Build Status](https://secure.travis-ci.org/yalesov/cron-expr-parser.png)](http://travis-ci.org/yalesov/cron-expr-parser)

Parse cron expressions and match them against time.

# Installation

[Composer](http://getcomposer.org/):

```json
{
    "require": {
        "yalesov/cron-expr-parser": "2.*"
    }
}
```

# Usage

Parse a Cron expression and a time, and determine if the given time falls within the given cron expression.

```php
use Yalesov\CronExprParser\Parser;
$match      = Parse::matchTime('next Thursday', '* * * * 4');
$notMatch   = Parse::matchTime('next Friday', '* * * * 4');
```

Function signature:

```php
public static function matchTime($time, $expr)
```

`$time` is either a timestamp, or a [`strtotime`](http://php.net/manual/en/function.strtotime.php)-compatible string.

`$expr` is any valid cron expression, in addition supporting:
- range: `0-5`
- range + interval: `10-59/5`
- comma-separated combinations of these: `1,4,7,10-20`
- English months: `january`
- English months (abbreviated to three letters): `jan`
- English weekdays: `monday`
- English weekdays (abbreviated to three letters): `mon`
- These text counterparts can be used in all places where their numerical counterparts are allowed, e.g. `jan-jun/2`
- A full example: `0-5,10-59/5 * 2-10,15-25 january-june/2 mon-fri` (every minute between minute 0-5 + every 5th minute between 10-59; every hour; every day between day 2-10 and day 15-25; every 2nd month between January-June; Monday-Friday)
