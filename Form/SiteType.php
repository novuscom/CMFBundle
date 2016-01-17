<?php

namespace Novuscom\CMFBundle\Form;

<<<<<<< HEAD
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Novuscom\CMFBundle\Form\AliasType;
=======
use Novuscom\CMFBundle\Entity\Alias;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
>>>>>>> 6b120d6339f9c8f270f714255a66ce26fbe4eb5c


class SiteType extends AbstractType
{
	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
<<<<<<< HEAD

		$entity = $builder->getData();

		//echo '<pre>'.print_r($entity->getEmails(), true).'</pre>';
		$builder->add('name', 'text', array(
=======
		$builder->add('name', TextType::class, array(
>>>>>>> 6b120d6339f9c8f270f714255a66ce26fbe4eb5c
			'label' => 'Название сайта',
			'attr' => array(
				'class' => 'form-control'
			)
		));
<<<<<<< HEAD

		$builder->add('code', 'text', array(
			'label' => 'Код',
		));

		$builder->add('emails', 'collection', array(
=======
		$builder->add('code', TextType::class, array(
			'label' => 'Код',
		));

		$builder->add('emails', CollectionType::class, array(
>>>>>>> 6b120d6339f9c8f270f714255a66ce26fbe4eb5c
			'label' => 'Email',
			'prototype' => true,
			'allow_add' => true,
			'allow_delete' => true,
			'mapped' => true,
			'by_reference' => false,
		));;

<<<<<<< HEAD
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
=======
		$builder->add('aliases', CollectionType::class, array(
			'label' => 'Алиасы',
			'entry_type' => AliasType::class,
			'prototype' => true,
			'allow_delete' => true,
			'mapped' => true,
			'by_reference' => false,
			'allow_add' => true,
			'entry_options' => array(
				//'data' => array()
			)
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

	public function configureOptions(OptionsResolver $resolver)
>>>>>>> 6b120d6339f9c8f270f714255a66ce26fbe4eb5c
	{
		$resolver->setDefaults(array(
			'data_class' => 'Novuscom\CMFBundle\Entity\Site',
			//'cascade_validation' => true,
<<<<<<< HEAD

=======
>>>>>>> 6b120d6339f9c8f270f714255a66ce26fbe4eb5c
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
