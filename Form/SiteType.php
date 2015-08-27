<?php

namespace Novuscom\CMFBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Novuscom\CMFBundle\Form\AliasType;


class SiteType extends AbstractType
{
	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{

		$entity = $builder->getData();

		//echo '<pre>'.print_r($entity->getEmails(), true).'</pre>';
		$builder->add('name', 'text', array(
			'label' => 'Название сайта',
			'attr' => array(
				'class' => 'form-control'
			)
		));

		$builder->add('code', 'text', array(
			'label' => 'Код',
		));

		$builder->add('emails', 'collection', array(
			'label' => 'Email',
			'prototype' => true,
			'allow_add' => true,
			'allow_delete' => true,
			'mapped' => true,
			'by_reference' => false,
		));;

		$builder->add('aliases', 'collection', array(
			'label' => 'Алиас',
			'type' => new AliasType(),
			'prototype' => true,
			'allow_add' => true,
			'allow_delete' => true,
			'mapped' => true,
			'by_reference' => false,
		));
		$builder->add('robotsTxt', 'textarea', array('required' => false, 'attr' => array(
			'class' => 'form-control'
		),));

		$builder->add('closed', 'checkbox', array(
			'label' => 'Доступ к публичной части закрыт',
			'required' => false,
		));

	}

	/**
	 * @param OptionsResolverInterface $resolver
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'Novuscom\CMFBundle\Entity\Site',
			//'cascade_validation' => true,

		));
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'cmf_sitebundle_site';
	}
}
