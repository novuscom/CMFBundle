<?php

namespace Novuscom\CMFBundle\Form;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Tests\Common\Annotations\Ticket\Doctrine\ORM\Mapping\Entity;
use Monolog\Handler\Curl\Util;
use Novuscom\CMFBundle\Entity\Element;
use Novuscom\CMFBundle\Entity\ElementPropertySection;
use Novuscom\CMFBundle\Form\ElementPropertySMultipleType;
use Novuscom\CMFBundle\Services\Utils;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Novuscom\CMFBundle\Entity\ElementProperty;

class ElementPropertyType extends AbstractType
{

	private $options;
	private $em;
	private $data;

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		//echo '<pre>' . print_r('build ElementPropertyType', true) . '</pre>'; exit;
		//$request = $this->request;
		//$params = $request->get('_route_params');
		//$block = $this->em->getRepository('NovuscomCMFBundle:Block')->find($params['id']);
		//return true;
		//echo '<pre>' . print_r(array_keys($options['data']['VALUES']), true) . '</pre>';
		//exit;
		//$builder->add('value');

		$this->data = $options;

		//echo '<pre>' . print_r(count($options['data']['BLOCK_PROPERTIES']), true) . '</pre>'; exit;
		foreach ($options['data']['BLOCK_PROPERTIES'] as $p) {
			//$value = $p->getValue();
			//echo '<pre>' . print_r($p->getType(), true) . '</pre>';
			//echo '<pre>' . print_r($p->getInfo(), true) . '</pre>';
			//$info = $p->getInfo();

			$info = json_decode($p->getInfo(), true);
			$is_multiple = (is_array($info) && array_key_exists('MULTIPLE', $info) && $info['MULTIPLE'] == true);
			$required = (is_array($info) && array_key_exists('REQUIRED', $info) && $info['REQUIRED'] == true);
			//echo '<pre>' . print_r($p->getType(), true) . '</pre>';
			$property_reference = $this->em->getReference('Novuscom\CMFBundle\Entity\Property', $p->getId());
			switch ($p->getType()) {
				/**
				 * Поле типа "Список"
				 */
				case 'LIST':

					/**
					 * Получаем значения свойства
					 */

					$PropertyList = $this->em->getRepository('NovuscomCMFBundle:PropertyList')->findBy(array(
						'property' => $property_reference,
					));
					$choices = array();
					foreach ($PropertyList as $pl) {
						$choices[$pl->getId()] = $pl->getValue();
					}

					/**
					 * Получаем значение элемента
					 */
					$values = $this->getPropertyValues($p->getId());
					$data = false;
					if ($values) {
						$data = $values[0];
					}


					/**
					 * Формируем поле
					 */
					$choiceOptions = array(
						'choices' => $choices,
						'required' => false,
						'multiple' => false,
						'label' => $p->getName(),
						'mapped' => false,
						'data' => $data,
					);
					if (isset($this->data['VALUES'][$p->getId()]) && is_numeric($this->data['VALUES'][$p->getId()])) {
						$choiceOptions['data'] = $this->data['VALUES'][$p->getId()];
					}
					$builder->add($p->getId(), ChoiceType::class, $choiceOptions);
					break;
				case 'E':
					$elements = $this->em->getRepository('NovuscomCMFBundle:Element')->findBy(array(
						'block' => $info['BLOCK_ID'],
					));
					$choices = array();
					foreach ($elements as $e) {
						$choices[$e->getName()] = $e->getId();
					}
					//echo '<pre>' . print_r($choices, true) . '</pre>';
					if (is_array($info) && array_key_exists('MULTIPLE', $info) && $info['MULTIPLE'] == true) {

						//$FPECollection = new \Doctrine\Common\Collections\ArrayCollection();


						//echo '<pre>' . print_r($this->data['VALUES'][$p->getId()], true) . '</pre>';
						/*if (isset($this->data['VALUES']) && is_array($this->data['VALUES']) && array_key_exists($p->getId(), $this->data['VALUES'])) {
							foreach ($this->data['VALUES'][$p->getId()] as $v) {
								$FPE = new ElementProperty();
								$FPE->setValue($v);
								$FPECollection->add($FPE);
							}
						}*/


						/*$builder->add($p->getId(), ChoiceType::class,
							array(
								//'type' => new ElementPropertyEMultipleType(
								//	$choices, array('PROPERTY' => $p)
								//),
								'entry_type' => Element::class,
								'mapped' => false,
								'allow_add' => true,
								'label' => $p->getName(),
								'allow_delete' => true,
								//'cascade_validation' => true,
								'by_reference' => false,
								'data' => $FPECollection
							)
						);*/


						/*$builder->addEventListener(
							FormEvents::BIND,
							function (FormEvent $event) {
								echo '<pre>' . print_r('BIND', true) . '</pre>';
								$data = $event->getData();
								$lineups = $data->getLineups();
								foreach ($lineups as &$lineup) {
									$lineup->setMatch($data);
								}
								$event->setData($data);
							}
						);*/

						$choiceOptions = array(
							'choices' => $choices,
							'required' => false,
							'multiple' => true,
							'label' => $p->getName(),
							'mapped' => false,
						);

						//echo '<pre>'.print_r($this->data['VALUES'], true).'</pre>';

						if (isset($options['data']['VALUES'][$p->getId()]) && $options['data']['VALUES'][$p->getId()]) {
							//$choiceOptions['data'] = $this->data['VALUES'][$p->getId()];
							//echo '<pre>'.print_r($options['data']['VALUES'][$p->getId()], true).'</pre>';
							$choiceOptions['data'] = $options['data']['VALUES'][$p->getId()];
						}
						$builder->add($p->getId(), ChoiceType::class, $choiceOptions);
					} else {
						$choiceOptions = array(
							'choices' => $choices,
							'required' => false,
							'multiple' => false,
							'label' => $p->getName(),
							'mapped' => false,
						);
						//Utils::msg($options['data']['VALUES'][$p->getId()][0]);
						if (isset($options['data']['VALUES'][$p->getId()]) && is_numeric($options['data']['VALUES'][$p->getId()][0])) {
							$choiceOptions['data'] = $options['data']['VALUES'][$p->getId()][0];
						}
						$builder->add($p->getId(), ChoiceType::class, $choiceOptions);
					}


					break;
				case "SECTION":
					$sections = new ArrayCollection();
					$builderData = $builder->getData();
					$element = $builderData['ELEMENT_ENTITY'];
					if ($element->getId()) {
						$ElementPropertySection = $this->em->getRepository('NovuscomCMFBundle:ElementPropertySection')->findBy(array(
							'element' => $element,
							'property' => $property_reference
						));
						$sectionsId = array();
						foreach ($ElementPropertySection as $es) {
							$sectionsId[] = $es->getSection()->getId();
						}
						if ($sectionsId) {
							$sectionsEntity = $this->em->getRepository('NovuscomCMFBundle:Section')->findBy(array('id' => $sectionsId));
							foreach ($sectionsEntity as $s) {
								$sections->add($s);
							}
						}
					}

					$builder->add($p->getId(), EntityType::class, array(
						'label' => 'Раздел',
						'class' => 'NovuscomCMFBundle:Section',
						'choice_label' => 'indentedTitle',
						//'expanded' => false,
						'multiple' => false,
						'required' => false,
						//'empty_data' => null,
						'mapped' => true,
						'attr' => array('size' => '20'),
						'data' => $sections[0],
						'query_builder' => function ($er) use ($info) {
							return $er->createQueryBuilder('s')
								->where("s.block = :block")
								->orderBy('s.root, s.lft', 'ASC')
								->setParameters(array('block' => $info['BLOCK_ID']));
						}
					));
					break;
				case 'F':
					if ($is_multiple) {
						$dataAtr = array();
						if (
							isset($options['data']['PROPERTY_FILE_VALUES'][$p->getId()])
							&& is_array($options['data']['PROPERTY_FILE_VALUES'])
							&& array_key_exists($p->getId(), $options['data']['PROPERTY_FILE_VALUES'])
						) {
							$filesId = $options['data']['PROPERTY_FILE_VALUES'][$p->getId()];
							$files = $this->em->getRepository('NovuscomCMFBundle:File')->findBy(array(
								'id' => $filesId,
							));
							$rFilesId = array_flip($filesId);
							//echo '<pre>' . print_r($rFilesId, true) . '</pre>';
							if (count($files) > 0) {
								$liip = $options['data']['LIIP'];
								$dataAtr['files'] = array();
								foreach ($files as $file) {
									//echo '<pre>' . print_r($file->getId(), true) . '</pre>';
									$fileInfo = array();
									$originalPath = '/upload/etc/' . $file->getName();
									$path = $liip->getBrowserPath($originalPath, 'my_thumb');
									$fileInfo['path'] = $path;
									$fileInfo['original_path'] = $originalPath;
									$fileInfo['file_id'] = $file->getId();
									$fileInfo['property_id'] = $p->getId();
									$fileInfo['property_row_id'] = $rFilesId[$file->getId()];
									$dataAtr['files'][] = $fileInfo;
								}
								//Utils::msg($dataAtr);
							}

						}
						$jsonData = json_encode($dataAtr);
						$builder->add($p->getId(), CollectionType::class,
							array(
								'entry_type' => ElementPropertyFMultipleType::class,
								'mapped' => false,
								'allow_add' => true,
								'label' => $p->getName(),
								'allow_delete' => true,
								'by_reference' => false,
								'label_attr' => array(
									'class' => 'files-property',
									'data-type' => 'files',
									'data-data' => $jsonData,
									'data-id' => $p->getId()
								)
							)
						);
					}
					break;
				/**
				 * Дата/время
				 */
				case 'DATE_TIME':
					$field_options = array(
						'label' => $p->getName(),
						'mapped' => false,
						'required' => false,
					);
					if ($info && is_array($info) && array_key_exists('MULTIPLE', $info) && $info['MULTIPLE'] == true) {

					} else {
						if (isset($options['data']['VALUES'][$p->getId()][0])) {
							//echo '<pre>'.print_r($options['data']['VALUES'][$p->getId()][0], true).'</pre>';
							if ($options['data']['VALUES'][$p->getId()][0] instanceof \DateTime)
								$field_options['data'] = $options['data']['VALUES'][$p->getId()][0];


						}
					}


					$builder->add(
						$p->getId(),
						DateTimeType::class,
						$field_options
					);
					break;
				case 'TEXT':
					$field_options = array(
						'label' => $p->getName(),
						'mapped' => false,
						'required' => false,
					);
					if (isset($this->data['VALUES'][$p->getId()])) {
						$field_options['data'] = $this->data['VALUES'][$p->getId()][0];
					}
					$field_options['attr'] = array(
						'style' => 'height: 150px;'
					);
					$builder->add(
						$p->getId(),
						TextareaType::class,
						$field_options
					);
					break;
				case 'HTML':
					$field_options = array(
						'label' => $p->getName(),
						'mapped' => false,
						'required' => false,
					);
					//echo '<pre>' . print_r($options['data']['VALUES'], true) . '</pre>';
					if (isset($options['data']['VALUES']) && is_array($options['data']['VALUES']) && array_key_exists($p->getId(), $options['data']['VALUES'])) {
						$field_options['data'] = $options['data']['VALUES'][$p->getId()][0];
					}
					$field_options['attr'] = array(
						'style' => 'height: 300px;',
						'class' => 'tinymce'
					);
					$builder->add(
						$p->getId(),
						TextareaType::class,
						$field_options
					);
					break;
				case 'N':
					$field_options = array(
						'label' => $p->getName(),
						'mapped' => false,
						'required' => false,
					);
					if ($this->getPropertyValues($p->getId())) {
						$field_options['data'] = $this->getPropertyValues($p->getId())[0];
					}
					$builder->add(
						$p->getId(),
						TextType::class,
						$field_options
					);
					break;
				case 'BOOLEAN':
					$field_options = array(
						'label' => $p->getName(),
						'mapped' => false,
						'required' => false,
						'value' => 1
					);
					if (isset($options['data']['VALUES'][$p->getId()])) {
						$field_options['data'] = true;
					}
					//echo '<pre>' . print_r($field_options, true) . '</pre>';
					$builder->add(
						$p->getId(),
						CheckboxType::class,
						$field_options
					);
					break;
				case 'U':
					$users = $this->em->getRepository('NovuscomCMFUserBundle:User')->findAll();
					$users_array = array();
					foreach ($users as $u) {
						$users_array[$u->getUsername()] = $u->getId();
					}
					$field_options = array(
						'choices' => $users_array,
						'required' => false,
						'label' => $p->getName(),
						'mapped' => false,
						'attr' => array(
							'class' => 'form-control'
						),
					);
					if (isset($this->data['VALUES'][$p->getId()])) {
						//echo '<pre>' . print_r($this->data['VALUES'][$p->getId()], true) . '</pre>';
						$field_options['data'] = $this->data['VALUES'][$p->getId()][0];
					}
					$builder->add($p->getId(), ChoiceType::class, $field_options);
					break;
				default:
					//echo '<pre>' . print_r($p->getInfo(), true) . '</pre>';
					//$info = json_decode($p->getInfo(), true);
					//echo '<pre>' . print_r($info, true) . '</pre>';
					//echo '<pre>' . print_r($options['data'], true) . '</pre>';

					//Utils::msg($options['data']['VALUES']);

					if ($info && is_array($info) && array_key_exists('MULTIPLE', $info) && $info['MULTIPLE'] == true) {
						//Utils::msg($info);
						//Utils::msg($this->getPropertyValues($p->getId()));


						//$data = array();

						/*
						$element_reference = $this->em->getReference('Novuscom\CMFBundle\Entity\Element', 251);
						$property_reference = $this->em->getReference('Novuscom\CMFBundle\Entity\Property', $p->getId());
						$ElementProperty = $this->em->getRepository('NovuscomCMFBundle:ElementProperty')->findBy(array(
							'element'=>$element_reference,
							'property'=>$property_reference
						));*/

						//echo '<pre>' . print_r($this->data['VALUES'][$p->getId()], true) . '</pre>';

						//echo '<pre>' . print_r($this->getPropertyValues($p->getId()), true) . '</pre>';
						$collection = new \Doctrine\Common\Collections\ArrayCollection();
						foreach ($this->getPropertyValues($p->getId()) as $k=>$v) {
							$ep = new ElementProperty();
							$ep->setValue($v);
							$ep->setDescription($this->getPropertyDescriptions($p->getId())[$k]);
							$collection->add($ep);
							//echo '<pre>' . print_r($v, true) . '</pre>';
						}
						$field_options = array(
							'entry_type' => ElementPropertySMultipleType::class,
							'mapped' => true,
							//'data_class' => null,
							'allow_add' => true,
							'label' => $p->getName(),
							'allow_delete' => true,
							//'by_reference' => false,
							'label_attr' => array(
								//'class' => 'files-property',
								//'data-type' => 'files',
								//'data-data' => $jsonData,
								//'data-id' => $p->getId()
							),
							/*'entry_options' => array(
								'data' => $collection
							),*/
							/*'data' => array(
								new ElementProperty()
							),*/
							'data' => $collection,
							//'data' => $ep,
						);
						//Utils::msg($this->getPropertyValues($p->getId()));
						//$field_options['entry_options']['data'] = $this->getPropertyValues($p->getId());
						//$field_options['entry_options']['data'] = $collection;
						//$field_options['data'] = $collection;
						$builder->add($p->getId(), CollectionType::class, $field_options);
					} else {
						$optionsArray = array(
							'mapped' => false,
							'label' => $p->getName(),
							'required' => $required,
							'attr' => array(
								'class' => 'form-control'
							),
						);

						if (isset($options['data']['VALUES']) && is_array($options['data']['VALUES']) && array_key_exists($p->getId(), $options['data']['VALUES'])) {
							$optionsArray['data'] = $options['data']['VALUES'][$p->getId()][0];
						}
						$builder->add(
							$p->getId(),
							TextType::class,
							$optionsArray
						);
					}

			}
			/*$builder->add('replaced_files', CollectionType::class,
				array(
					'type' => new ElementPropertyFMultipleType(false, false, array('REPLACED' => true)),
					'mapped' => false,
					'allow_add' => true,
					'allow_delete' => true,
					'cascade_validation' => true,
					'by_reference' => false,
					'label_attr' => array(
						'class' => 'files-property',
						'data-type' => 'files',
					)
				)
			);
			$builder->add('deleted_files', CollectionType::class,
				array(
					'type' => new ElementPropertyFMultipleType(false, false, array('DELETED' => true)),
					'mapped' => false,
					'allow_add' => true,
					'allow_delete' => true,
					'cascade_validation' => true,
					'by_reference' => false,
					'label_attr' => array(
						'class' => 'files-property',
						'data-type' => 'files',
					)
				)
			);*/
			//$builder->add('tags', 'collection', array('type' => new ElementPropertyStringType($this->options)));
			//$propertyStringForm = new ElementPropertyStringType($this->options);
			//$builder->add($p->getCode(), $propertyStringForm, array('mapped' => false, 'label' => 'Текстовые свойства'));
		}


