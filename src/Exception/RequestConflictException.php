<?php
/**
 * Created by PhpStorm.
 * User: misfitpixel
 * Date: 5/31/19
 * Time: 10:23 AM
 */

namespace MisfitPixel\Exception;


use MisfitPixel\Exception\Abstraction\BaseException;

/**
 * Class RequestConflictException
 * @package MisfitPixel\Exception
 */
class RequestConflictException extends BaseException
{
    /** @var int  */
    protected $statusCode = 409;

    /** @var string  */
    protected $message = 'Conflict';

    /**
     * @return array
     */
    public function getData(): array
    {
        return [];
    }
}