<?php
/**
 * Created by PhpStorm.
 * User: misfitpixel
 * Date: 3/28/19
 * Time: 1:46 PM
 */

namespace Exception;


use Exception\Abstraction\BaseException;

/**
 * Class ForbiddenException
 * @package App\Exception
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