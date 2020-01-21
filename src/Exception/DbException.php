<?php
/**
 * Created by PhpStorm.
 * User: misfitpixel
 * Date: 4/16/19
 * Time: 3:03 PM
 */

namespace MisfitPixel\Exception;


use MisfitPixel\Exception\Abstraction\BaseException;

/**
 * Class DbException
 * @package MisfitPixel\Exception
 */
class DbException extends BaseException
{
    /**
     * @return array
     */
    public function getData(): array
    {
        return [];
    }
}