<?php

namespace Novuscom\CMFBundle\Controller;

use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
use Doctrine\Common\Collections\ArrayCollection;
use Novuscom\CMFBundle\Entity\Site;
use Novuscom\CMFBundle\Form\SiteType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 * Site controller.
 * @Breadcrumb("CMF", route="cmf_admin_homepage")
 */
class SiteController extends Controller
{

	/**
	 * Lists all Site entities.
	 *
	 * @Breadcrumb("Сайты", route="cmf_admin_site_list")
	 */
	public function indexAction()
	{
		$em = $this->getDoctrine()->getManager();
		$usr = $this->getUser();
		$queryText = 'SELECT s, a FROM NovuscomCMFBundle:Site s LEFT JOIN s.aliases a';
		if ($usr->getSitesId()) {
			$queryText .= ' WHERE s.id IN (:sites)';
		}
		$query = $em->createQuery($queryText);
		if ($usr->getSitesId()) {
			$query->setParameter('sites', $usr->getSitesId());
		}
		$entities = $query->getResult();
		return $this->render('NovuscomCMFBundle:Site:index.html.twig', array(
			'entities' => $entities,
		));
	}

	/**
	 * Creates a new Site entity.
	 *
	 */
	public function createAction(Request $request)
	{
		$this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
		$entity = new Site();
		$form = $this->createCreateForm($entity);
		$form->handleRequest($request);

		if ($form->isValid()) {
			$em = $this->getDoctrine()->getManager();


			$em->persist($entity);
			$em->flush();

			return $this->redirect($this->generateUrl('cmf_admin_site_show', array('id' => $entity->getId())));
		}

		return $this->render('NovuscomCMFBundle:Site:new.html.twig', array(
			'entity' => $entity,
			'form' => $form->createView(),
		));
	}

	/**
	 * Creates a form to create a Site entity.
	 *
	 * @param Site $entity The entity
	 *
	 * @return \Symfony\Component\Form\Form The form
	 */
	private function createCreateForm(Site $entity)
	{
		$form = $this->createForm(SiteType::class, $entity, array(
			'action' => $this->generateUrl('cmf_admin_site_create'),
			'method' => 'POST',
		));

		return $form;
	}

	/**
	 * Displays a form to create a new Site entity.
	 *
	 */
	public function newAction()
	{
		$this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
		$entity = new Site();
		$form = $this->createCreateForm($entity);

		return $this->render('NovuscomCMFBundle:Site:new.html.twig', array(
			'entity' => $entity,
			'form' => $form->createView(),
		));
	}

	/**
	 * Finds and displays a Site entity.
	 *
	 * @Breadcrumb("Сайты", route="cmf_admin_site_list")
	 *
	 */
	public function showAction($id)
	{
		$em = $this->getDoctrine()->getManager();


		$entity = $em->getRepository('NovuscomCMFBundle:Site')->find($id);

		if (!$entity) {
			throw $this->createNotFoundException('Unable to find Site entity.');
		}
		$this->get("apy_breadcrumb_trail")->add($entity->getName(), 'cmf_admin_site_show', array("id" => $id));
		$deleteForm = $this->createDeleteForm($id);

		return $this->render('NovuscomCMFBundle:Site:show.html.twig', array(
			'entity' => $entity,
			'delete_form' => $deleteForm->createView(),
		));
	}

	/**
	 * Displays a form to edit an existing Site entity.
	 *
	 */
	public function editAction($id)
	{
		$User = $this->get('User');
		$userSites = $User->getUserSites();
		if ($userSites && !array_key_exists($id, $userSites)) {
			throw $this->createAccessDeniedException('Доступ запрещен');
		}
		$em = $this->getDoctrine()->getManager();

		$entity = $em->getRepository('NovuscomCMFBundle:Site')->find($id);

		if (!$entity) {
			throw $this->createNotFoundException('Unable to find Site entity.');
		}

		// dummy code - this is here just so that the Task has some tags
		// otherwise, this isn't an interesting example

		/*foreach ($entity->getAliases() as $alias) {
			//$em->persist($alias);
			$em->remove($alias);
		}*/
		//$em->flush();
		// end dummy code


		$editForm = $this->createEditForm($entity);
		$deleteForm = $this->createDeleteForm($id);

		return $this->render('NovuscomCMFBundle:Site:edit.html.twig', array(
			'entity' => $entity,
			'edit_form' => $editForm->createView(),
			'delete_form' => $deleteForm->createView(),
		));
	}

