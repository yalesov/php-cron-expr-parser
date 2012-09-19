<?php
namespace Heartsentwined\CronExprParser\Exception;

use Heartsentwined\CronExprParser\ExceptionInterface;

class InvalidArgumentException
    extends \InvalidArgumentException
    implements ExceptionInterface
{
}
