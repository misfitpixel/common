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
     * @return int|null
     */
    public abstract function getId(): ?int;

    /**
     * @return bool
     */
    public function save(): bool
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

            /**
             * prepare event dispatcher.
             */
            $event = new GenericEvent($this);
            $event->setArgument('previousValues', $previousValues);

            /** @var \Symfony\Component\EventDispatcher\EventDispatcher $dispatcher */
            $dispatcher = $this->getContainer()->get('event_dispatcher');

            /**
             * fire before_insert or before_update events.
             **/
            $dispatcher->dispatch($event, sprintf('api.%s.before_%s', strtolower($this->getEntityName()), $action));

            $this->getManager()->flush();

            /**
             * fire after_insert or after_update events.
             */
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
    public function delete(bool $soft = false): bool
    {
        $success = true;

        /**
         * fire before_delete events.
         */
        $event = new GenericEvent($this);

        /** @var \Symfony\Component\EventDispatcher\EventDispatcher $dispatcher */
        $dispatcher = $this->getContainer()->get('event_dispatcher');
        $dispatcher->dispatch($event, sprintf('api.%s.before_delete', strtolower($this->getEntityName())));

        if($soft && method_exists($this, 'setStatusId')) {
            $this->setStatusId(Entity\Status::DELETED)->save();

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
                if(method_exists($this, 'setStatusId')) {
                    $this->setStatusId(Entity\Status::DELETED)->save();

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
     * @return \Doctrine\ORM\EntityManagerInterface
     */
    protected function getManager(): \Doctrine\ORM\EntityManagerInterface
    {
        /** @var Kernel $app */
        global $app;

        /**
         * always ensure we have a fresh manager if
         * closed by an earlier exception.
         */
        if(!$app->getContainer()->get('doctrine')->getManager()->isOpen()) {
            $app->getContainer()->get('doctrine')->resetManager();
        }

        return $app->getContainer()->get('doctrine')->getManager();
    }

    /**
     * @return null|\Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function getContainer(): ?\Symfony\Component\DependencyInjection\ContainerInterface
    {
        /** @var Kernel $app */
        global $app;

        return $app->getContainer();
    }

    /**
     * @return string
     */
    protected function getEntityName(): string
    {
        return str_replace('MisfitPixel\Entity\\', '',
            str_replace('App\Entity\\', '', self::class)
        );
    }
}
