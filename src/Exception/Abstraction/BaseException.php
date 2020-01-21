<?php
/**
 * Created by PhpStorm.
 * User: misfitpixel
 * Date: 3/14/19
 * Time: 3:54 PM
 */

namespace MisfitPixel\Exception\Abstraction;


use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class BaseException
 * @package MisfitPixel\Exception\Abstraction
 */
abstract class BaseException extends HttpException
{
    /** @var int  */
    protected $statusCode = 500;

    /** @var string  */
    protected $message = "An unexpected error occurred.";

    /** @var null|string */
    protected $template = null;

    /**
     * BaseException constructor.
     * @param string|null $message
     * @param \Exception|null $previous
     * @param array $headers
     * @param int|null $code
     */
    public function __construct(string $message = null, \Exception $previous = null, array $headers = array(), ?int $code = 0)
    {
        if($message === null) {
            $message = $this->message;
        }

        parent::__construct($this->statusCode, $message, $previous, $headers, $code);
    }

    /**
     * @return null|string
     */
    public function getTemplate(): ?string
    {
        return $this->template;
    }

    abstract function getData(): array;
}