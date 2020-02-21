<?php


namespace MisfitPixel\Exception;


use MisfitPixel\Exception\Abstraction\BaseException;

/**
 * Class TimeoutException
 * @package MisfitPixel\Exception
 */
class TimeoutException extends BaseException
{
    /** @var int  */
    protected $statusCode = 408;

    /** @var string  */
    protected $message = "Request Timeout";

    /**
     * @return array
     */
    public function getData(): array
    {
        return [];
    }
}