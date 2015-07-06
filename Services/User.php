<?php

namespace Novuscom\CMFBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\DependencyInjection\ContainerInterface;

class User
{
    public function getUserSites($user = false){
        if (!$user) {
            $user = $this->container->get('security.token_storage')->getToken()->getUser();
        }
        $em = $this->container->get('doctrine.orm.entity_manager');

        $repo = $em->createQueryBuilder('n');
        $repo->from('NovuscomCMFBundle:Site', 'n', 'n.id');
        $repo->select(array('n.name, n.id'));

        if ($this->container->get('security.context')->isGranted('ROLE_ADMIN')){

        }
        else {
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

    private $container;

    public function __construct(ContainerInterface $container){
        $this->container = $container;
    }
}