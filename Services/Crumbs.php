<?php

namespace Novuscom\CMFBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;

class Crumbs
{

    public function getCrumbs($page_id)
    {
        $em = $this->em;
        $crumbs = $this->apy_breadcrumb_trail;
        $repo = $em->getRepository('NovuscomCMFBundle:Page');
        $page = $repo->find($page_id);
        $path = $repo->getPath($page);
        $codes_array = array();
        foreach ($path as $p) {
            if ($p->getLvl() == 0) {
                $crumbs->add($p->getName(), 'cmf_page_main');
            } else {
                $codes_array[] = $p->getUrl();
                $crumbs->add($p->getName(), 'cmf_page_frontend', array('name' => implode('/', $codes_array)));
            }
        }
        return $crumbs;
    }


    private $em;
    private $logger;
    private $apy_breadcrumb_trail;

    public function __construct(\Doctrine\ORM\EntityManager $em, $logger, $apyCrumbs)
    {
        $this->em = $em;
        $this->logger = $logger;
        $this->apy_breadcrumb_trail = $apyCrumbs;
    }
}