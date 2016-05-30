<?php

namespace Novuscom\CMFBundle\Controller;

use Novuscom\CMFBundle\Services\Utils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Novuscom\CMFBundle\Entity\Property;
use Novuscom\CMFBundle\Form\PropertyType;

/**
 * Property controller.
 *
 */
class PropertyController extends Controller
{
    /**
     * Lists all Property entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $properties = $em->getRepository('NovuscomCMFBundle:Property')->findAll();

        return $this->render('property/index.html.twig', array(
            'properties' => $properties,
        ));
    }

    /**
     * Creates a new Property entity.
     *
     */
    public function newAction(Request $request)
    {
        $property = new Property();
        $form = $this->createForm('Novuscom\CMFBundle\Form\PropertyType', $property, array(
	        'STANDALONE'=>true
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($property);
            $em->flush();

            return $this->redirectToRoute('admin_property_show', array('id' => $property->getId()));
        }

        return $this->render('property/new.html.twig', array(
            'property' => $property,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Property entity.
     *
     */
    public function showAction(Property $property)
    {
        $deleteForm = $this->createDeleteForm($property);

        return $this->render('property/show.html.twig', array(
            'property' => $property,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Property entity.
     *
     */
    public function editAction(Request $request, Property $property)
    {
        $deleteForm = $this->createDeleteForm($property);
        $editForm = $this->createForm('Novuscom\CMFBundle\Form\PropertyType', $property);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
	        if ($property->getIsForSection()==0) {
		        $property->setIsForSection(null);
	        }
            $em = $this->getDoctrine()->getManager();
            $em->persist($property);
            $em->flush();

            return $this->redirectToRoute('admin_property_edit', array('id' => $property->getId()));
        }

        return $this->render('property/edit.html.twig', array(
            'property' => $property,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Property entity.
     *
     */
    public function deleteAction(Request $request, Property $property)
    {
        $form = $this->createDeleteForm($property);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($property);
            $em->flush();
        }

        return $this->redirectToRoute('admin_property_index');
    }

    /**
     * Creates a form to delete a Property entity.
     *
     * @param Property $property The Property entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Property $property)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_property_delete', array('id' => $property->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
