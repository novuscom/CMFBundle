<?php

namespace Novuscom\CMFBundle\Form;

use Novuscom\CMFBundle\Form\ElementPropertyType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;



class ElementType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('active', CheckboxType::class, array(
            'label' => 'Активность',
            'required' => false,
            //'data' => true,
        ));

        $builder->add('name', TextType::class, array(
            'attr' => array(
                'class' => 'form-control',
                'data-translit' => false,
            ),
            'label' => 'Название',
        ));
        $builder->add('code', TextType::class, array(
            'label' => 'Код',
            'required' => false,
            'attr' => array(
                'class' => 'form-control',
                'data-translit-alias' => false,
            ),
        ));
        $builder->add('sort', IntegerType::class, array(
            'label' => 'Сортировка',
            'required' => false,
            'attr' => array(
                'class' => 'form-control'
            ),
        ));
        $builder->add('preview_picture', FileType::class, array(
            'label' => 'Картинка для анонса',
            'mapped' => false,
            'required' => false
        ));


        $previewPictureAltInfo = array(
            'label' => 'Описание картинки для анонса',
            'mapped' => false,
            'required' => false,
        );
        if ($builder->getData()->getPreviewPicture()) {
            $previewPictureAltInfo['data']=$builder->getData()->getPreviewPicture()->getDescription();
        }
        $previewPictureAlt = $builder->add('preview_picture_alt', TextType::class, $previewPictureAltInfo);

        
        $builder->add('preview_picture_src', TextType::class, array(
            'label' => 'Картинка для анонса (источник)',
            'attr' => array(
                'class' => 'form-control',
            ),
            'mapped' => false,
            'required' => false,
        ));
        $builder->add('detail_picture', FileType::class,
            array(
                'label' => 'Детальная картинка',
                'mapped' => false, 'required' => false)
        );
        $builder->add('detail_picture_src', TextType::class, array(
            'label' => 'Детальная картинка (источник)',
            'attr' => array(
                'class' => 'form-control',
            ),
            'mapped' => false,
            'required' => false,
        ));
        $detailPictureAltInfo = array(
            'label' => 'Описание детальной картинки',
            'mapped' => false,
            'required' => false
        );
        if ($builder->getData()->getDetailPicture()) {
            $detailPictureAltInfo['data']=$builder->getData()->getDetailPicture()->getDescription();
        }
        $detailPictureAlt = $builder->add('detail_picture_alt', TextType::class, $detailPictureAltInfo);
        $builder->add('title', TextType::class, array(
            'label' => 'Заголовок окна',
            'required'=>false,
        ));
		$builder->add('header', TextType::class, array(
			'label' => 'Заголовок страницы',
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


        //echo '<pre>' . print_r($entity->getPreviewPicture()->getName(), true) . '</pre>';


        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $data = $event->getData();
                //echo '<pre>' . print_r($data->getCode(), true) . '</pre>';

            }
        );

        $data = new ArrayCollection();
        $element = $builder->getData();
        if ($options['em'] && $element->getId()) {
            $em = $options['em'];


            $ElementSection = $em->getRepository('NovuscomCMFBundle:ElementSection')->findBy(array('element' => $element));
            $sectionsId = array();
            foreach ($ElementSection as $es) {
                if ($es->getSection()) {
                    $sectionsId[] = $es->getSection()->getId();
                }
            }

            //echo '<pre>' . print_r($sectionsId, true) . '</pre>';

            if ($sectionsId) {
                $sections = $em->getRepository('NovuscomCMFBundle:Section')->findBy(array('id' => $sectionsId));
                foreach ($sections as $s) {
                    $data->add($s);
                }
            }

        }
        if (isset($options['params']['_route_params']['section_id'])) {
            $section = $options['em']->getRepository('NovuscomCMFBundle:Section')->find($options['params']['_route_params']['section_id']);
            $data->add($section);
        }

        //echo '<pre>' . print_r($options['blockObject']->getName(), true) . '</pre>';
        $builder->add('section', EntityType::class, array(
            'label' => 'Раздел',
            'class' => 'NovuscomCMFBundle:Section',
            'choice_label' => 'indentedTitle',
            'expanded' => false,
            'multiple' => true,
            'required' => false,
            //'empty_value' => '',
            'empty_data' => null,
            'mapped' => false,
            'attr' => array('size' => '20'),
            'data' => $data,
            'query_builder' => function ($er) use ($options) {
                return $er->createQueryBuilder('s')
                    ->where("s.block = :block")
                    ->orderBy('s.root, s.lft', 'ASC')
                    ->setParameters(array('block' => $options['blockObject']));
            }
        ));
        /* $builder->add('block', 'entity', array(
             'class' => 'NovuscomCMFBundle:Block',
             'choice_label' => 'name',
             'expanded' => false,
             'multiple' => false,
         ));*/

        //$builder->add('category', new ElementPropertyType(), array('mapped'=>false));
        /*$builder->add('properties', CollectionType::class, array(
            'label' => 'Свойства',
            'entry_type' => new ElementPropertyType(),
            //'mapped'=>false,
            //'prototype' => true,
            //'allow_add' => true,
            //'allow_delete' => true,
            //'by_reference' => false,
        ));*/
	    /*$builder->add('properties', CollectionType::class, array(
		    'label' => 'Совйства',
		    'entry_type' => ElementPropertyType::class,
		    'mapped' => false,
		    'by_reference' => false,
		    'allow_add' => true,
		    'allow_delete' => true,
		    'prototype' => true,
		    'entry_options' => array(
			    'data' => 'dasdsa'
		    )
	    ));*/
        $builder->add('previewText', TextareaType::class, array(
                'label' => 'Описание для анонса',
                'required' => false, 'attr' => array())
        );
        $builder->add('detailText', TextareaType::class, array(
            'label' => 'Детальное описание',
            'required' => false, 'attr' => array()
        ));
	    $builder->add('submit', SubmitType::class, array('label' => 'Сохранить', 'attr' => array('class' => 'btn btn-info')));
    }

	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'Novuscom\CMFBundle\Entity\Element',
			'em' => false,
			'blockObject' => false,
			'params' => false,
		));
	}

    /**
     * @return string
     */
    public function getName()
    {
        return 'cmf_blockbundle_element';
    }
}
