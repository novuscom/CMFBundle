<?php

namespace Novuscom\CMFBundle\Services;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Util\SecureRandom;
use Monolog\Logger;
use Novuscom\CMFBundle\Entity\Cart as CartEntity;
use Novuscom\CMFBundle\Services\User;

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
		$generator = new SecureRandom();
		$random = bin2hex($generator->nextBytes(10));
		$this->logger->addInfo('Новая корзина с кодом ' . $random);
		$cart = new CartEntity();
		$this->setCreatedTime();
		$time = $this->getCreatedTime();
		$cart->setCreated($time);
		$cart->setUpdated($time);
		$cart->setCode($random);
		$user = $this->user;
		$stringClassName = $this->getUserClassName();
		if ((is_object($user) and $user instanceof $stringClassName))
			$cart->setUser($user);
		return $cart;
	}

	public function removeCurrentCart()
	{
		$current = $this->GetCurrent();
		$this->em->remove($current);
	}

	private $currentCart;

	private function setCurrent()
	{
		$this->logger->addInfo('Получение текущей корзины');
		$stringClassName = $this->getUserClassName();
		$cartCookie = $this->requestStack->getCurrentRequest()->cookies->get('cart');
		$user = $this->UserService->getCurrentUser();
		$this->user = $user;
		$cart = false;
		if (!$cartCookie) {
			$this->logger->addInfo('ИД корзины НЕ найден в куках');
			if ($this->UserService->isAuthorized()) {
				$cart = $this->GetByUserId($user->getId());
			}
			if ($cart == false) {
				$this->logger->addInfo('ИД корзины не записано в куку');
				$cart = $this->Create();
			}
		} else {
			$this->logger->addInfo('ИД корзины найден в куках: ' . $cartCookie);
			$filter = array(
				'code' => $cartCookie,
				//'user' => $this->getUserReference($user->getId())
			);
			if ($this->UserService->isAuthorized()) {
				$filter = array('user' => $user->getId());
			}
			$cart = $this->em->getRepository('NovuscomCMFBundle:Cart')->findOneBy($filter);
			if (count($cart) != 1) {
				$this->logger->addNotice('Корзина не найдена');
				$cart = $this->Create();
			} else {
				$this->logger->addNotice('Корзина найдена - ' . $cart->getId());
			}
		}
		$this->currentCart = $cart;
		return $cart;
	}

	public function GetCurrent()
	{
		if (empty($this->currentCart) == true)
			$this->setCurrent();
		return $this->currentCart;
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
	private $UserService;

	public function __construct(
		\Doctrine\ORM\EntityManager $em,
		Logger $logger,
		RequestStack $requestStack,
		ContainerInterface $container,
		User $user
	)
	{
		$this->em = $em;
		$this->requestStack = $requestStack;
		$this->logger = $logger;
		$this->container = $container;
		$this->UserService = $user;
	}
}