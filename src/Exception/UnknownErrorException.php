<?php
/**
 * Created by PhpStorm.
 * User: misfitpixel
 * Date: 3/20/19
 * Time: 3:13 PM
 */

namespace Exception;


use Exception\Abstraction\BaseException;

/**
 * Class UnknownErrorException
 * @package App\Exception
 */
class UnknownErrorException extends BaseException
{
    /**
     * @return array
     */
    public function getData(): array
    {
        return [];
    }
}