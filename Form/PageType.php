<?php

namespace Novuscom\CMFBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


class PageType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('name', 'text', array(
                'label' => 'Название (используется в хлебных крошках)',
                'attr' => array(
                    'class' => 'form-control'
                ),
            ))
            ->add('title', 'text', array(
                'label' => 'Заголовок окна браузера',
                'attr' => array(
                    'class' => 'form-control'
                ),
            ))
            ->add('description', 'text', array(
                'label' => 'Описание страницы',
                'attr' => array(
                    'class' => 'form-control'
                ),
            ))
            ->add('keywords', 'text', array(
                'label' => 'Ключевые слова страницы',
                'attr' => array(
                    'class' => 'form-control'
                ),
            ))
            ->add('header', 'text', array(
                'label' => 'Заголовок страницы (h1)',
                'attr' => array(
                    'class' => 'form-control'
                ),
            ));
        $builder->add('content', 'textarea', array(
            'required' => false,
			'attr' => array(
				'class' => 'tinymce',
				'data-theme' => 'bbcode' // Skip it if you want to use default theme
			)
        ));
        $entity = $builder->getData();

        if ($options['SHOW_URL'])
            $builder->add('url', null, array('attr' => array('class' => 'form-control col-lg-4',), 'label' => 'Адрес (без слеша)',));

        if ($options['SHOW_PARENT']) {
            $builder->add('parent', 'choice', array(
                'choices' => $options['CHOICES']
            ));
        };

        if ($options['SHOW_PARENT']) {
            $builder->add('parent', 'entity', array(
                'class' => 'NovuscomCMFBundle:Page',
                'property' => 'name',
                'query_builder' => function ($er) use ($options, $entity) {
                    if ($entity->getId()) {
                        $nots = $er->createQueryBuilder('s')
                            ->select('s.id')
                            ->where("s.lft > " . $entity->getLft() . " AND s.rgt < " . $entity->getRgt() . '')
                            ->getQuery()
                            ->getResult();
                        $notsId = array($entity->getId());
                        foreach ($nots as $val) {
                            $notsId[] = $val['id'];
                        }
                        $q = $er->createQueryBuilder('s');
                        $linked = $q
                            ->where($q->expr()->notIn('s.id', $notsId))
                            ->andwhere("s.site = :siteId")
                            ->setParameters(array('siteId' => $options['SITE_ID']))
                            ->orderBy('s.lft', 'ASC');
                        return $linked;
                    } else {
                        return $er->createQueryBuilder('s')
                            ->where("s.site = :siteId")
                            ->orderBy('s.lft', 'ASC')
                            ->setParameters(array('siteId' => $options['SITE_ID']));
                    }

                },
                'attr' => array('class' => 'form-control col-lg-4'),
                'label' => 'Родитель',
            ));
        }
        $builder->add('template', 'text', array(
            'label' => 'Шаблон',
            'attr' => array(
                'class' => 'form-control'
            ),
            'required' => false
        ));
        $builder->add('preview_picture', 'file', array(
            'label' => 'Картинка для анонса',
            'mapped' => false,
            'required' => false
        ));
        $builder->add('detail_picture', 'file', array(
            'label' => 'Картинка для анонса',
            'required' => false,
            'mapped' => false,
        ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Novuscom\CMFBundle\Entity\Page',
            'SHOW_PARENT' => true,
            'CHOICES' => false,
            'SHOW_URL' => true,
            'SITE_ID' => false,
            //'CURRENT_LEVEL' => null
        ));
    }


    /**
     * @return string
     */
    public function getName()
    {
        return 'cmf_pagebundle_page';
    }
}
