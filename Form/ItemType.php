<?php

namespace Novuscom\CMFBundle\Form;

use Novuscom\CMFBundle\Entity\Menu;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ItemType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
	    $entity = $builder->getData();
        $builder->add('name', TextType::class, array(
            'label'=>'Название',
        ));
        $builder->add('url', TextType::class, array(
            'label'=>'Адрес',
        ));
        $builder->add('sort', NumberType::class, array(
            'label'=>'Сортировка',
            'required'=>false,
        ));

        dump($options['menu_id']);


        $builder->add('parent', EntityType::class, array(
            'class' => 'NovuscomCMFBundle:Item',
            'choice_label' => 'indentedTitle',
            'query_builder' => function ($er) use ($options, $entity) {
                return $er->createQueryBuilder('s')
                    ->where("s.menu = :menuId")
                    ->orderBy('s.root, s.lft', 'ASC')
                    ->setParameters(array('menuId' => $options['menu_id']));

            },
            'attr' => array('class' => 'form-control'),
            'label' => 'Родитель',
	        'required' => false,
        ));
	    $builder->add('submit', SubmitType::class, array(
		    'label'=>'Сохранить',
		    'attr'=>array(
			    'class'=>'btn btn-success',
		    )
	    ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Novuscom\CMFBundle\Entity\Item',
            'menu_id' => false,
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'cmf_menubundle_item';
    }
}
