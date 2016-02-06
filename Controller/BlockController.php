<?php

namespace Novuscom\Bundle\CMFBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Novuscom\Bundle\CMFBundle\Entity\SiteBlock;
use Novuscom\Bundle\CMFBundle\Entity\Site;
use Novuscom\Bundle\CMFBundle\Entity\Block;
use Novuscom\Bundle\CMFBundle\Form\BlockType;
use \Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 * Block controller.
 *
 */
class BlockController extends Controller
{

	/**
	 * Lists all Block entities.
	 *
	 */
	public function indexAction()
	{

		$crumbs = $this->get("apy_breadcrumb_trail");
		$crumbs->add('nCMF', 'cmf_admin_homepage');
		$crumbs->add('Инфоблоки', 'admin_block');
		$em = $this->getDoctrine()->getManager();

		$User = $this->get('User');
		$sites = $User->getUserSites();

		if ($sites) {
			$entities = $em->getRepository('NovuscomCMFBundle:Block')->findBySites($sites);
		} else {
			$entities = $em->getRepository('NovuscomCMFBundle:Block')->findAll();
		}

		return $this->render('NovuscomCMFBundle:Block:index.html.twig', array(
			'entities' => $entities,
		));
	}

	/**
	 * Creates a new Block entity.
	 *
	 */
	public function createAction(Request $request)
	{
		$entity = new Block();
		$form = $this->createCreateForm($entity);
		$form->handleRequest($request);

		if ($form->isValid()) {
			$em = $this->getDoctrine()->getManager();
			if ($entity->getProperty()) {
				foreach ($entity->getProperty() as $property) {
					$property->setBlock($entity);
				}
			}


			$em->persist($entity);
			$em->flush();
			return $this->redirect($this->generateUrl('admin_block_edit', array('id' => $entity->getId())));
		}

		return $this->render('NovuscomCMFBundle:Block:new.html.twig', array(
			'entity' => $entity,
			'form' => $form->createView(),
		));
	}

	/**
	 * Creates a form to create a Block entity.
	 *
	 * @param Block $entity The entity
	 *
	 * @return \Symfony\Component\Form\Form The form
	 */
	private function createCreateForm(Block $entity)
	{
		$form = $this->createForm(BlockType::class, $entity, array(
			'action' => $this->generateUrl('admin_block_create'),
			'method' => 'POST',
		));

		$form->add('submit', SubmitType::class, array('label' => 'Создать', 'attr' => array('class' => 'btn btn-success')));

		return $form;
	}

	/**
	 * Displays a form to create a new Block entity.
	 *
	 */
	public function newAction()
	{
		$entity = new Block();
		$form = $this->createCreateForm($entity);

		return $this->render('NovuscomCMFBundle:Block:new.html.twig', array(
			'entity' => $entity,
			'form' => $form->createView(),
		));
	}

