<?php

namespace Novuscom\CMFBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Monolog\Logger;


class Section
{

    public function ElementsSections($elementsID){
        $repo = $this->em->getRepository('NovuscomCMFBundle:ElementSection');
		$refs = array();
		foreach ($elementsID as $e) {
			$refs[] = $this->em->getReference('Novuscom\CMFBundle\Entity\Element', $e);
		}
        $rows = $repo->findBy(array('element'=>$refs));
		$result = array();
		foreach ($rows as $r) {
			$result[$r->getElement()->getId()] = $r->getSection();
		}
        return $result;
    }

    public function getChildren($section){
        $repo = $this->em->getRepository('NovuscomCMFBundle:Section');
        $path = $repo->getChildren($section);
        return $path;
    }

    public function getPath($section){
        $repo = $this->em->getRepository('NovuscomCMFBundle:Section');
        $path = $repo->getPath($section);
        return $path;
    }

    public function clearCacheSection($block_id, $section_id)
    {
        $fullCode = $this->getFullCode($section_id);
        $cacheDriver = new \Doctrine\Common\Cache\ApcCache();
        $nameSpace = 'SectionAction_dev_'.$block_id;
        $cacheDriver->setNamespace($nameSpace);
        $cacheDriver->delete($fullCode);
        $nameSpace = 'SectionAction_prod_'.$block_id;
        $cacheDriver->setNamespace($nameSpace);
        $cacheDriver->delete($fullCode);
    }

    public function getFullCode($id){
        $this->logger->info('Section->getFullCode('.print_r($id, true).')');
        $repo = $this->em->getRepository('NovuscomCMFBundle:Section');
        $fullCode = false;
        if (is_numeric($id)) {
            $codes = array();
            $section = $repo->find($id);
            $path = $this->getPath($section);
            foreach ($path as $s) {
                $codes[] = $s->getCode();
            }
            $fullCode = implode('/', $codes);
        }
        if (is_array($id)) {
            $id = array_unique($id);
            $fullCode = array();
            $sections = $repo->findBy(array('id'=>$id));
            foreach ($sections as $s) {
                $path = $this->getPath($s);
                $codes = array();
                foreach ($path as $p) {
                    $codes[] = $p->getCode();
                }
                $full = implode('/', $codes);
                $fullCode[$s->getId()] = $full;
            }
        }
        return $fullCode;
    }

    private $logger;
    private $em;
    public function __construct(Logger $logger, EntityManager $em){
        $this->logger = $logger;
        $this->em = $em;
    }
}