<?php

namespace Novuscom\CMFBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Novuscom\CMFBundle\Entity\Item;
use Novuscom\CMFBundle\Form\ItemType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 * Item controller.
 *
 */
class ItemController extends Controller
{

	/**
	 * Lists all Item entities.
	 *
	 */
	public function indexAction()
	{
		$em = $this->getDoctrine()->getManager();

		$entities = $em->getRepository('NovuscomCMFBundle:Item')->findAll();

		return $this->render('NovuscomCMFBundle:Item:index.html.twig', array(
			'entities' => $entities,
		));
	}

	/**
	 * Creates a new Item entity.
	 *
	 */
	public function createAction(Request $request, $menu_id, $site_id)
	{
		$entity = new Item();
		$form = $this->createCreateForm($entity, $menu_id, $site_id);
		$form->handleRequest($request);

		if ($form->isValid()) {
			$em = $this->getDoctrine()->getManager();
			$menu_reference = $em->getReference('Novuscom\CMFBundle\Entity\Menu', $menu_id);
			$entity->setMenu($menu_reference);

			$em->persist($entity);
			$em->flush();
			return $this->redirect($this->generateUrl('admin_menu_show', array(
				'id' => $menu_id,
				'site_id' => $site_id,
			)));
		}

		return $this->render('NovuscomCMFBundle:Item:new.html.twig', array(
			'entity' => $entity,
			'form' => $form->createView(),
		));
	}

	/**
	 * Creates a form to create a Item entity.
	 *
	 * @param Item $entity The entity
	 *
	 * @return \Symfony\Component\Form\Form The form
	 */
	private function createCreateForm(Item $entity, $menu_id, $site_id)
	{
		$form = $this->createForm(ItemType::class, $entity, array(
			'action' => $this->generateUrl('admin_menuitem_create', array('menu_id' => $menu_id, 'site_id' => $site_id)),
			'method' => 'POST',
		));

		//$form->add('submit', 'submit', array('label' => 'Create'));

		return $form;
	}

	/**
	 * Displays a form to create a new Item entity.
	 *
	 */
	public function newAction($menu_id, $site_id)
	{

		$em = $this->getDoctrine()->getManager();

		$menu = $em->getRepository('NovuscomCMFBundle:Menu')->find($menu_id);
		if (!$menu) {
			throw $this->createNotFoundException('Unable to find Menu entity.');
		}

		$site = $em->getRepository('NovuscomCMFBundle:Site')->find($site_id);
		if (!$site) {
			throw $this->createNotFoundException('Unable to find Site entity.');
		}

		$entity = new Item();
		$form = $this->createCreateForm($entity, $menu_id, $site_id);

		return $this->render('NovuscomCMFBundle:Item:new.html.twig', array(
			'entity' => $entity,
			'form' => $form->createView(),
			'menu' => $menu,
			'site' => $site,
		));
	}

	/**
	 * Finds and displays a Item entity.
	 *
	 */
	public function showAction($id)
	{
		$em = $this->getDoctrine()->getManager();

		$entity = $em->getRepository('NovuscomCMFBundle:Item')->find($id);

		if (!$entity) {
			throw $this->createNotFoundException('Unable to find Item entity.');
		}

		$deleteForm = $this->createDeleteForm($id);

		return $this->render('NovuscomCMFBundle:Item:show.html.twig', array(
			'entity' => $entity,
			'delete_form' => $deleteForm->createView(),
		));
	}

	/**
	 * Displays a form to edit an existing Item entity.
	 *
	 */
	public function editAction($id, $site_id, $menu_id)
	{
		$em = $this->getDoctrine()->getManager();

		$entity = $em->getRepository('NovuscomCMFBundle:Item')->findOneBy(array(
			'id' => $id,
			'menu' => $menu_id,
		));

		if (!$entity) {
			throw $this->createNotFoundException('Unable to find Item entity.');
		}

		$editForm = $this->createEditForm($entity, $site_id, $menu_id);
		$deleteForm = $this->createDeleteForm($id, $site_id, $menu_id);

		return $this->render('NovuscomCMFBundle:Item:edit.html.twig', array(
			'entity' => $entity,
			'edit_form' => $editForm->createView(),
			'delete_form' => $deleteForm->createView(),
		));
	}

