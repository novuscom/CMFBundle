<?php

namespace Novuscom\CMFBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Novuscom\CMFBundle\Entity\ElementSection;
use Novuscom\CMFBundle\Services\Cart;

class Product
{

	public function getProduct($productId, $cartId=false)
	{
		if ($cartId)
			$cart_reference = $this->em->getReference('Novuscom\CMFBundle\Entity\Cart', $cartId);
		else {
			$cart_reference = $this->Cart->GetCurrent();
		}
		if (count($cart_reference)!=1)
			return false;
		$this->setCart($cart_reference);
		$product = $this->em->getRepository('NovuscomCMFBundle:Product')->findOneBy(array(
			'id' => $productId,
			'cart' => $cart_reference
		));
		return $product;
	}

	private $cart;

	public function setCart($cart){
		$this->cart = $cart;
	}

	public function getCart(){
		return $this->cart;
	}

	public function removeEntity($productEntity)
	{
		$this->em->remove($productEntity);
		$this->em->flush();
	}

	public function IfStoredInCart($element_id, $cart_id = false)
	{
		$element_reference = $this->em->getReference('Novuscom\CMFBundle\Entity\Element', $element_id);
		$cart_reference = null;
		if ($cart_id)
			$cart_reference = $this->em->getReference('Novuscom\CMFBundle\Entity\Cart', $cart_id);
		$entity = $this->em->getRepository('NovuscomCMFBundle:Product')->findOneBy(array(
			'element' => $element_reference,
			'cart' => $cart_reference
		));
		return $entity;
	}

	public function GetByElementId($element_id)
	{
		if (is_numeric($element_id) == false)
			return false;
		$element_reference = $this->em->getReference('Novuscom\CMFBundle\Entity\Element', $element_id);
		$entity = $this->em->getRepository('NovuscomCMFBundle:Product')->findOneBy(array(
			'element' => $element_reference
		));
		return $entity;
	}

	public function GetById($id)
	{
		if (is_numeric($id) == false)
			return false;
		$entity = $this->em->getRepository('NovuscomCMFBundle:Product')->find($id);
		return $entity;
	}

	private $em;
	private $Cart;

	public function __construct(\Doctrine\ORM\EntityManager $em, Cart $Cart)
	{
		$this->em = $em;
		$this->Cart = $Cart;
	}
}