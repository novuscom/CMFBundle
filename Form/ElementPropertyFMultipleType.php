<?php

namespace Novuscom\CMFBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ElementPropertyFMultipleType extends AbstractType
{


	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{

		//echo '<pre>' . print_r($this->choices, true) . '</pre>';

		$data = $this->data;

		//echo '<pre>' . print_r($data, true) . '</pre>';

		if ($data && array_key_exists('DELETED', $data) && $data['DELETED'] == true) {
			$builder->add('deleted_file_id', TextType::class, array(
				'required' => true
			));
		} else {
			$builder->add('file', FileType::class, array(
				'required' => true
			));
			$builder->add('description', TextType::class, array(
				'required' => false
			));
		}
		if ($data && array_key_exists('REPLACED', $data) && $data['REPLACED'] == true) {
			$builder->add('replace_file_id', 'text', array('required' => true));
		}


	}

	/**
	 * @param OptionsResolverInterface $resolver
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'Novuscom\CMFBundle\Entity\FormPropertyFile',

		));

	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'multiple_file';
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

