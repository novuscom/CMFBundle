<?php

namespace Novuscom\Bundle\CMFBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MenuType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, array(
            'label'=>'Название',
        ));
        $builder->add('code', TextType::class, array(
            'label'=>'Код',
        ));
        $builder->add('submit', SubmitType::class, array(
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
            'data_class' => 'Novuscom\Bundle\CMFBundle\Entity\Menu'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'cmf_menubundle_menu';
    }
}
