<?php

namespace Novuscom\Bundle\CMFBundle\Form;


use Novuscom\Bundle\CMFBundle\Form\PropertyType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class BlockType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        $builder
            ->add(
                'name',
                TextType::class,
                array(
                    'label' => 'Название',
                    'attr' => array(
                        'class' => 'form-control'
                    ),
                )
            )
            ->add('code', TextType::class,
                array(
                    'label' => 'Код',
                    'attr' => array(
                        'class' => 'form-control'
                    ),
                    'required'=>false,
                )
            );
        /**
         * Сайты
         */
        $builder->add('sites', EntityType::class, array(
            'class' => 'NovuscomCMFBundle:Site',
            'choice_label' => 'name',
            'expanded' => false,
            'multiple' => true,
            //'required' => true, // почему-то не работает атрибут required
            //'mapped' => false,
            'attr' => array(
                'class' => 'form-control'
            ),
            //'data' => $options['sites']
        ));
        $builder->add('group', EntityType::class, array(
            'class' => 'NovuscomCMFBundle:BlockGroup',
            'choice_label' => 'name',
            'expanded' => false,
            'multiple' => false,
            'label' => 'Группа',
            'required' => false,
            'attr' => array(
                'class' => 'form-control'
            ),
        ));
        $builder->add('property', CollectionType::class,
            array(
                'entry_type' => PropertyType::class,
                'prototype' => true,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'Свойства'
            )
        );
        /*$builder->add('tags', 'collection',
            array(
                'type' => new TagType(),
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            )
        );*/
    }
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'Novuscom\Bundle\CMFBundle\Entity\Block',
			'sites' => new ArrayCollection()
		));
	}

    /**
     * @return string
     */
    public function getName()
    {
        return 'cmf_blockbundle_block';
    }
}


