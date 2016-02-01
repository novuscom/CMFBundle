<?php

namespace Novuscom\Bundle\CMFBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SectionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, array(
            'label' => 'Название раздела'
        ));
        $builder->add('code', TextType::class, array(
            'label' => 'Код'
        ));
        $builder->add('sort', TextType::class, array(
            'label' => 'Сортировка',
            'required'=>false,
        ));
        $builder->add('title', TextType::class, array(
            'label' => 'Title',
            'required'=>false,
        ));
        $builder->add('keywords', TextType::class, array(
            'label' => 'Ключевые слова',
            'required'=>false,
        ));
        $builder->add('description', TextType::class, array(
            'label' => 'Описание',
            'required'=>false,
        ));
        $builder->add('preview_text', TextareaType::class, array(
            'label' => 'Краткое описание',
            'required'=>false,
            'attr'=>array(
                'class'=>'editor',
            ),
        ));
        $builder->add('detail_text', TextareaType::class, array(
            'label' => 'Детальное описание',
            'required'=>false,
            'attr'=>array(
                'class'=>'editor',
            ),
        ));

		$builder->add('preview_picture', FileType::class, array(
			'label' => 'Картинка для анонса',
			'mapped' => false,
			'required' => false
		));

		/*$builder->add('preview_picture', 'entity', array(
			'label' => 'Картинка для анонса',
			'class' => 'CMFMediaBundle:File',
			'multiple' => false,
			'required' => false,
			'mapped' => true,
		));*/
        /*$builder->add('parent', 'entity', array(
            'class' => 'NovuscomCMFBundle:Section',
            'property' => 'name',
            'expanded' => false,
            'multiple' => false,
            'required' => false,
            'empty_value' => '',
            'empty_data' => null,
        ));*/
        /*$builder->add('block', 'entity', array(
            'class' => 'NovuscomCMFBundle:Block',
            'property' => 'name',
            'expanded' => false,
            'multiple' => false,
        ));*/
        $builder->add('submit', SubmitType::class, array(
            'label' => 'Сохранить',
            'attr' => array(
                'class'=>'btn btn-success',
            ),
        ));
    }

    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Novuscom\Bundle\CMFBundle\Entity\Section'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'cmf_blockbundle_section';
    }
}
