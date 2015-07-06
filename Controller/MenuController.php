<?php

namespace Novuscom\CMFBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Novuscom\CMFBundle\Entity\Menu;
use Novuscom\CMFBundle\Form\MenuType;

/**
 * Menu controller.
 *
 */
class MenuController extends Controller
{

    /**
     * Lists all Menu entities.
     *
     */
    public function indexAction($site_id)
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('NovuscomCMFBundle:Menu')->findBy(array('site' => $site_id));

        return $this->render('NovuscomCMFBundle:Menu:index.html.twig', array(
            'entities' => $entities,
        ));
    }

    /**
     * Creates a new Menu entity.
     *
     */
    public function createAction(Request $request, $site_id)
    {
        $entity = new Menu();
        $em = $this->getDoctrine()->getManager();
        $site_reference = $em->getReference('Novuscom\CMFBundle\Entity\Site', $site_id);
        $entity->setSite($site_reference);
        $form = $this->createCreateForm($entity, $site_id);
        $form->handleRequest($request);

        if ($form->isValid()) {

            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_menu', array('site_id' => $site_id)));
        }

        return $this->render('NovuscomCMFBundle:Menu:new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a Menu entity.
     *
     * @param Menu $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Menu $entity, $site_id)
    {
        $form = $this->createForm(new MenuType(), $entity, array(
            'action' => $this->generateUrl('admin_menu_create', array('site_id' => $site_id)),
            'method' => 'POST',
        ));

        //$form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Menu entity.
     *
     */
    public function newAction($site_id)
    {
        $em = $this->getDoctrine()->getManager();
        $site = $em->getRepository('NovuscomCMFBundle:Site')->find($site_id);

        if (!$site) {
            throw $this->createNotFoundException('Unable to find Site entity.');
        }

        $entity = new Menu();
        $form = $this->createCreateForm($entity, $site_id);

        return $this->render('NovuscomCMFBundle:Menu:new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
            'site'=>$site
        ));
    }

    /**
     * Finds and displays a Menu entity.
     *
     */
    public function showAction($id, $site_id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NovuscomCMFBundle:Menu')->findOneBy(array('id' => $id, 'site' => $site_id));

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Menu entity.');
        }

        $items = $em->getRepository('NovuscomCMFBundle:Item')->findBy(array('menu' => $id), array('sort' => 'asc'));
        $deleteForm = $this->createDeleteForm($id, $site_id);

        return $this->render('NovuscomCMFBundle:Menu:show.html.twig', array(
            'entity' => $entity,
            'delete_form' => $deleteForm->createView(),
            'items' => $items,
        ));
    }

    /**
     * Displays a form to edit an existing Menu entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NovuscomCMFBundle:Menu')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Menu entity.');
        }

        $site = $entity->getSite();

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id, $site->getId());

        return $this->render('NovuscomCMFBundle:Menu:edit.html.twig', array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'site'=>$site,
        ));
    }

    /**
     * Creates a form to edit a Menu entity.
     *
     * @param Menu $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Menu $entity)
    {
        $form = $this->createForm(new MenuType(), $entity, array(
            'action' => $this->generateUrl('admin_menu_update', array(
                    'site_id' => $entity->getSite()->getId(),
                    'id' => $entity->getId())
            ),
            'method' => 'PUT',
        ));

        //$form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }

    /**
     * Edits an existing Menu entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NovuscomCMFBundle:Menu')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Menu entity.');
        }

        $site = $entity->getSite();

        $deleteForm = $this->createDeleteForm($id, $entity->getSite()->getId());
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('admin_menu_edit', array(
                'id' => $id,
                'site_id' => $site->getId()
            )));
        }

        return $this->render('NovuscomCMFBundle:Menu:edit.html.twig', array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'site' => $site
        ));
    }

    /**
     * Deletes a Menu entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('NovuscomCMFBundle:Menu')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Menu entity.');
        }
        $site = $entity->getSite();
        $form = $this->createDeleteForm($id, $site->getId());
        $form->handleRequest($request);

        if ($form->isValid()) {


            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_menu', array('site_id' => $site->getId())));
    }

    /**
     * Creates a form to delete a Menu entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id, $site_id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_menu_delete', array('id' => $id, 'site_id' => $site_id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm();
    }
}
