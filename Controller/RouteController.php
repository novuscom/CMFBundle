<?php

namespace Novuscom\CMFBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Novuscom\CMFBundle\Entity\Route;
use Novuscom\CMFBundle\Form\RouteType;

/**
 * Route controller.
 *
 */
class RouteController extends Controller
{

    /**
     * Lists all Route entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('NovuscomCMFBundle:Route')->findAll();

        return $this->render('NovuscomCMFBundle:Route:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new Route entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new Route();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $this->clearCache();
            return $this->redirect($this->generateUrl('admin_route', array('id' => $entity->getId())));
        }

        return $this->render('NovuscomCMFBundle:Route:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
    * Creates a form to create a Route entity.
    *
    * @param Route $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Route $entity)
    {
        $entity->setActive(true);
        $form = $this->createForm(RouteType::class, $entity, array(
            'action' => $this->generateUrl('admin_route_create'),
            'method' => 'POST',
        ));

        return $form;
    }

    /**
     * Displays a form to create a new Route entity.
     *
     */
    public function newAction()
    {
        $entity = new Route();
        $form   = $this->createCreateForm($entity);

        return $this->render('NovuscomCMFBundle:Route:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Route entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NovuscomCMFBundle:Route')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Route entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('NovuscomCMFBundle:Route:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),        ));
    }

    /**
     * Displays a form to edit an existing Route entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NovuscomCMFBundle:Route')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Route entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('NovuscomCMFBundle:Route:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a Route entity.
    *
    * @param Route $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Route $entity)
    {
        $form = $this->createForm(RouteType::class, $entity, array(
            'action' => $this->generateUrl('admin_route_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        return $form;
    }

    /**
     * Edits an existing Route entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NovuscomCMFBundle:Route')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Route entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $em->flush();
            $this->clearCache();
            return $this->redirect($this->generateUrl('admin_route_edit', array('id' => $id)));
        }

        return $this->render('NovuscomCMFBundle:Route:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    private function clearCache(){
	    $files = array(
		    $_SERVER['DOCUMENT_ROOT'].'/../app/cache/dev/appDevUrlGenerator.php',
		    $_SERVER['DOCUMENT_ROOT'].'/../app/cache/dev/appDevUrlMatcher.php',
		    $_SERVER['DOCUMENT_ROOT'].'/../app/cache/prod/appProdUrlGenerator.php',
		    $_SERVER['DOCUMENT_ROOT'].'/../app/cache/prod/appProdUrlMatcher.php',
		    $_SERVER['DOCUMENT_ROOT'].'/../var/cache/dev/appDevUrlGenerator.php',
		    $_SERVER['DOCUMENT_ROOT'].'/../var/cache/dev/appDevUrlMatcher.php',
		    $_SERVER['DOCUMENT_ROOT'].'/../var/cache/prod/appProdUrlGenerator.php',
		    $_SERVER['DOCUMENT_ROOT'].'/../var/cache/prod/appProdUrlMatcher.php',
	    );
	    foreach ($files as $filenameForRemove) {
		    if (file_exists($filenameForRemove) &&
			    is_writable($filenameForRemove))
		    {
			    unlink ( $filenameForRemove );
		    }
	    }
    }

    /**
     * Deletes a Route entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NovuscomCMFBundle:Route')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Route entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_route'));
    }

    /**
     * Creates a form to delete a Route entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_route_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', SubmitType::class, array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
