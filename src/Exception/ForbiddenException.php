<?php
/**
 * Created by PhpStorm.
 * User: misfitpixel
 * Date: 3/28/19
 * Time: 1:46 PM
 */

namespace MisfitPixel\Exception;


use MisfitPixel\Exception\Abstraction\BaseException;

/**
 * Class ForbiddenException
 * @package MisfitPixel\Exception
 */
class ForbiddenException extends BaseException
{
    /** @var int  */
    protected $statusCode = 403;

    /** @var string  */
    protected $message = 'Forbidden';

    /**
     * @return array
     */
    public function getData(): array
    {
        return [];
    }
}