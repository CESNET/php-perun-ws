<?php

namespace PerunWs\Util;


/**
 * A simple parser for parsing a comma separated non-zero numbers.
 */
class CsvParser
{

    /**
     * @var string
     */
    protected $delimiter = ',';


    /**
     * @return string
     */
    public function getDelimiter()
    {
        return $this->delimiter;
    }


    /**
     * @param string $delimiter
     */
    public function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;
    }


    /**
     * Parses a comma separated integer non-zero values and returns them in an array.
     * 
     * @param string $input
     * @throws \InvalidArgumentException
     * @return array|null
     */
    public function parse($input)
    {
        if (! $input) {
            return null;
        }
        
        $values = explode($this->delimiter, $input);
        if (empty($values)) {
            return null;
        }
        
        foreach ($values as $index => $value) {
            $value = intval($value);
            if (0 === $value) {
                throw new \InvalidArgumentException(sprintf("Invalid input value '%s', should be numbers separated with '%s'", $input, $this->delimiter));
            }
            
            $values[$index] = $value;
        }
        
        return $values;
    }
}