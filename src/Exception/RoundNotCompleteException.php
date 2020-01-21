<?php
/**
 * Created by PhpStorm.
 * User: misfitpixel
 * Date: 6/20/19
 * Time: 3:30 PM
 */

namespace MisfitPixel\Exception;


use MisfitPixel\Exception\Abstraction\BaseException;

/**
 * Class RoundNotCompleteException
 * @package MisfitPixel\Exception
 */
class RoundNotCompleteException extends BaseException
{
    /** @var int  */
    protected $statusCode = 409;

    /** @var string  */
    protected $message = 'You can\'t start a new round until the previous one has been completed';

    /**
     * @return array
     */
    public function getData(): array
    {
        return [];
    }
}