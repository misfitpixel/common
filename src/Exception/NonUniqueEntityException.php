<?php
/**
 * Created by PhpStorm.
 * User: misfitpixel
 * Date: 4/16/19
 * Time: 2:39 PM
 */

namespace Exception;


use Symfony\Component\HttpFoundation\Response;

/**
 * Class NonUniqueEntityException
 * @package App\Exception
 */
class NonUniqueEntityException extends EntityNotFoundException
{
    /** @var int */
    protected $statusCode = Response::HTTP_CONFLICT;

    /** @var string  */
    protected $message = 'This record already exists';

    /**
     * NonUniqueEntityException constructor.
     * @param null|string $message
     * @param null $entity
     * @param \Exception|null $previous
     * @param array $headers
     * @param int|null $code
     */
    public function __construct(?string $message = null, $entity = null, ?\Exception $previous = null, array $headers = array(), ?int $code = 0)
    {
        parent::__construct($message, $entity, $previous, $headers, $code);

        if($this->entity != null) {
            $this->message = sprintf('This %s already exists', ucfirst($this->entity));
        }
    }
}