<?php

namespace Novuscom\CMFBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SectionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array(
            'label' => 'Название раздела'
        ));
        $builder->add('code', 'text', array(
            'label' => 'Код'
        ));
        $builder->add('sort', 'text', array(
            'label' => 'Сортировка',
            'required'=>false,
        ));
        $builder->add('title', 'text', array(
            'label' => 'Title',
            'required'=>false,
        ));
        $builder->add('keywords', 'text', array(
            'label' => 'Ключевые слова',
            'required'=>false,
        ));
        $builder->add('description', 'text', array(
            'label' => 'Описание',
            'required'=>false,
        ));
        $builder->add('preview_text', 'textarea', array(
            'label' => 'Краткое описание',
            'required'=>false,
            'attr'=>array(
                'class'=>'editor',
            ),
        ));
        $builder->add('detail_text', 'textarea', array(
            'label' => 'Детальное описание',
            'required'=>false,
            'attr'=>array(
                'class'=>'editor',
            ),
        ));

		$builder->add('preview_picture', 'file', array(
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
        $builder->add('submit', 'submit', array(
            'label' => 'Сохранить',
            'attr' => array(
                'class'=>'btn btn-success',
            ),
        ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Novuscom\CMFBundle\Entity\Section'
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
