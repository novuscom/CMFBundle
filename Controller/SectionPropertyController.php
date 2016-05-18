<?php

namespace Novuscom\CMFBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Novuscom\CMFBundle\Entity\SectionProperty;
use Novuscom\CMFBundle\Form\SectionPropertyType;

/**
 * SectionProperty controller.
 *
 */
class SectionPropertyController extends Controller
{
    /**
     * Lists all SectionProperty entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $sectionProperties = $em->getRepository('NovuscomCMFBundle:SectionProperty')->findAll();

        return $this->render('sectionproperty/index.html.twig', array(
            'sectionProperties' => $sectionProperties,
        ));
    }

    /**
     * Creates a new SectionProperty entity.
     *
     */
    public function newAction(Request $request)
    {
        $sectionProperty = new SectionProperty();
        $form = $this->createForm('Novuscom\CMFBundle\Form\SectionPropertyType', $sectionProperty);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($sectionProperty);
            $em->flush();

            return $this->redirectToRoute('admin_sectionproperty_show', array('id' => $sectionProperty->getId()));
        }

        return $this->render('sectionproperty/new.html.twig', array(
            'sectionProperty' => $sectionProperty,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a SectionProperty entity.
     *
     */
    public function showAction(SectionProperty $sectionProperty)
    {
        $deleteForm = $this->createDeleteForm($sectionProperty);

        return $this->render('sectionproperty/show.html.twig', array(
            'sectionProperty' => $sectionProperty,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing SectionProperty entity.
     *
     */
    public function editAction(Request $request, SectionProperty $sectionProperty)
    {
        $deleteForm = $this->createDeleteForm($sectionProperty);
        $editForm = $this->createForm('Novuscom\CMFBundle\Form\SectionPropertyType', $sectionProperty);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($sectionProperty);
            $em->flush();

            return $this->redirectToRoute('admin_sectionproperty_edit', array('id' => $sectionProperty->getId()));
        }

        return $this->render('sectionproperty/edit.html.twig', array(
            'sectionProperty' => $sectionProperty,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a SectionProperty entity.
     *
     */
    public function deleteAction(Request $request, SectionProperty $sectionProperty)
    {
        $form = $this->createDeleteForm($sectionProperty);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($sectionProperty);
            $em->flush();
        }

        return $this->redirectToRoute('admin_sectionproperty_index');
    }

    /**
     * Creates a form to delete a SectionProperty entity.
     *
     * @param SectionProperty $sectionProperty The SectionProperty entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(SectionProperty $sectionProperty)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_sectionproperty_delete', array('id' => $sectionProperty->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
