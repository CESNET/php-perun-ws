<?php

namespace PerunWsTest\Util;

use PerunWs\Util\CsvParser;


class CsvParserTest extends \PHPUnit_Framework_TestCase
{


    /**
     * @dataProvider inputs
     */
    public function testParse($input, $output, $exceptionName)
    {
        if ($exceptionName) {
            $this->setExpectedException($exceptionName);
        }
        
        $parser = new CsvParser();
        
        $this->assertSame($output, $parser->parse($input));
    }


    public function inputs()
    {
        return array(
            array(
                'input' => null,
                'output' => null,
                'exceptionName' => null
            ),
            array(
                'input' => '123',
                'output' => array(
                    123
                ),
                'exceptionName' => null
            ),
            array(
                'input' => '123,456,789',
                'output' => array(
                    123,
                    456,
                    789
                ),
                'exceptionName' => null
            ),
            array(
                'input' => '123 ,456, 789',
                'output' => array(
                    123,
                    456,
                    789
                ),
                'exceptionName' => null
            ),
            array(
                'input' => '',
                'output' => null,
                'exceptionName' => null
            ),
            array(
                'input' => '123, abc,456',
                'output' => null,
                'exceptionName' => 'InvalidArgumentException'
            )
        );
    }
}