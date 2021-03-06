<?php

namespace Novuscom\CMFBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;


class PropertyType extends AbstractType
{
	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('name', TextType::class, array(
			'label' => 'Название',
			'attr' => array(
				'class' => 'form-control'
			)
		));
		$builder->add('code', TextType::class, array(
			'label' => 'Код',
			'attr' => array(
				'class' => 'form-control'
			)
		));
		$builder->add('info', TextType::class, array(
			'label' => 'JSON',
			'attr' => array(
				'class' => 'form-control'
			),
			'required' => false,
		));
		$builder->add('type', ChoiceType::class, array(
			'choices' => array(
				'Строка' => 'S',
				'Элемент' => 'E',
				'Раздел' => 'SECTION',
				'Файл' => 'F',
				'Число' => 'N',
				'Дата и время' => 'DATE_TIME',
				'Пользователь' => 'U',
				'Список' => 'LIST',
				'Простой текст' => 'TEXT',
				'HTML' => 'HTML',
				'Да/Нет' => 'BOOLEAN',
			),
			'required' => true,
			'attr' => array(
				'class' => 'form-control'
			)
		));
		if (isset($options['STANDALONE'])) {
			$builder->add('block', EntityType::class, array(
				'class' => 'NovuscomCMFBundle:Block',
				'choice_label' => 'name',
				//'expanded' => false,
				//'multiple' => false,
			));
			$builder->add('isForSection', ChoiceType::class, array(
				'choices' => array(
					'Для элемента' => 0,
					'Для раздела' => true,
				),
				'required' => true,
				'label' => 'Для',
				'mapped' => true,
			));
		}

	}

	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'Novuscom\CMFBundle\Entity\Property',
			'STANDALONE' => false,
		));
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'cmf_blockbundle_property';
	}
}
