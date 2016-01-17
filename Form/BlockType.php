<?php

namespace Novuscom\CMFBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\Common\Collections\ArrayCollection;

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
                'text',
                array(
                    'label' => 'Название',
                    'attr' => array(
                        'class' => 'form-control'
                    ),
                )
            )
            ->add('code', 'text',
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
        $builder->add('sites', 'entity', array(
            'class' => 'NovuscomCMFBundle:Site',
            'property' => 'name',
            'expanded' => false,
            'multiple' => true,
            //'required' => true, // почему-то не работает атрибут required
            //'mapped' => false,
            'attr' => array(
                'class' => 'form-control'
            ),
            //'data' => $options['sites']
        ));
        $builder->add('group', 'entity', array(
            'class' => 'NovuscomCMFBundle:BlockGroup',
            'property' => 'name',
            'expanded' => false,
            'multiple' => false,
            'label' => 'Группа',
            'required' => false,
            'attr' => array(
                'class' => 'form-control'
            ),
        ));
        $builder->add('property', 'collection',
            array(
                'type' => new PropertyType(),
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

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Novuscom\CMFBundle\Entity\Block',
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


