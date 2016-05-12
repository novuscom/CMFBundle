<?php

namespace Novuscom\CMFBundle\Form;

use Monolog\Handler\Curl\Util;
use Novuscom\CMFBundle\Entity\Element;
use Novuscom\CMFBundle\Services\Utils;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
			switch ($p->getType()) {
				/**
				 * Поле типа "Список"
				 */
				case 'LIST':

					/**
					 * Получаем значения свойства
					 */
					$property_reference = $this->em->getReference('Novuscom\CMFBundle\Entity\Property', $p->getId());
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
				case 'F':
					if ($is_multiple) {
						$dataAtr = array();
						//echo '<pre>' . print_r($this->data['VALUES'], true) . '</pre>';
						if (
							isset($this->data['PROPERTY_FILE_VALUES'][$p->getId()])
							&& is_array($this->data['PROPERTY_FILE_VALUES'])
							&& array_key_exists($p->getId(), $this->data['PROPERTY_FILE_VALUES'])
						) {
							//$choiceOptions['data'] = $this->data['VALUES'][$p->getId()];
							$filesId = $this->data['PROPERTY_FILE_VALUES'][$p->getId()];
							//echo '<pre>' . print_r($filesId, true) . '</pre>';
							$files = $this->em->getRepository('NovuscomCMFBundle:File')->findBy(array(
								'id' => $filesId,
							));

							if (count($files) > 0) {
								$liip = $this->data['LIIP'];
								$dataAtr['files'] = array();
								foreach ($files as $file) {
									$fileInfo = array();
									$originalPath = '/upload/images/' . $file->getName();
									$path = $liip->getBrowserPath($originalPath, 'my_thumb');
									$fileInfo['path'] = $path;
									$fileInfo['original_path'] = $originalPath;
									$fileInfo['file_id'] = $file->getId();
									$fileInfo['property_id'] = $p->getId();
									$dataAtr['files'][] = $fileInfo;
								}

							}

						}
						$jsonData = json_encode($dataAtr);
						$builder->add($p->getId(), 'collection',
							array(
								'type' => new ElementPropertyFMultipleType(),
								'mapped' => false,
								'allow_add' => true,
								'label' => $p->getName(),
								'allow_delete' => true,
								'cascade_validation' => true,
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
					if (isset($this->data['VALUES'][$p->getId()])) {
						if (is_a($this->data['VALUES'][$p->getId()][0], 'DateTime')) {
							$field_options['data'] = $this->data['VALUES'][$p->getId()][0];
						}

					}
					$builder->add(
						$p->getId(),
						'datetime',
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
						'textarea',
						$field_options
					);
					break;
				case 'N':
					$field_options = array(
						'label' => $p->getName(),
						'mapped' => false,
						'required' => false,
					);
					if (isset($options['data']['VALUES'][$p->getId()])) {
						$field_options['data'] = $options['data']['VALUES'][$p->getId()][0];
					}
					$builder->add(
						$p->getId(),
						TextType::class,
						$field_options
					);
					break;
				case 'U':
					$users = $this->em->getRepository('NovuscomCMFUserBundle:User')->findAll();
					$users_array = array();
					foreach ($users as $u) {
						$users_array[$u->getId()] = $u->getUsername();
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
					$builder->add($p->getId(), 'choice', $field_options);
					break;
				default:
					//echo '<pre>' . print_r($p->getInfo(), true) . '</pre>';
					//$info = json_decode($p->getInfo(), true);
					//echo '<pre>' . print_r($info, true) . '</pre>';
					if ($info && is_array($info) && array_key_exists('MULTIPLE', $info) && $info['MULTIPLE'] == true) {
						$data = array();
						/*
						$element_reference = $this->em->getReference('Novuscom\CMFBundle\Entity\Element', 251);
						$property_reference = $this->em->getReference('Novuscom\CMFBundle\Entity\Property', $p->getId());
						$ElementProperty = $this->em->getRepository('NovuscomCMFBundle:ElementProperty')->findBy(array(
							'element'=>$element_reference,
							'property'=>$property_reference
						));*/
						//echo '<pre>' . print_r($this->data['VALUES'][$p->getId()], true) . '</pre>';


						$collection = new \Doctrine\Common\Collections\ArrayCollection();

						foreach ($this->getPropertyValues($p->getId()) as $v) {
							$ep = new ElementProperty();
							$ep->setValue($v);
							$collection->add($ep);
						}


						$builder->add($p->getId(), 'collection',
							array(
								'type' => new ElementPropertySMultipleType($data),
								'mapped' => false,
								'allow_add' => true,
								'label' => $p->getName(),
								'allow_delete' => true,
								'cascade_validation' => true,
								'by_reference' => false,
								'data' => $collection
							)
						);
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


	private function getPropertyValues($property_id)
	{
		$result = array();
		if (isset($this->data['VALUES']) && is_array($this->data['VALUES']) && array_key_exists($property_id, $this->data['VALUES'])) {
			$result = $this->data['VALUES'][$property_id];
		};
		return $result;
	}


	public function setDefaultOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			//'data_class' => 'Novuscom\CMFBundle\Entity\FormProperty',
			'data_class' => 'Novuscom\CMFBundle\Entity\ElementProperty',
			'data' => null,
			'test_option_kakaha' => false,
		));
		/*$resolver->setRequired(array(
			'em',
		));
		$resolver->setAllowedTypes(array(
			'em' => 'Doctrine\Common\Persistence\ObjectManager',
		));*/
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'cmf_blockbundle_elementproperty';
	}

	private $request;

	public function __construct($em)
	{
		$this->em = $em;
	}
}


