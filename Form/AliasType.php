<?php

namespace Novuscom\CMFBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AliasType extends AbstractType
{
	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('name');
	}

	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'Novuscom\CMFBundle\Entity\Alias',
			//'cascade_validation' => true,
		));
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'cmf_sitebundle_alias';
	}
}
