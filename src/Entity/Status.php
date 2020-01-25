<?php
/**
 * Created by PhpStorm.
 * User: misfitpixel
 * Date: 1/19/20
 * Time: 8:52 PM
 */

namespace MisfitPixel\Entity;


use MisfitPixel\Entity\Abstraction\Respondent;

/**
 * Class Status
 * @package MisfitPixel\Entity
 */
class Status
{
    use Respondent {
        getResponse as getDefaultResponse;
    };

    const ACTIVE = 1;
    const INACTIVE = 2;
    const EXPIRED = 3;
    const DELETED = 4;
    const COMPLETE = 5;

    /** @var int */
    private $id;

    /** @var string */
    private $name;

    /**
     * Status constructor.
     * @param int $id
     */
    public function __construct(int $id)
    {
        $this->id = $id;

        switch($this->getId()) {
            case self::ACTIVE:
                $name = 'active';

                break;

            case self::INACTIVE:
                $name = 'inactive';

                break;

            case self::EXPIRED:
                $name = 'expired';

                break;

            case self::DELETED:
                $name = 'deleted';

                break;

            case self::COMPLETE:
                $name = 'complete';

                break;

            default:
                $name = 'na';
        }

        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function getResponse(): array
    {
        $response = $this->getDefaultResponse();

        if(isset($response['status_id'])) {
            unset($response['status_id']);
        }

        return $response;
    }
}