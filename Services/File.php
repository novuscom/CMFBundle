<?php

namespace Novuscom\CMFBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\DependencyInjection\ContainerInterface;

class File
{

	public function uploadFile(\Novuscom\CMFBundle\Entity\File $entity, $path = false)
	{
		if (!$path)
			$path = $this->container->getParameter('upload_directory');
		$file = $entity->getFile();
		$file->move(
			$path,
			$entity->getName()
		);
	}

	public function ResizeImage($path, $filter, $size)
	{
		$path = trim($path, '/');
		$cacheManager = $this->container->get('liip_imagine.cache.manager');
		$dataManager = $this->container->get('liip_imagine.data.manager');
		$filterManager = $this->container->get('liip_imagine.filter.manager');
		$id = substr(md5(json_encode(array($path, $filter, $size))), 0, 10);
		$newPath = 'o/' . $id . '/' . $path;
		if (!$cacheManager->isStored($newPath, $filter)) {
			$binary = $dataManager->find($filter, $path);
			$config = array(
				'thumbnail' => array(
					'size' => array($size[0], $size[1])
				)
			);
			$filteredBinary = $filterManager->applyFilter($binary, $filter, array(
				'filters' => $config
			));

			$cacheManager->store($filteredBinary, $newPath, $filter);
		}
		$resolve = $cacheManager->resolve($newPath, $filter);
		return array(
			'src' => $resolve
		);
	}

	private $container;

	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}
}