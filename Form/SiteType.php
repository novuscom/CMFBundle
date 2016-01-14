<?php

namespace Novuscom\CMFBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
		$builder->add('name', TextType::class, array(
			'label' => 'Название сайта',
			'attr' => array(
				'class' => 'form-control'
			)
		));

		$builder->add('code', TextType::class, array(
			'label' => 'Код',
		));

		$builder->add('emails', CollectionType::class, array(
			'label' => 'Email',
			'prototype' => true,
			'allow_add' => true,
			'allow_delete' => true,
			'mapped' => true,
			'by_reference' => false,
		));;

		$builder->add('aliases', CollectionType::class, array(
			'label' => 'Алиасы',
			'entry_type' => AliasType::class,
			'prototype' => true,
			'allow_delete' => true,
			'mapped' => true,
			'by_reference' => false,
			'allow_add' => true,
		));
		$builder->add('robotsTxt', TextareaType::class, array('required' => false, 'attr' => array(
			'class' => 'form-control'
		),));

		$builder->add('closed', CheckboxType::class, array(
			'label' => 'Доступ к публичной части закрыт',
			'required' => false,
		));
		$builder->add('submit', SubmitType::class, array('label' => 'Сохранить', 'attr' => array(
			'class' => 'btn btn-success'
		)));
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
