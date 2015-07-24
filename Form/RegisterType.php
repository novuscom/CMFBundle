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

class RegisterType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        $builder->add('username', 'text', array(
            'label' => 'Имя',
            //'label_attr' => array('class' => 'MYCLASSFOR_LABEL'),
            //'attr' => array('class' => 'MYCLASSFOR_INPUTS'),
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
        $builder->add('plainPassword', 'repeated', array(
            'type' => 'password',
            'invalid_message' => 'Пароли не совпадают',
            //'options' => array('attr' => array('class' => 'password-field')),
            'required' => true,
            'first_options'  => array('label' => 'Пароль'),
            'second_options' => array('label' => 'Повторите пароль'),
        ));
        $builder->add('email', 'text', array(
            'label' => 'Электронная почта',
            'constraints' => array(
                new NotBlank(array('message' => 'поле должно быть заполнено')),
                new Email(array('message' => 'адрес электронной почты указан неверно')),
            ),
        ));
        $builder->add('phone', 'text', array(
            'label' => 'Телефон',
            'data' => '+7',
            'constraints' => array(
                new NotBlank(array('message' => 'поле должно быть заполнено')),
            ),
        ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => true,
            'data_class'=>'Novuscom\CMFUserBundle\Entity\User'
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