	/**
	 * Creates a form to edit a Site entity.
	 *
	 * @param Site $entity The entity
	 *
	 * @return \Symfony\Component\Form\Form The form
	 */
	private function createEditForm(Site $entity)
	{
		$form = $this->createForm(SiteType::class, $entity, array(
			'action' => $this->generateUrl('cmf_admin_site_update', array('id' => $entity->getId())),
			'method' => 'PUT',
		));
		/* $form->add('sa', 'collection', array(
			 'label' => 'Алиас',
			 'type' => AliasType::class,
			 'prototype' => true,
			 'allow_add' => true,
			 'allow_delete' => true,
			 'mapped'=>false,
			 'data'=>$entity->getAliases()
			 //'by_reference' => false,
		 ));*/

		return $form;
	}

	/**
	 * Edits an existing Site entity.
	 *
	 */
	public function updateAction(Request $request, $id)
	{
		$em = $this->getDoctrine()->getManager();

		$entity = $em->getRepository('NovuscomCMFBundle:Site')->find($id);

		if (!$entity) {
			throw $this->createNotFoundException('Unable to find Site entity.');
		}

		$Site = $this->get('Site');


		$clearAliases = array();

		$originalAliases = new ArrayCollection();
		foreach ($entity->getAliases() as $alias) {
			$clearAliases[] = $alias->getName();
			$originalAliases->add($alias);
		}
		$deleteForm = $this->createDeleteForm($id);
		$editForm = $this->createEditForm($entity);
		$editForm->handleRequest($request);
		if ($editForm->isSubmitted()) {
			if ($editForm->isValid()) {
				foreach ($originalAliases as $alias) {
					$clearAliases[] = $alias->getName();
					if ($entity->getAliases()->contains($alias) === false) {
						$entity->getAliases()->removeElement($alias);
						$em->persist($entity);
						$em->remove($alias);
					}
				}
				$clearAliases = array_unique($clearAliases);
				foreach ($clearAliases as $a) {
					$Site->clearCache($a);
				}
				$em->flush();
				//return $this->redirect($this->generateUrl('cmf_admin_site_edit', array('id' => $id)));
			} else {
			}
		} else {

		}

		return $this->render('NovuscomCMFBundle:Site:edit.html.twig', array(
			'entity' => $entity,
			'edit_form' => $editForm->createView(),
			'delete_form' => $deleteForm->createView(),
		));
	}


	private function msg($obj)
	{
		echo '<pre>' . print_r($obj, true) . '</pre>';
	}

	private function deleteCollections($em, $init, $final)
	{
		if (empty($init)) {
			return;
		}

		if (!$final->getAliases() instanceof \Doctrine\ORM\PersistentCollection) {
			foreach ($init['aliases'] as $addr) {
				$em->remove($addr);
			}
		}
	}


	/**
	 * Deletes a Site entity.
	 *
	 */
	public function deleteAction(Request $request, $id)
	{
		$form = $this->createDeleteForm($id);
		$form->handleRequest($request);

		if ($form->isValid()) {
			$em = $this->getDoctrine()->getManager();
			$entity = $em->getRepository('NovuscomCMFBundle:Site')->find($id);

			if (!$entity) {
				throw $this->createNotFoundException('Unable to find Site entity.');
			}

			$em->remove($entity);
			$em->flush();
		}

		return $this->redirect($this->generateUrl('cmf_admin_site_list'));
	}

	/**
	 * Creates a form to delete a Site entity by id.
	 *
	 * @param mixed $id The entity id
	 *
	 * @return \Symfony\Component\Form\Form The form
	 */
	private function createDeleteForm($id)
	{
		return $this->createFormBuilder()
			->setAction($this->generateUrl('cmf_admin_site_delete', array('id' => $id)))
			->setMethod('DELETE')
			->add('submit', SubmitType::class, array('label' => 'Удалить', 'attr' => array('class' => 'btn btn-danger')))
			->getForm();
	}
}
