<?php

namespace Novuscom\CMFBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Monolog\Logger;

class Site
{

    public function clearCache($host = false)
    {
        if ($host == false)
            $host = $this->getHost();
        $this->getCacheDriver()->delete($host);
    }

    public function getSiteByID($siteId)
    {
        $repo = $this->entityManager->createQueryBuilder('n');
        $repo->from('NovuscomCMFBundle:Site', 'n');
        $repo->select('n.id, n.name, n.closed, n.code, n.robots_txt, n.emails');
        $repo->where('n.id = :id');
        $repo->setParameter('id', $siteId);
        $repo->setMaxResults(1);
        $query = $repo->getQuery();
        $sql = $query->getSql();
        $result = $query->getResult();
        return $result[0];
    }

    /**
     * Возвращает массив с ифнормацией о текущем сайте
     * @return array
     */
    public function getCurrentSite()
    {
        $cacheId = $this->getHost();
        $cacheDriver = $this->getCacheDriver();
        if ($fooString = $cacheDriver->fetch($cacheId)) {
            $this->logger->info('Сайт найден в кеше по id ' . $cacheId);
            $result = unserialize($fooString);
        } else {
            $this->logger->info('Сайт НЕ найден в кеше ' . $cacheId);
            $result = $this->getSite();
            $cacheDriver->save($cacheId, serialize($result));
        }
        return $result;
    }

    private $site;
    private $alias;

    private function getSite()
    {
        if (empty($this->site)) {
            $this->setSite();
        }
        return $this->site;
    }

    public function getAlias()
    {
        if (empty($this->alias)) {
            $this->setAlias();
        }
        return $this->alias;
    }

    private function setSite()
    {
        $site = false;
        $alias = $this->getAlias();
        if (!$alias)
            $this->logger->alert('Не найден alias по хосту ' . $this->getHost());
        else {
            $site = $this->getSiteByID($alias['site_id']);
            $this->logger->info('Сайт: ' . print_r($site, true));
        }

        $this->site = $site;
    }


    public function getHost()
    {
        return $this->host;
    }

    private function setAlias()
    {
        $result = false;
        $host = $this->getHost();
        $repo = $this->entityManager->createQueryBuilder('n');
        $repo->from('NovuscomCMFBundle:Alias', 'n');
        $repo->select('n.id, n.name, IDENTITY(n.site) as site_id');
        $repo->where('n.name = :name');
        $repo->setParameter('name', $host);
        $repo->setMaxResults(1);
        $query = $repo->getQuery();
        $sql = $query->getSql();
        if ($qr = $query->getResult()) {
            $result = $qr[0];
        }

        return $this->alias = $result;
    }

    private $requestStack;
    private $container;
    private $logger;
    private $host;
    private $entityManager;

    private function getCacheDriver()
    {
        $env = $this->container->get('kernel')->getEnvironment();
        //$cacheDriver = new \Doctrine\Common\Cache\FilesystemCache($_SERVER['DOCUMENT_ROOT'] . '/../app/cache/' . $env . '/sys/Sites/');
        $cacheDriver = new \Doctrine\Common\Cache\ApcCache();
        //$cacheDriver->setNamespace('Pages_' . $env);
        return $cacheDriver;
    }

    public function __construct(ContainerInterface $container, Logger $logger, RequestStack $requestStack, EntityManager $entityManager)
    {
        $this->container = $container;
        $this->logger = $logger;
        $this->requestStack = $requestStack;
        $this->host = $this->requestStack->getMasterRequest()->getHost();
        $this->entityManager = $entityManager;
    }
}