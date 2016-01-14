<?php

namespace Novuscom\CMFBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PropertyType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array(
            'label' => 'Название',
            'attr' => array(
                'class' => 'form-control'
            )
        ));
        $builder->add('code', 'text', array(
            'label' => 'Код',
            'attr' => array(
                'class' => 'form-control'
            )
        ));
        $builder->add('info', 'textarea', array(
            'label' => 'JSON',
            'attr' => array(
                'class' => 'form-control'
            ),
            'required'=>false,
        ));
        $builder->add('type', 'choice', array(
            'choices' => array(
                'S' => 'Строка',
                'E' => 'Элемент',
                'F' => 'Файл',
                'N' => 'Число',
                'DATE_TIME' => 'Дата и время',
                'U' => 'Пользователь',
                'LIST' => 'Список',
                'TEXT' => 'Простой текст',
                'HTML' => 'HTML',
            ),
            'required' => true,
            'attr' => array(
                'class' => 'form-control'
            )
        ));
        /*$builder->add('block', 'entity', array(
            'class' => 'NovuscomCMFBundle:Block',
            'property' => 'name',
            'expanded' => false,
            'multiple' => false,
        ));*/
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Novuscom\CMFBundle\Entity\Property'
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
