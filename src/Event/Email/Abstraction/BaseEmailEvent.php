<?php


namespace MisfitPixel\Event\Email\Abstraction;


use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Twig\Environment;

abstract class BaseEmailEvent
{
    /** @var ContainerInterface */
    protected $container;

    /** @var Environment */
    private $twig;

    /** @var \Swift_Mailer */
    private $mailer;

    /**
     * BaseEmailEvent constructor.
     * @param ContainerInterface $container
     * @param Environment $twig
     * @param \Swift_Mailer $mailer
     */
    public function __construct(ContainerInterface $container, Environment $twig, \Swift_Mailer $mailer)
    {
        $this->container = $container;
        $this->twig = $twig;
        $this->mailer = $mailer;
    }

    /**
     * @param GenericEvent $event
     * @return void
     */
    abstract public function execute(GenericEvent $event);

    /**
     * @param string $subject
     * @param string $template
     * @param array $data
     * @param string $to
     * @param string $from
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    protected function send(string $subject, string $template, array $data = [], string $to, string $from = 'no-reply@mtgbracket.com')
    {
        $message = (new \Swift_Message($subject, $this->twig->render($template, $data), 'text/html'))
            ->setTo($to)
            ->setFrom($from);

        $this->mailer->send($message);
    }

    /**
     * @return Environment
     */
    public function getTwig()
    {
        return $this->twig;
    }
}
