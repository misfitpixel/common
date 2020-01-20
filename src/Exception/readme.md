#Creating Exceptions
Custom exceptions should extend from _Exception\Abstraction\BaseException.php_.

Each of these exceptions are based on the Symfony4 native HTTPException, and allow storing of an error message and an 
HTTP status code.

Whenever an exception is incurred, logic is passed through our event controller at 
_Exception\Controller\ExceptionController.php_

Each custom controller should have at least two of a possible three protected attributes initialized with default 
values:

* statusCode: the default HTTP status code to be sent with the response.
* message: the default error message to be sent with the response.
* template (optional): to be included if the exception should be rendered with an HTML template instead of as JSON.

Additionally, each custom exception must implement a getData() method, which is used to return extra data to the
if needed.

We can use our InvalidFieldException as an example, which returns an HTTP 400 and keeps track of an API request field 
that failed some kind of validation.

```php
...

/** @var int */
    protected $statusCode = 400;

    /** @var string */
    protected $message = 'Bad Request';

    /** @var string */
    protected $field;

    /**
     * InvalidFieldException constructor.
     * @param null|string $message
     * @param null|string $field
     * @param \Exception|null $previous
     * @param array $headers
     * @param int|null $code
     */
    public function __construct(?string $message = null, ?string $field = null, ?\Exception $previous = null, array $headers = array(), ?int $code = 0)
    {
        $this->field = $field;

        parent::__construct($message, $previous, $headers, $code);
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return [
            'field' => $this->field
        ];
    }

...
```
This example sets the default HTTP status code to 400 (Bad Request), and the error message to the same string.

Additionally, this exception keeps track of a particular field, and returns that field as part of the getData() method.

The status code and message can be overridden at any time whenever the exception is invoked as part of the constructor.