	/**
	 * Finds and displays a Block entity.
	 *
	 */
	public function showAction($id, $section_id = null)
	{
		$em = $this->getDoctrine()->getManager();

		$crumbs = $this->get("apy_breadcrumb_trail");
		$crumbs->add('nCMF', 'cmf_admin_homepage');
		//$crumbs->add('Инфоблоки', 'admin_block');

		$entity = $em->getRepository('NovuscomCMFBundle:Block')->find($id);
		$elements = array();

		if (!$entity) {
			throw $this->createNotFoundException('Инфоблок не найден');
		}

		$crumbs->add($entity->getName(), 'admin_block_show', array('id' => $entity->getId()));


		$filter = array(
			'block' => $entity,
			'parent' => null,
		);
		$section = null;

		if ($section_id) {

			$section = $em->getRepository('NovuscomCMFBundle:Section')->find($section_id);
			if (!$section) {
				throw $this->createNotFoundException('Не найден раздел инфоблока');
			}

			$filter['parent'] = $section_id;

			$parents = $em->getRepository('NovuscomCMFBundle:Section')->createQueryBuilder('s')
				->where("s.block=:block")
				->andWhere("s.lft<:left")
				->andWhere("s.rgt>:right")
				->andWhere("s.lvl<:level")
				->andWhere("s.root=:root")
				->setParameters(array(
					'block' => $entity,
					'left' => $section->getLft(),
					'right' => $section->getRgt(),
					'level' => $section->getLvl(),
					'root' => $section->getRoot(),
				))
				->orderBy('s.sort', 'ASC')
				->getQuery()
				->getResult();

			foreach ($parents as $p) {
				$crumbs->add($p->getName(), 'admin_block_show_section', array('id' => $entity->getId(), 'section_id' => $p->getId()));
			}

			$crumbs->add($section->getName(), 'admin_block_show_section', array('id' => $entity->getId(), 'section_id' => $section->getId()));

		}

		$sections = $em->getRepository('NovuscomCMFBundle:Section')->findBy($filter, array('sort' => 'ASC', 'id' => 'DESC'));


		/**
		 * Получаем элементы
		 */
		$ElementSection = $em->getRepository('NovuscomCMFBundle:ElementSection')->findBy(array(
			'section' => $section
		));
		$elementsId = array();
		foreach ($ElementSection as $es) {
			$elementsId[] = $es->getElement()->getId();
		}
		//echo '<pre>' . print_r($elementsId, true) . '</pre>';
		$elementsFilter = array(
			'block' => $entity,
		);
		if ($elementsId) {
			$elementsFilter['id'] = array_unique($elementsId); // закоментить надо
			$elements = $em->getRepository('NovuscomCMFBundle:Element')->findBy($elementsFilter);
		}


		$deleteForm = $this->createDeleteForm($id);

		$data = array(
			'block' => $entity,
			'sections' => $sections,
			'section' => $section,
			'elements' => $elements,
			'delete_form' => $deleteForm->createView(),
		);
		return $this->render('NovuscomCMFBundle:Block:show.html.twig', $data);
	}

	/**
	 * Displays a form to edit an existing Block entity.
	 *
	 */
	public function editAction($id)
	{
		$em = $this->getDoctrine()->getManager();
		$entity = $em->getRepository('NovuscomCMFBundle:Block')->find($id);
		$crumbs = $this->get("apy_breadcrumb_trail");
		$crumbs->add('nCMF', 'cmf_admin_homepage');
		$crumbs->add('Инфоблоки', 'admin_block');
		$crumbs->add($entity->getName(), 'admin_block_show', array('id' => $entity->getId()));

		if (!$entity) {
			throw $this->createNotFoundException('Unable to find Block entity.');
		}

		$editForm = $this->createEditForm($entity);
		$deleteForm = $this->createDeleteForm($id);

		return $this->render('NovuscomCMFBundle:Block:edit.html.twig', array(
			'entity' => $entity,
			'edit_form' => $editForm->createView(),
			'delete_form' => $deleteForm->createView(),
		));
	}

	private $sites;

	private function getSites()
	{
		if (empty($this->sites)) {
			$this->setSites();
		}
		return $this->sites;
	}

	private function setSites()
	{
		$em = $this->getDoctrine()->getManager();
		$sites = $em->getRepository('NovuscomCMFBundle:Site')->findAll();
		$this->sites = $sites;
	}

	private function getChoices($entities)
	{
		$result = array();
		foreach ($entities as $entity) {
			$result[$entity->getId()] = $entity->getName();
		}
		return $result;
	}


	/**
	 * Creates a form to edit a Block entity.
	 *
	 * @param Block $entity The entity
	 *
	 * @return \Symfony\Component\Form\Form The form
	 */
	private function createEditForm(Block $entity)
	{
		$form = $this->createForm(BlockType::class, $entity, array(
			'action' => $this->generateUrl('admin_block_update', array('id' => $entity->getId())),
			'method' => 'PUT',
		));

		$form->add('submit', SubmitType::class, array('label' => 'Update'));

		return $form;
	}