	/**
	 * Creates a form to edit a Item entity.
	 *
	 * @param Item $entity The entity
	 *
	 * @return \Symfony\Component\Form\Form The form
	 */
	private function createEditForm(Item $entity, $site_id, $menu_id)
	{
		$form = $this->createForm(ItemType::class, $entity, array(
			'action' => $this->generateUrl('admin_menuitem_update', array(
				'id' => $entity->getId(),
				'site_id' => $site_id,
				'menu_id' => $menu_id
			)),
            'menu_id' => $menu_id,
			'method' => 'PUT',
		));

		//$form->add('submit', 'submit', array('label' => 'Update'));

		return $form;
	}

	/**
	 * Edits an existing Item entity.
	 *
	 */
	public function updateAction(Request $request, $id, $site_id, $menu_id)
	{
		$em = $this->getDoctrine()->getManager();

		$entity = $em->getRepository('NovuscomCMFBundle:Item')->findOneBy(array(
			'id' => $id,
			'menu' => $menu_id
		));

		if (!$entity) {
			throw $this->createNotFoundException('Unable to find Item entity.');
		}

		$deleteForm = $this->createDeleteForm($id, $site_id, $menu_id);
		$editForm = $this->createEditForm($entity, $site_id, $menu_id);
		$editForm->handleRequest($request);

		if ($editForm->isValid()) {
			$em->flush();

			return $this->redirect($this->generateUrl('admin_menuitem_edit', array(
				'id' => $id,
				'site_id' => $site_id,
				'menu_id' => $menu_id,
			)));
		}

		return $this->render('NovuscomCMFBundle:Item:edit.html.twig', array(
			'entity' => $entity,
			'edit_form' => $editForm->createView(),
			'delete_form' => $deleteForm->createView(),
		));
	}

	private function clearMenuCache($menu_id)
	{
		//$env = $this->container->getParameter("kernel.environment");
		//echo '<pre>' . print_r('Удаление кеша '.$menu_id, true) . '</pre>'; exit;
		$cacheDriver = new \Doctrine\Common\Cache\ApcuCache();
		$cacheDriver->setNamespace('menu_prod_' . $menu_id);
		$cacheDriver->deleteAll();
		$cacheDriver->setNamespace('menu_dev_' . $menu_id);
		$cacheDriver->deleteAll();
	}

	/**
	 * Deletes a Item entity.
	 *
	 */
	public function deleteAction(Request $request, $id, $site_id, $menu_id)
	{
		$form = $this->createDeleteForm($id, $site_id, $menu_id);
		$form->handleRequest($request);

		if ($form->isValid()) {
			$em = $this->getDoctrine()->getManager();
			$entity = $em->getRepository('NovuscomCMFBundle:Item')->find($id);

			if (!$entity) {
				throw $this->createNotFoundException('Unable to find Item entity.');
			}

			$em->remove($entity);
			$em->flush();
		}

		return $this->redirect($this->generateUrl('admin_menu_show', array(
			'id' => $id,
			'site_id' => $site_id
		)));
	}

	/**
	 * Creates a form to delete a Item entity by id.
	 *
	 * @param mixed $id The entity id
	 *
	 * @return \Symfony\Component\Form\Form The form
	 */
	private
	function createDeleteForm($id, $site_id, $menu_id)
	{
		return $this->createFormBuilder()
			->setAction($this->generateUrl('admin_menuitem_delete', array(
				'id' => $id,
				'site_id' => $site_id,
				'menu_id' => $menu_id,
			)))
			->setMethod('DELETE')
			->add('submit', SubmitType::class, array('label' => 'Delete'))
			->getForm();
	}
}
