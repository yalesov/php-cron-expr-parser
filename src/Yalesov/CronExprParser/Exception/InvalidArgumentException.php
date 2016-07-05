<?php
namespace Yalesov\CronExprParser\Exception;

use Yalesov\CronExprParser\ExceptionInterface;

class InvalidArgumentException
  extends \InvalidArgumentException
  implements ExceptionInterface
{
}
