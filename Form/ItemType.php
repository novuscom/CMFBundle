<?php

namespace Novuscom\CMFBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ItemType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array(
            'label'=>'Название',
        ));
        $builder->add('url', 'text', array(
            'label'=>'Адрес',
        ));
        $builder->add('sort', 'number', array(
            'label'=>'Сортировка',
            'required'=>false,
        ));
        $builder->add('submit', 'submit', array(
            'label'=>'Сохранить',
            'attr'=>array(
                'class'=>'btn btn-success',
            )
        ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Novuscom\CMFBundle\Entity\Item'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'cmf_menubundle_item';
    }
}
