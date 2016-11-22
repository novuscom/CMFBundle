<?php

namespace Novuscom\CMFBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Monolog\Logger;


class Section
{

	public function setSectionsPictures($sections)
	{
		$em = $this->em;
		$files_id = array();
		foreach ($sections as $e) {
			$files_id[] = $e['preview_picture'];
		}
		$files_id = array_filter(array_unique($files_id));
		if ($files_id) {
			$repo = $em->createQueryBuilder('n');
			$repo = $repo->from('NovuscomCMFBundle:File', 'n', 'n.id');
			$repo = $repo->select('n.id, n.name, n.size, n.description, n.type');
			$repo = $repo->andWhere('n.id IN(:files_id)');
			$repo = $repo->setParameter('files_id', $files_id);
			$repo = $repo->getQuery();
			$sql = $repo->getSql();
			$preview_pictures = $repo->getResult();
		}
		foreach ($sections as $key => $e) {
			if ($e['preview_picture'] && array_key_exists($e['preview_picture'], $preview_pictures)) {
				$array = $preview_pictures[$e['preview_picture']];
				$array['src'] = 'upload/etc/' . $array['name'];
				$array['path'] = $array['src'];
				$sections[$key]['preview_picture'] = $array;
			}
		}
		return $sections;
	}

    public function ElementsSections($elementsID){
        $repo = $this->em->getRepository('NovuscomCMFBundle:ElementSection');
		$refs = array();
		foreach ($elementsID as $e) {
			$refs[] = $this->em->getReference('Novuscom\CMFBundle\Entity\Block', $e);
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
        $cacheDriver = new \Doctrine\Common\Cache\ApcuCache();
        $nameSpace = 'SectionAction_dev_'.$block_id;
        $cacheDriver->setNamespace($nameSpace);
        $cacheDriver->delete($fullCode);
        $nameSpace = 'SectionAction_prod_'.$block_id;
        $cacheDriver->setNamespace($nameSpace);
        $cacheDriver->delete($fullCode);
    }

	public function getCodeString($section){
		$codes = array();
		$path = $this->getPath($section);
		foreach ($path as $s) {
			$codes[] = $s->getCode();
		}
		$fullCode = implode('/', $codes);
		return $fullCode;
	}

    public function getFullCode($id){
        $repo = $this->em->getRepository('NovuscomCMFBundle:Section');
        $fullCode = false;
        if (is_numeric($id)) {
            $section = $repo->find($id);
            $fullCode = $this->getCodeString($section);
        }
        if (is_array($id)) {
            $id = array_unique($id);
            $fullCode = array();
            $sections = $repo->findBy(array('id'=>$id));
            foreach ($sections as $s) {
                //$path = $this->getPath($s);
	            $path = array();
                $codes = array();
                foreach ($path as $p) {
                    $codes[] = $p->getCode();
                }
                $full = implode('/', $codes);
                $fullCode[$s->getId()] = $full;
            }
        }
        if (is_object($id)) {
	        //echo '<pre>'.print_r('is_object', true).'</pre>';
	        $fullCode = $this->getCodeString($id);
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