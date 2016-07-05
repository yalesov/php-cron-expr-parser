<?php
namespace Yalesov\CronExprParser;

use Yalesov\ArgValidator\ArgValidator;
use Yalesov\CronExprParser\Exception;

class Parser
{
    /**
     * determine whether a given time falls within the given cron expr
     *
     * @param string|numeric $time
     *      timestamp or strtotime()-compatible string
     * @param string $expr
     *      any valid cron expression, in addition supporting:
     *      range: '0-5'
     *      range + interval: '10-59/5'
     *      comma-separated combinations of these: '1,4,7,10-20'
     *      English months: 'january'
     *      English months (abbreviated to three letters): 'jan'
     *      English weekdays: 'monday'
     *      English weekdays (abbreviated to three letters): 'mon'
     *      These text counterparts can be used in all places where their
     *          numerical counterparts are allowed, e.g. 'jan-jun/2'
     *      A full example:
     *          '0-5,10-59/5 * 2-10,15-25 january-june/2 mon-fri' -
     *          every minute between minute 0-5 + every 5th min between 10-59
     *          every hour
     *          every day between day 2-10 and day 15-25
     *          every 2nd month between January-June
     *          Monday-Friday
     * @throws Exception\InvalidArgumentException on invalid cron expression
     * @return bool
     */
    public static function matchTime($time, $expr)
    {
        ArgValidator::assert($time, array('string', 'numeric'));
        ArgValidator::assert($expr, 'string');

        $cronExpr = preg_split('/\s+/', $expr, null, PREG_SPLIT_NO_EMPTY);
        if (count($cronExpr) !== 5) {
            throw new Exception\InvalidArgumentException(sprintf(
                 'cron expression should have exactly 5 arguments, "%s" given',
                 $expr
            ));
        }

        if (is_string($time)) $time = strtotime($time);

        $date = getdate($time);

        return self::matchTimeComponent($cronExpr[0], $date['minutes'])
            && self::matchTimeComponent($cronExpr[1], $date['hours'])
            && self::matchTimeComponent($cronExpr[2], $date['mday'])
            && self::matchTimeComponent($cronExpr[3], $date['mon'])
            && self::matchTimeComponent($cronExpr[4], $date['wday']);
    }

    /**
     * match a cron expression component to a given corresponding date/time
     *
     * In the expression, * * * * *, each component
     *      *[1] *[2] *[3] *[4] *[5]
     * will correspond to a getdate() component
     * 1. $date['minutes']
     * 2. $date['hours']
     * 3. $date['mday']
     * 4. $date['mon']
     * 5. $date['wday']
     *
     * @see self::exprToNumeric() for additional valid string values
     *
     * @param  string                             $expr
     * @param  numeric                            $num
     * @throws Exception\InvalidArgumentException on invalid expression
     * @return bool
     */
    public static function matchTimeComponent($expr, $num)
    {
        ArgValidator::assert($expr, 'string');
        ArgValidator::assert($num, 'numeric');

        //handle all match
        if ($expr === '*') {
            return true;
        }

        //handle multiple options
        if (strpos($expr, ',') !== false) {
            $args = explode(',', $expr);
            foreach ($args as $arg) {
                if (self::matchTimeComponent($arg, $num)) {
                    return true;
                }
            }

            return false;
        }

        //handle modulus
        if (strpos($expr, '/') !== false) {
            $arg = explode('/', $expr);
            if (count($arg) !== 2) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'invalid cron expression component: '
                        . 'expecting match/modulus, "%s" given',
                    $expr
                ));
            }
            if (!is_numeric($arg[1])) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'invalid cron expression component: '
                        . 'expecting numeric modulus, "%s" given',
                    $expr
                ));
            }

            $expr = $arg[0];
            $mod = $arg[1];
        } else {
            $mod = 1;
        }

        //handle all match by modulus
        if ($expr === '*') {
            $from = 0;
            $to   = 60;
        }
        //handle range
        elseif (strpos($expr, '-') !== false) {
            $arg = explode('-', $expr);
            if (count($arg) !== 2) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'invalid cron expression component: '
                        . 'expecting from-to structure, "%s" given',
                    $expr
                ));
            }
            $from = self::exprToNumeric($arg[0]);
            $to = self::exprToNumeric($arg[1]);
        }
        //handle regular token
        else {
            $from = self::exprToNumeric($expr);
            $to = $from;
        }

        if ($from === false || $to === false) {
            throw new Exception\InvalidArgumentException(sprintf(
                'invalid cron expression component: '
                    . 'expecting numeric or valid string, "%s" given',
                $expr
            ));
        }

        return ($num >= $from) && ($num <= $to) && ($num % $mod === 0);
    }

    /**
     * parse a string month / weekday expression to its numeric equivalent
     *
     * @param string|numeric $value
     *      accepts, case insensitive,
     *      - Jan - Dec
     *      - Sun - Sat
     *      - (or their long forms - only the first three letters important)
     * @return int|false
     */
    public static function exprToNumeric($value)
    {
        ArgValidator::assert($value, array('string', 'numeric'));

        static $data = array(
            'jan'   => 1,
            'feb'   => 2,
            'mar'   => 3,
            'apr'   => 4,
            'may'   => 5,
            'jun'   => 6,
            'jul'   => 7,
            'aug'   => 8,
            'sep'   => 9,
            'oct'   => 10,
            'nov'   => 11,
            'dec'   => 12,

            'sun'   => 0,
            'mon'   => 1,
            'tue'   => 2,
            'wed'   => 3,
            'thu'   => 4,
            'fri'   => 5,
            'sat'   => 6,
        );

        if (is_numeric($value)) {
            // allow all numerics values, this change fix the bug for minutes range like 0-59 or hour range like 0-20
            return $value;
        }

        if (is_string($value)) {
            $value = strtolower(substr($value, 0, 3));
            if (isset($data[$value])) {
                return $data[$value];
            }
        }

        return false;
    }
}
