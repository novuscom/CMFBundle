<?php

namespace Novuscom\CMFBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use \Novuscom\CMFBundle\Services\Site;
use Monolog\Logger;

class Page
{

    private $site;

    private function getSite(){
        if (!$result = $this->site)
            $result = $this->Site->getCurrentSite();
        return $result;
    }

    public function findPage($name)
    {
        $page = false;
        $em = $this->entityManager;
        $er = $em->getRepository('NovuscomCMFBundle:Page');
        $site = $this->getSite();

        if ($name) {
            $codeArray = explode('/', preg_replace('/^\/?(.+?)\/?$/', '\\1', $name));
            $maxLevel = count($codeArray);
            /*if (preg_match('/^(\/)(.+?)(\/)$/', $name)) {
                //throw $this->createNotFoundException('Слэш в начале');
                $this->setExceptionText('Слэш в начале');
            };*/

            $root = $er->findOneBy(array(
                'site' => $site['id'],
                'lvl' => 0
            ));
            if (!$root) {
                //throw $this->createNotFoundException('Страница не найдена');
                //$this->setExceptionText('Страница не найдена');
            }
            if ($maxLevel > 0) {
                $pages_by_last_code = $er->createQueryBuilder('p')
                    ->where("p.site=:site")
                    ->andWhere("p.lft>:left")
                    ->andWhere("p.rgt<:right")
                    ->andWhere("p.url=:url")
                    ->setParameters(array(
                        'site' => $em->getReference('Novuscom\CMFBundle\Entity\Site', $site['id']),
                        'left' => $root->getLft(),
                        'right' => $root->getRgt(),
                        'url' => $codeArray[$maxLevel - 1]
                    ))
                    ->orderBy('p.lft', 'ASC')
                    ->getQuery()
                    ->getResult();
                $pages_by_id = array();
                $paths = array();
                $paths_array = array();
                foreach ($pages_by_last_code as $p) {
                    $pages_by_id[$p->getId()] = $p;
                    $path = $er->getPath($p);
                    $path_id = array();
                    foreach ($path as $pa) {
                        $path_id[] = $pa->getUrl();
                        $paths_array[$p->getId()][] = $pa;
                    }
                    $paths[$p->getId()] = $path_id;
                }
                $page_id = false;
                foreach ($paths as $id => $array) {
                    array_shift($array);
                    if ($array == $codeArray) {
                        $page_id = $id;
                        break;
                    }
                }
                if (!$page_id) {
                    //throw $this->createNotFoundException('Страница не найдена (путь не найден)');
                    //$this->setExceptionText('Страница не найдена (путь не найден)');
                } else
                    $page = $pages_by_id[$page_id];
                

            } else {
                $page = $root;
            }
        } else {
            $page = $em->getRepository('NovuscomCMFBundle:Page')->findOneBy(array(
                'site' => $site,
                'lvl' => 0
            ));
        }
        return $page;
    }

    private $entityManager;
    private $Site;
    private $logger;

    public function __construct(EntityManager $entityManager, Logger $logger, Site $site)
    {
        $this->entityManager = $entityManager;
        $this->Site = $site;
        $this->logger = $logger;
    }
}