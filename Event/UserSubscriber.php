<?php

namespace Novuscom\CMFBundle\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;


class UserSubscriber implements EventSubscriberInterface
{

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public static function getSubscribedEvents()
    {
        return array(
            'user.register' => array('onUserRegister', 0),
        );
    }


    public function onUserRegister(UserEvent $event)
    {
        $user = $event->getUser();
        $message = \Swift_Message::newInstance()
            ->setSubject('Подтверждение регистрации')
            ->setFrom('info@novuscom.ru')
            ->setTo($user->getEmailCanonical())
            ->setBody(
                $this->container->get('templating')->render(
                    'NovuscomCMFBundle:Email:ConfirmRegistration.html.twig',
                    array(
                        'name' => $user->getUsername(),
                        'token' => $user->getConfirmationToken(),
                    )
                ),
                'text/html'
            );
        $this->container->get('mailer')->send($message);
    }
}