		//$propertyStringForm = new ElementPropertyStringType($this->options);
		//$builder->add('value', $propertyStringForm, array('mapped' => false, 'label' => 'Текстовые свойства'));


		//$builder->add('tags', 'collection', array('type' => new ElementPropertyStringType()));
		/*$builder->addEventListener(
			FormEvents::PRE_SET_DATA,
			function (FormEvent $event) {
				$form = $event->getForm();
				$data = $event->getData();
				echo '<pre>' . print_r($form->get('11')->getData(), true) . '</pre>';

			}
		);*/


	}
	private function getPropertyDescriptions($property_id)
	{
		$result = array();
		if (isset($this->data['data']['DESCRIPTION']) && is_array($this->data['data']['DESCRIPTION']) && array_key_exists($property_id, $this->data['data']['DESCRIPTION'])) {
			$result = $this->data['data']['DESCRIPTION'][$property_id];
		};
		return $result;
	}

	private function getPropertyValues($property_id)
	{
		$result = array();
		if (isset($this->data['data']['VALUES']) && is_array($this->data['data']['VALUES']) && array_key_exists($property_id, $this->data['data']['VALUES'])) {
			$result = $this->data['data']['VALUES'][$property_id];
		};
		return $result;
	}


	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			//'data_class' => 'Novuscom\CMFBundle\Entity\ElementProperty',
			'data_class' => null,
			'data' => null,
		));
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'cmf_blockbundle_elementproperty';
	}

	public function __construct($em)
	{
		$this->em = $em;
	}
}


