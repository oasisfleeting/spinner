<?php
/**
 * MIT License
 * ===========
 *
 * Copyright (c) 2013 Code Green Creative <jesse@codegreencreative.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
 * CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @category   Libraries
 * @package    Libraries
 * @subpackage Libraries
 * @author     Jesse Vista <jesse@codegreencreative.com>
 * @copyright  2013 Code Green Creative.
 * @license    http://www.opensource.org/licenses/mit-license.php  MIT License
 * @version    2.0.0
 * @link       http://codegreencreative.com
 */

namespace CodeGreenCreative\Spinner;

// use \Config;
// use \Session;

class Spinner
{
    // The content we are going to spin
    protected $content = null;

    // By default we want to show a specific spin on a per page basis
    protected $seed_page_name = true;

    // Opening character starting a separated list of words
    protected $opening_construct = '{';

    // Closing character ending a separated list of words
    protected $closing_construct = '}';

    // Word separator character
    protected $separator = '|';

    /**
     * Set the content to be spun
     */
    public function setContent($content = null)
    {
        // If content is null just return
        if (is_null($content)) {
            return;
        }
        $this->content = trim($content);
    }

    /**
     * Return the content string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Main spin method to get spinning of content
     * Special thanks to Paul Norman, of whom wrote the oringinal script
     * http://www.paul-norman.co.uk/2009/06/spin-text-for-seo/
     */
    public function spin()
    {
        # If we have nothing to spin just exit
        if(is_null($this->content) && !strpos($this->content, $this->opening_construct) === false) {
            return $this->content;
        }

        # Find all positions of the starting and opening braces
        $startPositions = self::strpos_all($this->content, $this->opening_construct);
        $endPositions   = self::strpos_all($this->content, $this->closing_construct);

        # There must be the same number of opening constructs to closing ones
        if($startPositions === false OR count($startPositions) !== count($endPositions))
        {
            return $this->content;
        }

        # Optional, always show a particular combination on the page
        if($this->seed_page_name)
        {
            mt_srand(crc32($_SERVER['REQUEST_URI']));
        }

        # Might as well calculate these once
        $openingConstructLength = mb_strlen($this->opening_construct);
        $closingConstructLength = mb_strlen($this->closing_construct);

        # Organise the starting and opening values into a simple array showing orders
        foreach($startPositions as $pos)
        {
            $order[$pos] = 'open';
        }
        foreach($endPositions as $pos)
        {
            $order[$pos] = 'close';
        }
        ksort($order);

        # Go through the positions to get the depths
        $depth = 0;
        $chunk = 0;
        foreach($order as $position => $state)
        {
            if($state == 'open')
            {
                $depth++;
                $history[] = $position;
            }
            else
            {
                $lastPosition   = end($history);
                $lastKey        = key($history);
                unset($history[$lastKey]);

                $store[$depth][] = mb_substr($this->content, $lastPosition + $openingConstructLength, $position - $lastPosition - $closingConstructLength);
                $depth--;
            }
        }
        krsort($store);

        # Remove the old array and make sure we know what the original state of the top level spin blocks was
        unset($order);
        $original = $store[1];

        # Move through all elements and spin them
        foreach($store as $depth => $values)
        {
            foreach($values as $key => $spin)
            {
                # Get the choices
                $choices = explode($this->separator, $store[$depth][$key]);
                $replace = $choices[mt_rand(0, count($choices) - 1)];

                # Move down to the lower levels
                $level = $depth;
                while($level > 0)
                {
                    foreach($store[$level] as $k => $v)
                    {
                        $find = $this->opening_construct.$store[$depth][$key].$this->closing_construct;
                        if($level == 1 AND $depth == 1)
                        {
                            $find = $store[$depth][$key];
                        }
                        $store[$level][$k] = self::str_replace_first($find, $replace, $store[$level][$k]);
                    }
                    $level--;
                }
            }
        }

        # Put the very lowest level back into the original string
        foreach($original as $key => $value)
        {
            $this->content = self::str_replace_first($this->opening_construct.$value.$this->closing_construct, $store[1][$key], $this->content);
        }

        return $this->content;
    }

    /**
     * Similar to str_replace, but only replaces the first instance of the needle
     */
    private function str_replace_first($find, $replace, $string)
    {
        # Ensure we are dealing with arrays
        if(!is_array($find))
        {
            $find = array($find);
        }

        if(!is_array($replace))
        {
            $replace = array($replace);
        }

        foreach($find as $key => $value)
        {
            if(($pos = mb_strpos($string, $value)) !== false)
            {
                # If we have no replacement make it empty
                if(!isset($replace[$key]))
                {
                    $replace[$key] = '';
                }

                $string = mb_substr($string, 0, $pos).$replace[$key].mb_substr($string, $pos + mb_strlen($value));
            }
        }

        return $string;
    }

    /**
     * Finds all instances of a needle in the haystack and returns the array
     */
    private function strpos_all($haystack, $needle)
    {
        $offset = 0;
        $i      = 0;
        $return = false;

        while(is_integer($i))
        {
            $i = mb_strpos($haystack, $needle, $offset);

            if(is_integer($i))
            {
                $return[]   = $i;
                $offset     = $i + mb_strlen($needle);
            }
        }

        return $return;
    }
}