	/**
	 * Edits an existing Block entity.
	 *
	 */
	public function updateAction(Request $request, $id)
	{
		$em = $this->getDoctrine()->getManager();

		$entity = $em->getRepository('NovuscomCMFBundle:Block')->find($id);

		if (!$entity) {
			throw $this->createNotFoundException('Unable to find Block entity.');
		}


		$originalProperties = new ArrayCollection();
		foreach ($entity->getProperty() as $o) {
			$originalProperties->add($o);
		}


		$deleteForm = $this->createDeleteForm($id);
		$editForm = $this->createEditForm($entity);


		$editForm->handleRequest($request);
		if ($editForm->isSubmitted()) {
			if ($editForm->isValid()) {

				/*foreach ($editForm->get('property') as $property) {
					echo '<pre>' . print_r($property->getName(), true) . '</pre>';
					echo '<pre>' . print_r($property->get('code')->getData(), true) . '</pre>';
				}*/

				/*foreach ($entity->getProperty() as $property) {
					$property->setBlock($entity);
				}*/
				foreach ($originalProperties as $property) {
					if ($entity->getProperty()->contains($property) === false) {
						$entity->getProperty()->removeElement($property);
						$em->persist($entity);
						$em->remove($property);
						//$em->remove($property);
					} 
				}
				$em->flush();

				return $this->redirect($this->generateUrl('admin_block_edit', array('id' => $id)));
			}
		}

		return $this->render('NovuscomCMFBundle:Block:edit.html.twig', array(
			'entity' => $entity,
			'edit_form' => $editForm->createView(),
			'delete_form' => $deleteForm->createView(),
		));
	}

	private function updateBlockSites($entity, $new_site_id)
	{
		$em = $this->getDoctrine()->getManager();
		/**
		 * Ид сайтов к которым был привязан блок до обновления
		 */
		$original_site_id = array();
		foreach ($entity->getSiteBlock() as $site_block) {
			$original_site_id[] = $site_block->getSite()->getId();
		}
		$add_id = array_diff($new_site_id, $original_site_id);
		$remove_id = array_diff($original_site_id, $new_site_id);
		if ($add_id) {
			$sites = $em->getRepository('NovuscomCMFBundle:Site')->findBy(array('id' => $add_id));
			foreach ($sites as $add_site) {
				$sb = new SiteBlock();
				$sb->setSite($add_site);
				$sb->setBlock($entity);
				$em->persist($sb);
			}
		}
		foreach ($entity->getSiteBlock() as $SiteBlock) {
			if (in_array($SiteBlock->getSite()->getId(), $remove_id)) {
				$em->remove($SiteBlock);
			}
		}
	}

	private function msg($obj)
	{
		echo '<pre>' . print_r($obj, true) . '</pre>';
	}

	/**
	 * Deletes a Block entity.
	 *
	 */
	public function deleteAction(Request $request, $id)
	{
		$form = $this->createDeleteForm($id);
		$form->handleRequest($request);

		if ($form->isValid()) {
			$em = $this->getDoctrine()->getManager();
			$entity = $em->getRepository('NovuscomCMFBundle:Block')->find($id);

			if (!$entity) {
				throw $this->createNotFoundException('Unable to find Block entity.');
			}
			$original = $entity->getSiteBlock();
			foreach ($original as $sb) {
				$em->remove($sb);
			}
			$em->remove($entity);
			$em->flush();
		}

		return $this->redirect($this->generateUrl('admin_block'));
	}

	/**
	 * Creates a form to delete a Block entity by id.
	 *
	 * @param mixed $id The entity id
	 *
	 * @return \Symfony\Component\Form\Form The form
	 */
	private function createDeleteForm($id)
	{
		return $this->createFormBuilder()
			->setAction($this->generateUrl('admin_block_delete', array('id' => $id)))
			->setMethod('DELETE')
			->add('submit', SubmitType::class, array('label' => 'Delete'))
			->getForm();
	}
}
