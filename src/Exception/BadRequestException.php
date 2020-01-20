<?php
/**
 * Created by PhpStorm.
 * User: misfitpixel
 * Date: 6/27/19
 * Time: 1:45 PM
 */

namespace Exception;


use Exception\Abstraction\BaseException;

/**
 * Class BadRequestException
 * @package App\Exception
 */
class BadRequestException extends BaseException
{
    /** @var int  */
    protected $statusCode = 400;

    /** @var string  */
    protected $message = 'Bad request';

    /**
     * @return array
     */
    public function getData(): array
    {
        return [];
    }
}