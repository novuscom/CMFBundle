<?php

namespace Novuscom\Bundle\CMFBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Doctrine\Common\Collections\ArrayCollection;

class ElementType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('active', 'checkbox', array(
            'label' => 'Активность',
            'required' => false,
            //'data' => true,
        ));

        $builder->add('name', 'text', array(
            'attr' => array(
                'class' => 'form-control',
                'data-translit' => false,
            ),
            'label' => 'Название',
        ));
        $builder->add('code', 'text', array(
            'label' => 'Код',
            'required' => false,
            'attr' => array(
                'class' => 'form-control',
                'data-translit-alias' => false,
            ),
        ));
        $builder->add('sort', 'integer', array(
            'label' => 'Сортировка',
            'required' => false,
            'attr' => array(
                'class' => 'form-control'
            ),
        ));
        $builder->add('preview_picture', 'file', array(
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
        $previewPictureAlt = $builder->add('preview_picture_alt', 'text', $previewPictureAltInfo);

        
        $builder->add('preview_picture_src', 'text', array(
            'label' => 'Картинка для анонса (источник)',
            'attr' => array(
                'class' => 'form-control',
            ),
            'mapped' => false,
            'required' => false,
        ));
        $builder->add('detail_picture', 'file',
            array(
                'label' => 'Детальная картинка',
                'mapped' => false, 'required' => false)
        );
        $builder->add('detail_picture_src', 'text', array(
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
        $detailPictureAlt = $builder->add('detail_picture_alt', 'text', $detailPictureAltInfo);
        $builder->add('title', 'text', array(
            'label' => 'Заголовок окна',
            'required'=>false,
        ));
		$builder->add('header', 'text', array(
			'label' => 'Заголовок страницы',
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
        $builder->add('section', 'entity', array(
            'label' => 'Раздел',
            'class' => 'NovuscomCMFBundle:Section',
            'property' => 'name',
            'expanded' => false,
            'multiple' => true,
            'required' => false,
            'empty_value' => '',
            'empty_data' => null,
            'mapped' => false,
            'attr' => array('size' => '20'),
            'data' => $data,
            'query_builder' => function ($er) use ($options) {
                return $er->createQueryBuilder('s')
                    ->where("s.block = :block")
                    ->orderBy('s.name', 'ASC')
                    ->setParameters(array('block' => $options['blockObject']));
            }
        ));
        /* $builder->add('block', 'entity', array(
             'class' => 'NovuscomCMFBundle:Block',
             'property' => 'name',
             'expanded' => false,
             'multiple' => false,
         ));*/

        //$builder->add('category', new ElementPropertyType(), array('mapped'=>false));
        /*$builder->add('properties', 'collection', array(
            'label' => 'Свойства',
            'type' => new ElementPropertyType(),
            //'mapped'=>false,
            //'prototype' => true,
            //'allow_add' => true,
            //'allow_delete' => true,
            //'by_reference' => false,
        ));*/

        $builder->add('previewText', 'textarea', array(
                'label' => 'Описание для анонса',
                'required' => false, 'attr' => array())
        );
        $builder->add('detailText', 'textarea', array(
            'label' => 'Детальное описание',
            'required' => false, 'attr' => array()
        ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Novuscom\Bundle\CMFBundle\Entity\Element',
            'em' => false,
            'blockObject' => false,
            'params' => false,
            //'data_class' => 'Novuscom\Bundle\CMFBundle\Entity\FormElement'
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
