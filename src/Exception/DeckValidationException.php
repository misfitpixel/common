<?php
/**
 * Created by PhpStorm.
 * User: misfitpixel
 * Date: 6/13/19
 * Time: 2:33 PM
 */

namespace Exception;


use Exception\Abstraction\BaseException;

/**
 * Class DeckValidationException
 * @package App\Exception
 */
class DeckValidationException extends BaseException
{
    /** @var int  */
    protected $statusCode = 409;

    /** @var string  */
    protected $message = 'Your deck does not meet the requirements for this event';

    /**
     * @return array
     */
    public function getData(): array
    {
        return [];
    }
}