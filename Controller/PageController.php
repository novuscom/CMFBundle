<?php

namespace Novuscom\CMFBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Novuscom\CMFBundle\Entity\Page;
use Novuscom\CMFBundle\Form\PageType;

/**
 * Page controller.
 *
 */
class PageController extends Controller
{


    private function prepareUrl($url, $parent)
    {
        $result = $url;
        if ($url != '/') {
            $result = str_replace(array('/', '\\'), array('-', '-'), $url);
            $result = strtolower($result);
        }


        //$em = $this->getDoctrine()->getManager();
        if ($parent) {
            $parentLevel = $parent->getLvl();
            $result = $parent->getUrl() . $result . '/';
        }

        //print_r($result);
        //exit;
        //$parent = $em->getRepository('NovuscomCMFBundle:Page')->find($parentId);
        //print_r($parent->getUrl()); exit;
        return $result;
    }


    /**
     * Lists all Page entities.
     *
     */
    public function indexAction($site_id)
    {


        $em = $this->getDoctrine()->getManager();
        $site = $em->getRepository('NovuscomCMFBundle:Site')->find($site_id);
        if (!$site) {
            throw $this->createNotFoundException('Unable to find Site entity.');
        }

        $entities = $em->getRepository('NovuscomCMFBundle:Page')->findBy(array('site' => $site), array('lft' => 'ASC'));


        return $this->render('NovuscomCMFBundle:Page:index.html.twig', array(
            'entities' => $entities,
            'site' => $site
        ));
    }

    /**
     * Creates a new Page entity.
     *
     */
    public function createAction(Request $request)
    {
        //print_r('createAction'); exit;
        $page = new Page();
        $siteId = $this->getRequest()->get('site_id');
        //print_r($siteId); exit;
        $form = $this->createCreateForm($page, $siteId);
        $form->handleRequest($request);

        //print_r($page->getParent()->getId()); exit;


        if ($form->isValid()) {
            if ($page->getUrl()) {
                //$url = $this->prepareUrl($page->getUrl(), $page->getParent());
            } else {
                $url = '/';
                $page->setUrl($url);
            }
            $em = $this->getDoctrine()->getManager();

            //print_r($siteId); exit;

            //print_r('форма прошла проверку');
            //print_r('['.$page->getSiteId().']');

            $site = $em->getRepository('NovuscomCMFBundle:Site')->find($siteId);

            //print_r($site->getName());

            $page->setSite($site);


            //print_r($page->getSite()->getId());

            //exit;


            $em->persist($page);
            $em->flush();

            return $this->redirect($this->generateUrl('cmf_admin_site_pages',
                array(
                    'id' => $page->getId(),
                    'site_id' => $siteId,
                )
            ));
        }

        return $this->render('NovuscomCMFBundle:Page:new.html.twig', array(
            'entity' => $page,
            'form' => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a Page entity.
     *
     * @param Page $entity The entity
     * @param $siteId Site id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Page $entity, $siteId)
    {

        $showParent = true;
        $showURL = true;
        $choices = false;
        $em = $this->getDoctrine()->getManager();

        //$siteId = $entity->getSiteId();
        //print_r($siteId);
        //$entities = $em->getRepository('NovuscomCMFBundle:Page')->findAll();
        $entities = $em->getRepository('NovuscomCMFBundle:Page')->findBy(
            array(
                'site' => $siteId
            )
        );
        $count = count($entities);
        //print_r($count);

        if ($count < 1) {
            $showParent = false;
            $showURL = false;
        } else {
            $choices = array();
            foreach ($entities as $e) {
                //print_r($e->getName());
                $pageId = $e->getId();
                $choices[$pageId] = $e->getName();
            }
        }

        $form = $this->createForm(new PageType(), $entity, array(
            'action' => $this->generateUrl(
                'cmf_admin_page_create',
                array('site_id' => $siteId)
            ),
            'method' => 'POST',
            'SHOW_PARENT' => $showParent,
            'SHOW_URL' => $showURL,
            'SITE_ID' => $siteId,
            'CHOICES' => $this->getPagesList(),
        ));

        $form->add('submit', 'submit', array('label' => 'Создать'));

        return $form;
    }

    private function getPagesList()
    {
        $choices = array();
        $entities = $this->getDoctrine()->getRepository('NovuscomCMFBundle:Page')->findBy(array(), array('lft' => 'ASC'));
        foreach ($entities as $child) {
            $choices[$child->getId()] = $child->getName();
        }
        return $choices;
    }

    /**
     * Displays a form to create a new Page entity.
     *
     */
    public function newAction($site_id)
    {
        //print_r($site_id); exit;
        $entity = new Page();
        $form = $this->createCreateForm($entity, $site_id);

        return $this->render('NovuscomCMFBundle:Page:new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Page entity.
     *
     */
    public function showAction($id)
    {
        //print_r('showAction'); exit;
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NovuscomCMFBundle:Page')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Page entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('NovuscomCMFBundle:Page:show.html.twig', array(
            'entity' => $entity,
            'delete_form' => $deleteForm->createView(),));
    }

    /**
     * Displays a form to edit an existing Page entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NovuscomCMFBundle:Page')->find($id);


        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Page entity.');
        }


        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('NovuscomCMFBundle:Page:edit.html.twig', array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Creates a form to edit a Page entity.
     *
     * @param Page $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Page $entity)
    {
        $showParent = false;
        $showURL = true;

        if ($entity->getLvl() > 0) {
            $showParent = true;

        } else {
            $showURL = false;
        }
        $siteId = $this->getRequest()->get('site_id');
        $form = $this->createForm(new PageType(), $entity, array(
            'action' => $this->generateUrl('cmf_admin_page_update',
                array(
                    'id' => $entity->getId(),
                    'site_id' => $siteId
                )
            ),
            'method' => 'PUT',
            'SHOW_PARENT' => $showParent,
            'SHOW_URL' => $showURL,
            'SITE_ID' => $siteId,
        ));

        $form->add('submit', 'submit', array('label' => 'Сохранить', 'attr' => array('class' => 'btn btn-success')));

        return $form;
    }

    private function clearCache($page_id)
    {
        $cacheDriver = new \Doctrine\Common\Cache\ApcCache();
        $cacheDriver->delete('page_' . $page_id);
    }

    /**
     * Edits an existing Page entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NovuscomCMFBundle:Page')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Page entity.');
        }


        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {

            /*if ($entity->getUrl()) {
                $url = $this->prepareUrl($entity->getUrl(), $entity->getParent());
                $entity->setUrl($url);
            }*/
            $this->clearCache($id);

            $em->flush();
            $siteId = $this->getRequest()->get('site_id');
            return $this->redirect($this->generateUrl('cmf_admin_page_edit',
                array(
                    'id' => $id,
                    'site_id' => $siteId
                )
            )
            );
        }

        return $this->render('NovuscomCMFBundle:Page:edit.html.twig', array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Page entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('NovuscomCMFBundle:Page')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Page entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('cmf_admin_site_pages', array('site_id' => $entity->getSite()->getId())));
    }

    /**
     * Creates a form to delete a Page entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        $siteId = $this->getRequest()->get('site_id');
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('cmf_admin_page_delete',
                array(
                    'id' => $id,
                    'site_id' => $siteId
                )
            )
            )
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Удалить', 'attr' => array('class' => 'btn btn-danger')))
            ->getForm();
    }
}
