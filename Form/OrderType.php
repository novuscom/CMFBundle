<?php

namespace Novuscom\CMFBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
        $builder->add('address', TextType::class, array(
            'label' => 'Адрес доставки',
        ));
        $builder->add('phone', TextType::class, array(
            'label' => 'Телефон',
        ));
        $builder->add('name', TextType::class, array(
            'label' => 'ФИО',
        ));
        $builder->add('submit', SubmitType::class, array(
            'label' => 'Заказать',
        ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(//'data_class' => 'Novuscom\CMFBundle\Entity\Cart'
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
