<?php
/**
 * Created by PhpStorm.
 * User: misfitpixel
 * Date: 3/28/19
 * Time: 1:52 PM
 */

namespace MisfitPixel\Exception;


use MisfitPixel\Exception\Abstraction\BaseException;

/**
 * Class UnauthorizedException
 * @package MisfitPixel\Exception
 */
class UnauthorizedException extends BaseException
{
    /** @var int  */
    protected $statusCode = 401;

    /** @var string  */
    protected $message = 'Unauthorized';

    /**
     * @return array
     */
    public function getData(): array
    {
        return [];
    }
}