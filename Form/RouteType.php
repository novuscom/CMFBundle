<?php

namespace Novuscom\Bundle\CMFBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RouteType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('active', CheckboxType::class, array(
            'label'     => 'Активность',
            'required'  => false,
        ));
        $builder->add('name', TextType::class, array(
            'required' => false,
            'attr' => array('class' => 'form-control'),
            'label' => 'Название маршрута'
        ));
        $builder->add('code', TextType::class, array(
            'required' => false,
            'attr' => array('class' => 'form-control'),
            'label' => 'Текстовый код'
        ));
        $builder->add('template', TextType::class, array(
            'label' => 'Шаблон',
            'required' => true,
            'attr' => array('class' => 'form-control'),
        ));
        $builder->add('controller', TextType::class, array(
            'required' => true,
            'attr' => array('class' => 'form-control'),
            'label' => 'Контроллер'
        ));
        $builder->add('site', EntityType::class, array(
            'class' => 'NovuscomCMFBundle:Site',
            'choice_label' => 'name',
            'attr' => array('class' => 'form-control'),
            'label' => 'Сайт',
            'required' => true,
        ));
        $builder->add('page', EntityType::class, array(
            'class' => 'NovuscomCMFBundle:Page',
            'choice_label' => 'indentedTitle',
            'attr' => array('class' => 'form-control'),
            'label' => 'Страница',
            'required' => false,
	        'query_builder' => function ($er) use ($options) {
		        return $er->createQueryBuilder('s')
			        ->orderBy('s.lft', 'ASC');
	        }
        ));
        $builder->add('block', EntityType::class, array(
            'class' => 'NovuscomCMFBundle:Block',
            'choice_label' => 'name',
            'attr' => array('class' => 'form-control'),
            'label' => 'Блок',
            'required' => false,
        ));
        $builder->add('params', TextareaType::class, array(
            'required' => false,
            'attr' => array('class' => 'form-control'),
        ));
        $builder->add('sort', NumberType::class, array(
            'required' => false,
            'attr' => array('class' => 'form-control'),
            'label' => 'Индекс сортировки'
        ));
	    $builder->add('submit', SubmitType::class, array('label' => 'Сохранить', 'attr'=>array('class'=>'btn btn-success')));
    }

    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Novuscom\Bundle\CMFBundle\Entity\Route'
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
