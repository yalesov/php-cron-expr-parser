<?php
namespace Yalesov\Test\CronExprParser;

use Yalesov\CronExprParser\Parser;

class ParserTest extends \PHPUnit_Framework_TestCase
{
  public function testExprToNumeric()
  {
    $data = array(
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
    foreach ($data as $str => $num) {
      $this->assertSame($num,
        Parser::exprToNumeric($str));
    }

    // variants of case
    $this->assertSame(1,
      Parser::exprToNumeric('jan'));
    $this->assertSame(1,
      Parser::exprToNumeric('JAN'));
    $this->assertSame(1,
      Parser::exprToNumeric('Jan'));
    $this->assertSame(1,
      Parser::exprToNumeric('jAn'));
    $this->assertSame(1,
      Parser::exprToNumeric('jaN'));

    // long forms
    $this->assertSame(1,
      Parser::exprToNumeric('january'));
    $this->assertSame(1,
      Parser::exprToNumeric('janrubbish'));

    // invalid string input
    $this->assertSame(false,
      Parser::exprToNumeric(''));
    $this->assertSame(false,
      Parser::exprToNumeric('rubbish'));

    // straight numeric input
    $this->assertSame(1,
      Parser::exprToNumeric(1));
    // invalid numeric input
    $this->assertSame(false,
      Parser::exprToNumeric(13));
  }

  /**
   * @depends testExprToNumeric
   */
  public function testMatchTimeComponent()
  {
    // anything
    $this->assertTrue(
      Parser::matchTimeComponent('*', 0));
    $this->assertTrue(
      Parser::matchTimeComponent('*', 1));

    // comma-separated
    $this->assertFalse(
      Parser::matchTimeComponent('1,2,3', 0));
    $this->assertTrue(
      Parser::matchTimeComponent('1,2,3', 1));
    $this->assertTrue(
      Parser::matchTimeComponent('1,2,3', 2));
    $this->assertTrue(
      Parser::matchTimeComponent('1,2,3', 3));
    $this->assertFalse(
      Parser::matchTimeComponent('1,2,3', 4));

    // modulus
    $this->assertFalse(
      Parser::matchTimeComponent('*/5', 1));
    $this->assertTrue(
      Parser::matchTimeComponent('*/5', 0));
    $this->assertTrue(
      Parser::matchTimeComponent('*/5', 5));

    // range
    $this->assertFalse(
      Parser::matchTimeComponent('1-3', 0));
    $this->assertTrue(
      Parser::matchTimeComponent('1-3', 1));
    $this->assertTrue(
      Parser::matchTimeComponent('1-3', 2));
    $this->assertTrue(
      Parser::matchTimeComponent('1-3', 3));
    $this->assertFalse(
      Parser::matchTimeComponent('1-3', 4));

    // string
    $this->assertTrue(
      Parser::matchTimeComponent('mar', 3));
    $this->assertFalse(
      Parser::matchTimeComponent('mar', 4));

    // combinations

    $this->assertTrue(
      Parser::matchTimeComponent('0,2,4/2', 0));
    $this->assertTrue(
      Parser::matchTimeComponent('0,2,4/2', 2));
    $this->assertTrue(
      Parser::matchTimeComponent('0,2,4/2', 4));
    $this->assertFalse(
      Parser::matchTimeComponent('0,2,4/2', 1));
    $this->assertFalse(
      Parser::matchTimeComponent('0,2,4/2', 3));
    $this->assertFalse(
      Parser::matchTimeComponent('0,2,4/2', 5));

    $this->assertTrue(
      Parser::matchTimeComponent('0-4/2', 0));
    $this->assertTrue(
      Parser::matchTimeComponent('0-4/2', 2));
    $this->assertTrue(
      Parser::matchTimeComponent('0-4/2', 4));
    $this->assertFalse(
      Parser::matchTimeComponent('0-4/2', 1));
    $this->assertFalse(
      Parser::matchTimeComponent('0-4/2', 3));
    $this->assertFalse(
      Parser::matchTimeComponent('0-4/2', 5));

    $this->assertTrue(
      Parser::matchTimeComponent('sun,tue,thu/2', 0));
    $this->assertTrue(
      Parser::matchTimeComponent('sun,tue,thu/2', 2));
    $this->assertTrue(
      Parser::matchTimeComponent('sun,tue,thu/2', 4));
    $this->assertFalse(
      Parser::matchTimeComponent('sun,tue,thu/2', 1));
    $this->assertFalse(
      Parser::matchTimeComponent('sun,tue,thu/2', 3));
    $this->assertFalse(
      Parser::matchTimeComponent('sun,tue,thu/2', 5));

    $this->assertTrue(
      Parser::matchTimeComponent('sun-thu/2', 0));
    $this->assertTrue(
      Parser::matchTimeComponent('sun-thu/2', 2));
    $this->assertTrue(
      Parser::matchTimeComponent('sun-thu/2', 4));
    $this->assertFalse(
      Parser::matchTimeComponent('sun-thu/2', 1));
    $this->assertFalse(
      Parser::matchTimeComponent('sun-thu/2', 3));
    $this->assertFalse(
      Parser::matchTimeComponent('sun-thu/2', 5));

    $this->assertTrue(
      Parser::matchTimeComponent('0-thu/2', 0));
    $this->assertTrue(
      Parser::matchTimeComponent('0-thu/2', 2));
    $this->assertTrue(
      Parser::matchTimeComponent('0-thu/2', 4));
    $this->assertFalse(
      Parser::matchTimeComponent('0-thu/2', 1));
    $this->assertFalse(
      Parser::matchTimeComponent('0-thu/2', 3));
    $this->assertFalse(
      Parser::matchTimeComponent('0-thu/2', 5));

    $this->assertTrue(
      Parser::matchTimeComponent('sun-4/2', 0));
    $this->assertTrue(
      Parser::matchTimeComponent('sun-4/2', 2));
    $this->assertTrue(
      Parser::matchTimeComponent('sun-4/2', 4));
    $this->assertFalse(
      Parser::matchTimeComponent('sun-4/2', 1));
    $this->assertFalse(
      Parser::matchTimeComponent('sun-4/2', 3));
    $this->assertFalse(
      Parser::matchTimeComponent('sun-4/2', 5));
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testExprModulusEmpty()
  {
    Parser::matchTimeComponent('/', 2);
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testExprModulusNoDividend()
  {
    Parser::matchTimeComponent('/2', 2);
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testExprModulusNoDivisor()
  {
    Parser::matchTimeComponent('2/', 2);
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testExprModulusTooManyArgs()
  {
    Parser::matchTimeComponent('2/3/4', 2);
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testExprModulusStringDivisor()
  {
    Parser::matchTimeComponent('2/foo', 2);
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testExprRangeEmpty()
  {
    Parser::matchTimeComponent('-', 2);
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testExprRangeNoFrom()
  {
    Parser::matchTimeComponent('-2', 2);
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testExprRangeNoTo()
  {
    Parser::matchTimeComponent('2-', 2);
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testExprRangeTooManyArgs()
  {
    Parser::matchTimeComponent('2-3-4', 2);
  }

  public function testMatchTime()
  {
    $this->assertTrue(
      Parser::matchTime(time(), '* * * * *'));
    $this->assertTrue(
      Parser::matchTime('now', '* * * * *'));
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testMatchTimeTooFewArgs()
  {
    Parser::matchTime('now', '* * * *');
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testMatchTimeTooManyArgs()
  {
    Parser::matchTime('now', '* * * * * *');
  }
}
