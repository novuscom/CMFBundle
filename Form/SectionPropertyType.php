<?php

namespace Novuscom\CMFBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class SectionPropertyType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('value')
            ->add('description')
            ->add('property')
        ;
        $builder->add('section', EntityType::class, array(
            'label' => 'Раздел',
            'class' => 'NovuscomCMFBundle:Section',
            'choice_label' => 'indentedTitle',
            //'expanded' => false,
            'multiple' => false,
            'required' => true,
            //'empty_data' => null,
            'mapped' => true,
            'attr' => array('size' => '20'),
            //'data' => $sections[0],
            /*'query_builder' => function ($er) use ($info) {
                return $er->createQueryBuilder('s')
                    ->where("s.block = :block")
                    ->orderBy('s.root, s.lft', 'ASC')
                    ->setParameters(array('block' => $info['BLOCK_ID']));
            }*/
        ));
	    $builder->add('property', EntityType::class, array(
		    'label' => 'Свойство',
		    'class' => 'NovuscomCMFBundle:Property',
		    'choice_label' => 'name',
		    //'expanded' => false,
		    'multiple' => false,
		    'required' => true,
		    //'empty_data' => null,
		    'mapped' => true,
		    'attr' => array('size' => '20'),
		    //'data' => $sections[0],
		    'query_builder' => function ($er) use ($options) {
				return $er->createQueryBuilder('s')
					->where("s.isForSection = :isForSection")
					->setParameters(array('isForSection' => true));
			}
	    ));
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Novuscom\CMFBundle\Entity\SectionProperty',
        ));
    }
}
