<?php

namespace Novuscom\CMFBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\DependencyInjection\ContainerInterface;

class User
{

    public function isAuthorized()
    {
        // IS_AUTHENTICATED_REMEMBERED
<<<<<<< HEAD
        return ($this->container->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY'));
=======
        return ($this->container->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY'));
>>>>>>> 6b120d6339f9c8f270f714255a66ce26fbe4eb5c
    }

    public function getUserSites($user = false)
    {
        if (!$user) {
            $user = $this->getCurrentUser();
        }
        $em = $this->container->get('doctrine.orm.entity_manager');

        $repo = $em->createQueryBuilder('n');
        $repo->from('NovuscomCMFBundle:Site', 'n', 'n.id');
        $repo->select(array('n.name, n.id'));

<<<<<<< HEAD
        if ($this->container->get('security.context')->isGranted('ROLE_ADMIN')) {
=======
        if ($this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
>>>>>>> 6b120d6339f9c8f270f714255a66ce26fbe4eb5c

        } else {
            if ($sitesId = $user->getSitesId()) {
                $repo->where('n.id IN (:sites_id)');
                $repo->setParameter('sites_id', $sitesId);
            }
        }
        $query = $repo->getQuery();
        $sql = $query->getSql();
        $sites = $query->getResult();
        return $sites;
    }

    public function getCurrentUser(){
<<<<<<< HEAD
        $user = $this->container->get('security.context')
=======
        $user = $this->container->get('security.token_storage')
>>>>>>> 6b120d6339f9c8f270f714255a66ce26fbe4eb5c
            ->getToken()
            ->getUser();
        return $user;
    }

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
}