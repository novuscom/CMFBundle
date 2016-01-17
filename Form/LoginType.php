<?php

namespace Novuscom\CMFBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Choice;

class LoginType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('username', 'text', array(
            'label' => 'Имя',
            'required' => true,
            'constraints' => array(
                new NotBlank(array('message' => 'Поле должно быть заполнено')),
                new Length(array(
                    'min' => 3,
                    'max' => 50,
                    'minMessage' => 'имя должно содержать минимум 3 символа',
                    'maxMessage' => 'имя пользвоателя не может быть больше 50 символов',
                )),
            ),
        ));
        $builder->add('plainPassword', 'password', array(
            'label' => 'Пароль',
            'required' => true,
        ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => true,
            'data_class' => 'Novuscom\CMFUserBundle\Entity\User'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return '';
    }
}
