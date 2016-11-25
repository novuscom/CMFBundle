<?php

namespace Novuscom\CMFBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class GroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array(
            'label' => 'Название',
        ));
       /* $builder->add('roles', 'choice', array(
            'label'=>'Роли',
            'choices' => array(
                'ROLE_ADMIN' => 'Администратор',
            ),
            'required' => false,
            'attr' => array(
                'class' => 'form-control'
            ),
            'multiple'=>true,
        ));*/
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Novuscom\CMFBundle\Entity\Group'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'cmf_NovuscomCMFBundle_group';
    }
}
