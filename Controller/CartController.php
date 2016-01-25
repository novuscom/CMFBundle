<?php

namespace Novuscom\Bundle\CMFBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Novuscom\Bundle\CMFBundle\Entity\Cart;
use Novuscom\Bundle\CMFBundle\Form\CartType;

/**
 * Cart controller.
 *
 */
class CartController extends Controller
{

    /**
     * Lists all Cart entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('NovuscomCMFBundle:Cart')->findAll();

        return $this->render('NovuscomCMFBundle:Cart:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new Cart entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new Cart();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_cart_show', array('id' => $entity->getId())));
        }

        return $this->render('NovuscomCMFBundle:Cart:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a Cart entity.
     *
     * @param Cart $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Cart $entity)
    {
        $form = $this->createForm(new CartType(), $entity, array(
            'action' => $this->generateUrl('admin_cart_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Cart entity.
     *
     */
    public function newAction()
    {
        $entity = new Cart();
        $form   = $this->createCreateForm($entity);

        return $this->render('NovuscomCMFBundle:Cart:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Cart entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NovuscomCMFBundle:Cart')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Cart entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('NovuscomCMFBundle:Cart:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Cart entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NovuscomCMFBundle:Cart')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Cart entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('NovuscomCMFBundle:Cart:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a Cart entity.
    *
    * @param Cart $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Cart $entity)
    {
        $form = $this->createForm(new CartType(), $entity, array(
            'action' => $this->generateUrl('admin_cart_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Cart entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NovuscomCMFBundle:Cart')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Cart entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('admin_cart_edit', array('id' => $id)));
        }

        return $this->render('NovuscomCMFBundle:Cart:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a Cart entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NovuscomCMFBundle:Cart')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Cart entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_cart'));
    }

    /**
     * Creates a form to delete a Cart entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_cart_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
