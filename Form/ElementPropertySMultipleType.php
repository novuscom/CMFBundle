<?php

namespace Novuscom\CMFBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
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


		$builder->add('text', TextType::class, array(
			'required' => true,
			'label' => 'Значение',
		));



	}

	/**
	 * @param OptionsResolverInterface $resolver
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => null,

		));

	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'multiple_string';
	}

	private $choices;
	private $options;
	private $data;

	public function __construct($choices = false, $options = false, $data = array())
	{
		$this->data = $data;
		$this->choices = $choices;
		$this->options = $options;
	}

}

