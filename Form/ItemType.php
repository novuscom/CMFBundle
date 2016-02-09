<?php

namespace Novuscom\Bundle\CMFBundle\Form;

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

	    $options['MENU_ID'] = 1;
        $builder->add('parent', EntityType::class, array(
            'class' => 'NovuscomCMFBundle:Item',
            'choice_label' => 'indentedTitle',
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
                        ->andwhere("s.menu = :menuId")
                        ->setParameters(array('menuId' => $options['MENU_ID']))
                        ->orderBy('s.root, s.lft, s.sort', 'ASC');
                    return $linked;
                } else {
                    return $er->createQueryBuilder('s')
                        ->where("s.menu = :menuId")
                        ->orderBy('s.root, s.lft', 'ASC')
                        ->setParameters(array('menuId' => $options['MENU_ID']));
                }

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

    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Novuscom\Bundle\CMFBundle\Entity\Item'
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
