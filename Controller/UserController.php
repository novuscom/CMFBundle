<?php

namespace Novuscom\CMFBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Novuscom\CMFBundle\Entity\User;
use Novuscom\CMFBundle\Form\UserType;


/**
 * User controller.
 *
 */
class UserController extends Controller
{

    /**
     * Lists all User entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('NovuscomCMFBundle:User')->findAll();

        return $this->render('NovuscomCMFBundle:User:index.html.twig', array(
            'entities' => $entities,
        ));
    }

    /**
     * Creates a new User entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new User();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);

            $userManager = $this->container->get('fos_user.user_manager');
            if ($form->get('password')->getData()) {

                $entity->setPassword($form->get('password')->getData());

            }
            //$userManager->updateUser($entity);

            $em->flush();

            return $this->redirect($this->generateUrl('admin_user_edit', array('id' => $entity->getId())));
        }

        return $this->render('NovuscomCMFBundle:User:new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a User entity.
     *
     * @param User $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(User $entity)
    {
        $form = $this->createForm(new UserType(), $entity, array(
            'action' => $this->generateUrl('admin_user_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Сохранить', 'attr' => array(
            'class' => 'btn btn-success'
        )));

        return $form;
    }

    /**
     * Displays a form to create a new User entity.
     *
     */
    public function newAction()
    {
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Доступ запрещен');
        }
        $entity = new User();
        $form = $this->createCreateForm($entity);

        return $this->render('NovuscomCMFBundle:User:new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a User entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NovuscomCMFBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('NovuscomCMFBundle:User:show.html.twig', array(
            'entity' => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing User entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('NovuscomCMFBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $usr = $this->getUser();
        if ($usr->getId() != $id && !$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Доступ запрещен');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('NovuscomCMFBundle:User:edit.html.twig', array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Creates a form to edit a User entity.
     *
     * @param User $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(User $entity)
    {
        $form = $this->createForm(UserType::class, $entity, array(
            'action' => $this->generateUrl('admin_user_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            $form->remove('enabled');
            $form->remove('roles');
            $form->remove('groups');
            $form->remove('sites');
        }

        $form->add('submit', SubmitType::class, array('label' => 'Сохранить', 'attr' => array(
            'class' => 'btn btn-success'
        )));

        return $form;
    }

    /**
     * Edits an existing User entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NovuscomCMFBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $userManager = $this->container->get('fos_user.user_manager');
            if ($editForm->get('password')->getData()) {

                $entity->setPlainPassword($editForm->get('password')->getData());

            }

            $userManager->updateUser($entity);
            //$userManager->refreshUser($entity);
            return $this->redirect($this->generateUrl('admin_user_edit', array('id' => $id)));
        }

        return $this->render('NovuscomCMFBundle:User:edit.html.twig', array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a User entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NovuscomCMFBundle:User')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find User entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_user'));
    }

    /**
     * Creates a form to delete a User entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_user_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', SubmitType::class, array('label' => 'Удалить', 'attr' => array(
                'class' => 'btn btn-danger'
            )))
            ->getForm();
    }
}
