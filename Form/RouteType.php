<?php

namespace Novuscom\CMFBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RouteType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('active', 'checkbox', array(
            'label'     => 'Активность',
            'required'  => false,
        ));
        $builder->add('name', 'text', array(
            'required' => false,
            'attr' => array('class' => 'form-control'),
            'label' => 'Название маршрута'
        ));
        $builder->add('code', 'text', array(
            'required' => false,
            'attr' => array('class' => 'form-control'),
            'label' => 'Текстовый код'
        ));
        $builder->add('template', 'text', array(
            'label' => 'Шаблон',
            'required' => true,
            'attr' => array('class' => 'form-control'),
        ));
        $builder->add('controller', 'text', array(
            'required' => true,
            'attr' => array('class' => 'form-control'),
            'label' => 'Контроллер'
        ));
        $builder->add('site', 'entity', array(
            'class' => 'NovuscomCMFBundle:Site',
            'property' => 'name',
            'attr' => array('class' => 'form-control col-lg-4'),
            'label' => 'Сайт',
            'required' => true,
        ));
        $builder->add('page', 'entity', array(
            'class' => 'NovuscomCMFBundle:Page',
            'property' => 'name',
            'attr' => array('class' => 'form-control col-lg-4'),
            'label' => 'Страница',
            'empty_value' => '',
            'required' => false,
        ));
        $builder->add('block', 'entity', array(
            'class' => 'NovuscomCMFBundle:Block',
            'property' => 'name',
            'attr' => array('class' => 'form-control col-lg-4'),
            'label' => 'Блок',
            'empty_value' => '',
            'required' => false,
        ));
        $builder->add('params', 'textarea', array(
            'required' => false,
            'attr' => array('class' => 'form-control'),
        ));
        $builder->add('sort', 'number', array(
            'required' => false,
            'attr' => array('class' => 'form-control'),
            'label' => 'Индекс сортировки'
        ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Novuscom\CMFBundle\Entity\Route'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'cmf_routingbundle_route';
    }
}
