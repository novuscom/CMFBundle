<?php

namespace Novuscom\CMFBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ElementPropertySMultipleType extends AbstractType
{


	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{

		//echo '<pre>' . print_r($builder->getData(), true) . '</pre>';

		//echo '<pre>' . print_r($builder->getData(), true) . '</pre>';

		$builder->add('value', TextType::class, array(
			'required' => true,
			'mapped' => true,
			'label' => 'Значение',
			//'data' => 'asdasd'
		));



		$builder->add('description', TextType::class, array(
			'required' => false,
			'mapped' => true,
			'label' => 'Описание',
		));



	}

	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'Novuscom\CMFBundle\Entity\ElementProperty',
			//'data_class' => null,
			//'cascade_validation' => true,
		));
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'multiple_string';
	}

}

