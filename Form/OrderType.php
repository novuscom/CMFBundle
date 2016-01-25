<?php

namespace Novuscom\Bundle\CMFBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OrderType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('address', 'text', array(
            'label' => 'Адрес доставки',
        ));
        $builder->add('phone', 'text', array(
            'label' => 'Телефон',
        ));
        $builder->add('name', 'text', array(
            'label' => 'ФИО',
        ));
        $builder->add('submit', 'submit', array(
            'label' => 'Заказать',
        ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(//'data_class' => 'Novuscom\Bundle\CMFBundle\Entity\Cart'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'order_form';
    }
}
