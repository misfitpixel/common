<?php
/**
 * Created by PhpStorm.
 * User: misfitpixel
 * Date: 1/19/20
 * Time: 9:17 PM
 */

namespace MisfitPixel\Entity\Abstraction;


use MisfitPixel\Entity;
use MisfitPixel\Exception;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Trait Persistent
 * @package MisfitPixel\Entity\Abstraction
 */
trait Persistent
{
    /**
     * @return bool
     */
    public function save()
    {
        $success = true;
        $action = ($this->getId() === null) ? 'insert' : 'update';
        $previousValues = [];

        try {
            $this->getManager()->persist($this);
            $this->getManager()->getUnitOfWork()->computeChangeSets();

            /**
             * calculate changes.
             */
            foreach($this->getManager()->getUnitOfWork()->getScheduledEntityUpdates() as $entity) {
                foreach($this->getManager()->getUnitOfWork()->getEntityChangeSet($entity) as $field=>$value) {
                    $previousValues[$field] = $value[0];
                }
            }

            $this->getManager()->flush();

            /**
             * fire after_insert or after_update events.
             */
            $event = new GenericEvent($this);
            $event->setArgument('previousValues', $previousValues);

            /** @var \Symfony\Component\EventDispatcher\EventDispatcher $dispatcher */
            $dispatcher = $this->getContainer()->get('event_dispatcher');
            $dispatcher->dispatch($event, sprintf('api.%s.after_%s', strtolower($this->getEntityName()), $action));

        } catch(\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
            throw new Exception\NonUniqueEntityException(null, self::class);

        } catch(\Doctrine\DBAL\Exception\NotNullConstraintViolationException $e) {
            throw new Exception\DbException($e->getMessage());

        } catch(\Exception $e) {
            /**
             * unexpected doctrine error override output in dev environment.
             */
            if(!in_array(getenv('APP_ENV'), ['prod', 'production'])) {
                print_r($e->getMessage());
                exit;
            }

            $success = false;
        }

        return $success;
    }

    /**
     * @param bool $soft
     * @return bool
     */
    public function delete(bool $soft = false)
    {
        $success = true;

        /**
         * fire before_delete events.
         */
        $event = new GenericEvent($this);

        /** @var \Symfony\Component\EventDispatcher\EventDispatcher $dispatcher */
        $dispatcher = $this->getContainer()->get('event_dispatcher');
        $dispatcher->dispatch($event, sprintf('api.%s.before_delete', strtolower($this->getEntityName())));

        if($soft && method_exists($this, 'setStatus')) {
            /** @var Entity\Status $status */
            $status = $this->getManager()->getRepository(Entity\Status::class)
                ->find(Entity\Status::DELETED)
            ;

            $this->setStatus($status)->save();

        } else {
            try {
                $this->getManager()->remove($this);
                $this->getManager()->flush();

                /**
                 * TODO: catch foreign key failures (this should change to the doctrine exception)
                 * TODO: if statused, attempt to set status to 4
                 * TODO: otherwise, return false;
                 */
            } catch(Exception\DbException $e) {
                /**
                 * force restart the entity manager.
                 */
                $this->getContainer()->get('doctrine')->resetManager();

                /**
                 * attempt to set the status instead.
                 */
                if(method_exists($this, 'setStatus')) {
                    /** @var Entity\Status $status */
                    $status = $this->getManager()->getRepository(Entity\Status::class)
                        ->find(Entity\Status::DELETED)
                    ;

                    $this->setStatus($status)->save();

                    $success = true;
                }

            } catch(\Exception $e) {
                $success = false;
            }
        }

        /**
         * fire after_delete events.
         */
        if($success){
            $event = new GenericEvent($this);

            /** @var \Symfony\Component\EventDispatcher\EventDispatcher $dispatcher */
            $dispatcher = $this->getContainer()->get('event_dispatcher');
            $dispatcher->dispatch($event, sprintf('api.%s.after_delete', strtolower($this->getEntityName())));
        }

        return $success;
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getManager()
    {
        /** @var Kernel $kernel */
        global $kernel;

        return $kernel->getContainer()->get('doctrine')->getManager();
    }

    /**
     * @return null|\Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function getContainer()
    {
        /** @var Kernel $kernel */
        global $kernel;

        return $kernel->getContainer();
    }

    /**
     * @return string
     */
    protected function getEntityName()
    {
        return str_replace('App\Entity\\', '', self::class);
    }
}