<?php

namespace Novuscom\CMFBundle\Services;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Monolog\Logger;
use Novuscom\CMFBundle\Entity\Cart as CartEntity;

class Cart
{

    private $createdTime;

    public function getCreatedTime()
    {
        return $this->createdTime;
    }

    public function setCreatedTime($createdTime = false)
    {
        if ($createdTime == false)
            $createdTime = new \DateTime('now');
        $this->createdTime = $createdTime;
    }

    private $user;

    private function GetUser()
    {
        return $this->user;
    }

    private function Create()
    {
        $cart = new CartEntity();
        $this->setCreatedTime();
        $time = $this->getCreatedTime();
        $cart->setCreated($time);
        $cart->setUpdated($time);
        $user = $this->user;
        $stringClassName = $this->getUserClassName();
        if ((is_object($user) and $user instanceof $stringClassName))
            $cart->setUser($user);
        return $cart;
    }

    public function GetCurrent()
    {
        $this->logger->addInfo('Получение текущей корзины');
        $stringClassName = $this->getUserClassName();
        $cartCookie = $this->requestStack->getCurrentRequest()->cookies->get('cart');
        $user = $this->container->get('security.context')
            ->getToken()
            ->getUser();
        $this->user = $user;
        if (!($cartCookie and is_numeric($cartCookie))) {
            $this->logger->addInfo('ИД корзины не записано в куку');
            if (!(is_object($user) and $user instanceof $stringClassName)){
                $cart = $this->Create();
            }
            else{
                $cart = $this->GetByUserId($user->getId());
                if (count($cart) != 1) {
                    $cart = $this->Create();
                }
            }

        } else {
            $this->logger->addInfo('ИД корзины найден в куках: '.$cartCookie);
            $cart = $this->em->getRepository('NovuscomCMFBundle:Cart')->findOneBy(array(
                'id' => $cartCookie,
                'user' => $this->getUserReference($user->getId())
            ));
            if (count($cart) != 1) {
                $this->logger->addNotice('Корзина не найдена по ID пользователя и ID корзины');
                $cart = $this->Create();
            }
            else {
                $this->logger->addNotice('Корзина найдена - '.$cart->getId());
            }
        }
        return $cart;
    }

    private function getUserReference($user_id)
    {
        $element_reference = $this->em->getReference('Novuscom\CMFUserBundle\Entity\User', $user_id);
        return $element_reference;
    }

    public function GetByUserId($user_id)
    {
        $cart = $this->em->getRepository('NovuscomCMFBundle:Cart')->findOneBy(array(
            'user' => $this->getUserReference($user_id)
        ));
        return $cart;
    }

    private function getUserClassName()
    {
        return 'Novuscom\CMFUserBundle\Entity\User';
    }

    private $em;
    private $requestStack;
    private $logger;
    private $container;

    public function __construct(\Doctrine\ORM\EntityManager $em, Logger $logger, RequestStack $requestStack, ContainerInterface $container)
    {
        $this->em = $em;
        $this->requestStack = $requestStack;
        $this->logger = $logger;
        $this->container = $container;
    }
}