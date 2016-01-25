<?php

namespace Novuscom\Bundle\CMFBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProductType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('price')
            ->add('quantity')
            ->add('url')
            ->add('created')
            ->add('route')
            ->add('weight')
            ->add('element')
            ->add('cart')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Novuscom\Bundle\CMFBundle\Entity\Product'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'novuscom_cmfbundle_product';
    }
}
