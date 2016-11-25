<?php

namespace Novuscom\CMFBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('username', TextType::class, array(
            'label' => 'Логин',
            'attr' => array(
                'class' => 'form-control'
            )
        ));
        $builder->add('email', EmailType::class, array(
            'label' => 'Email',
            'attr' => array(
                'class' => 'form-control'
            )
        ));
        /*$builder->add('locked', 'checkbox', array(
            'label'     => 'Блокировка',
            'required'  => false,

        ));*/


        $builder->add('password', RepeatedType::class, array(
            'type' => PasswordType::class,
            'invalid_message' => 'Пароли не совпадают',
            'options' => array('attr' => array('class' => 'form-control')),
            'required' => false,
            'first_options' => array('label' => 'Новый пароль'),
            'second_options' => array('label' => 'Повторить новый пароль'),
            'mapped' => false,
        ));


        $builder->add('enabled', CheckboxType::class, array(
            'label' => 'Активность',
            'required' => false,
        ));
        $builder->add('roles', ChoiceType::class, array(
            'choices' => array(
                'Администратор'=>'ROLE_ADMIN',
                'Редактор'=>'ROLE_EDITOR',
            ),
            'required' => false,
            'attr' => array(
                'class' => 'form-control'
            ),
            'mapped' => true,
            'multiple' => true,
        ));
        $builder->add('groups', EntityType::class, array(
            'class' => 'NovuscomCMFBundle:Group',
            'choice_label' => 'name',
            'expanded' => false,
            'multiple' => true,
            'required' => false,
            'attr' => array(
                'class' => 'form-control'
            )
        ));
        $builder->add('sites', EntityType::class, array(
            'class' => 'NovuscomCMFBundle:Site',
            'choice_label' => 'name',
            'expanded' => false,
            'multiple' => true,
            'required' => false,
            'attr' => array(
                'class' => 'form-control'
            )
        ));


    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Novuscom\CMFBundle\Entity\User'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'cmf_NovuscomCMFBundle_user';
    }
}
