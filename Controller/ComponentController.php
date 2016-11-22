<?php

namespace Novuscom\CMFBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Event\UserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Util\TokenGenerator;
use Knp\Menu\MenuFactory;
use Knp\Menu\Renderer\ListRenderer;
use LSS\Array2XML;
use Novuscom\CMFBundle\Entity\Block;
use Novuscom\CMFBundle\Entity\Order;
use Novuscom\CMFBundle\Entity\Product;
use Novuscom\CMFBundle\Entity\SearchQuery;
use Novuscom\CMFBundle\Entity\Site;
use Novuscom\CMFBundle\Entity\SiteBlock;
use Novuscom\CMFBundle\Event\UserEvent as CMFUserEvent;
use Novuscom\CMFBundle\Event\UserSubscriber;
use Novuscom\CMFBundle\Form\BlockType;
use Novuscom\CMFBundle\Form\LoginType;
use Novuscom\CMFBundle\Form\OrderType;
use Novuscom\CMFBundle\Form\RegisterType;
use Novuscom\CMFBundle\Services\Section as Section;
use Novuscom\CMFBundle\Services\Utils;
use Novuscom\CMFBundle\UserEvents;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Validator\Constraints\DateTime;


/**
 * Block controller.
 *
 */
class ComponentController extends Controller
{

	public function SearchAction($params = false,
	                             Request $request)
	{
		$Route = $this->get('Route');
		$query = trim($request->query->get('q'));
		$Site = $this->get('Site');
		$currentSite = $Site->getCurrentSite();
		$currentAlias = $Site->getAlias();
		$alias = $currentAlias['name'];
		$prefix = 'http://' . $alias;
		$em = $this->getDoctrine()->getManager();
		$result = array();
		$elements = $em->getRepository("NovuscomCMFBundle:Element")->createQueryBuilder('o')
			->where('o.name LIKE :query')
			->andWhere('o.active = 1')
			->setParameter('query', '%' . $query . '%')
			->getQuery()
			->getResult();

		$blocks = array();
		foreach ($elements as $e) {
			$result['e-' . $e->getId()] = array(
				'title' => $e->getName(),
				'type' => 'element',
			);
			$blocks[] = $em->getReference('Novuscom\CMFBundle\Entity\Block', $e->getBlock()->getId());
		}
		$routes = $em->getRepository('NovuscomCMFBundle:Route')->findBy(array(
			'active' => true,
			'block' => $blocks
		));
		foreach ($routes as $r) {
			foreach ($elements as $element) {
				$url = false;
				if ($r->getController() == 'NovuscomCMFBundle:Component:Element')
					$url = $Route->getUrl($r->getCode(), $element);
				if ($url !== false) {
					$url = $prefix . $url;
					$result['e-' . $element->getId()]['url'] = $url;
				}
			}
		}
		$countResults = count($result);
		$queryEntity = $em->getRepository('NovuscomCMFBundle:SearchQuery')->findOneByQuery($query);
		if ($queryEntity) {
			$queryEntity->setQuantity(($queryEntity->getQuantity() + 1));
			$queryEntity->setResults($countResults);
			$queryEntity->setTime(new \DateTime('now'));
		} else {
			$queryEntity = new SearchQuery();
			$queryEntity->setQuantity(1);
			$queryEntity->setQuery($query);
			$queryEntity->setResults($countResults);
			$queryEntity->setTime(new \DateTime('now'));
			$em->persist($queryEntity);
		}
		$em->flush();
		$responseData = array(
			'query' => $query,
			'result' => $result
		);
		$response = $this->render('@templates/' . $currentSite['code'] . '/Search/index.html.twig', $responseData);
		return $response;
	}

	public function SiteMapXMLAction(Request $request)
	{
		$logger = $this->get('logger');

		$result = array();
		$em = $this->getDoctrine()->getManager();
		$routes = $em->getRepository('NovuscomCMFBundle:Route')->findBy(array('active' => true));


		$Route = $this->get('Route');
		$Site = $this->get('Site');
		$currentAlias = $Site->getAlias();
		$alias = $currentAlias['name'];
		$prefix = 'http://' . $alias;

		$needRoutes = array(
			'@attributes' => array(
				'xmlns' => 'http://www.sitemaps.org/schemas/sitemap/0.9'
			),
			'url' => array());
		$urlArray = array();
		/*
		 * Страницы
		 */
		$Site = $this->get('Site');
		$currentSite = $Site->getCurrentSite();
		$siteRef = $em->getReference('Novuscom\CMFBundle\Entity\Site', $currentSite['id']);
		$pagesRepo = $em->getRepository('NovuscomCMFBundle:Page');
		$pages = $pagesRepo->findBy(array('site' => $siteRef));
		foreach ($pages as $p) {
			$codes = array();
			foreach ($pagesRepo->getPath($p) as $path) {
				$codes[] = $path->getUrl();
			}
			$url = implode('/', $codes);
			$url = str_replace('//', '/', $url);
			if ($p->getLvl() > 0) {
				$url = trim($url, '/');
				$url = $this->get('router')->generate('page', array('url' => $url));
			} else {
				$url = $this->get('router')->generate('cmf_page_main');
			}
			$url = $prefix . $url;
			$urlArray[] = $url;
		}
		foreach ($routes as $r) {
			$params = json_decode($r->getParams(), true);
			if ($r->getBlock() === false)
				continue;
			if ($r->getController() == 'NovuscomCMFBundle:Component:Element') {
				foreach ($r->getBlock()->getElement() as $element) {
					if ($element->getActive() === false)
						continue;
					$url = $Route->getUrl($r->getCode(), $element);
					if ($url) {
						$url = $prefix . $url;
						$urlArray[] = $url;
					}

				}
			}
			if ($r->getController() == 'NovuscomCMFBundle:Component:Section') {
				foreach ($r->getBlock()->getSection() as $section) {
					$url = $Route->getUrl($r->getCode(), $section);
					if ($url !== false) {
						$url = $prefix . $url;
						$urlArray[] = $url;
					}
				}
			}
		}
		$urlArray = array_unique($urlArray);
		sort($urlArray);
		foreach ($urlArray as $u) {
			$needRoutes['url'][]['loc'][] = $u;
		}
		$xml = Array2XML::createXML('urlset', $needRoutes)->saveXML();
		$response = new Response();
		$response->headers->set('Content-Type', 'application/xml; charset=UTF-8');
		$response->setContent($xml);
		return $response;
	}

	private function msg($result)
	{
		echo '<pre>' . print_r($result, true) . '</pre>';
	}

	public function RecountCartAction(Request $request)
	{
		$logger = $this->get('logger');
		$PRODUCT_ID = intval($request->get('PRODUCT_ID'));
		$QUANTITY = intval($request->get('QUANTITY'));
		$logger->debug('quantity: ' . $QUANTITY);
		$logger->debug('product id: ' . $PRODUCT_ID);
		if (!$PRODUCT_ID || !$QUANTITY)
			$this->JSON404('Incorrect data');
		$Product = $this->get('Product');
		$product = $Product->getProduct($PRODUCT_ID);
		$product->setQuantity($QUANTITY);
		$this->getDoctrine()->getManager()->flush();
		$cart = $product->getCart();

		$result = array(
			'DATA' => array(
				'CART_TOTAL' => $cart->getTotal(),
				'PRODUCT_SUM' => $product->getSum()
			)
		);

		$response = new Response();
		$response->headers->set('Content-Type', 'application/json; charset=UTF-8');
		$response->setContent(json_encode($result));
		return $response;
	}

	public function OrderAction($params, Request $request)
	{
		$page_class = $this->get('Page');
		$page = $page_class->GetById($params['page_id']);

		$Cart = $this->get('Cart');
		$cart = $Cart->GetCurrent();
		if (!$cart) {
			throw $this->createNotFoundException('Не найдена корзина');
		}

		$user = $this->container->get('security.token_storage')
			->getToken()
			->getUser();

		if (!$user)
			throw $this->createNotFoundException('Не найден пользователь');
		$em = $this->getDoctrine()->getManager();
		$routeName = $request->get('_route');


		$form = $this->createForm(OrderType::class);
		if ($request->getMethod() == 'POST') {
			$order = new Order();
			//$order->setUser($user);
			$order->setCreated(new \DateTime('now'));
			$form->handleRequest($request);
			$data = $form->getData();
			$order->setName($data['name']);
			$order->setAddress($data['address']);
			$order->setPhone($data['phone']);
			$em->persist($order);


			$Site = $this->get('Site');
			$site = $Site->getCurrentSite();

			foreach ($cart->getProduct() as $product) {
				$product->setOrder($order);
				$product->setCart(null);
				//echo '<pre>' . print_r($product->getName(), true) . '</pre>';
				$em->persist($product);
			}
			$em->remove($cart);
			$Cart->removeCurrentCart();
			$em->flush();

			$this->get('session')->getFlashBag()->add(
				'ok',
				'Ваш заказ оформлен'
			);
			$url = $request->getSchemeAndHttpHost() . $this->get('router')->generate('admin_order_show', array('id' => $order->getId()));
			$message = \Swift_Message::newInstance()
				->setSubject('Новый заказ на сайте')
				->setFrom('info@novuscom.ru')
				->setTo($site['emails'])
				->setBody(
					$this->get('templating')->render(
						'NovuscomCMFBundle:Email:NewOrder.html.twig',
						array(
							'id' => $order->getId(),
							'url' => $url,
						)
					),
					'text/html'
				);

			$this->get('mailer')->send($message);
			$response = new RedirectResponse($this->generateUrl($routeName));
			$response->headers->clearCookie('cart');
			return $response;
		}
		$responseData = array(
			'page' => $page,
		);
		$response = $this->render('@templates/' . $params['params']['template_directory'] . '/Shop/' . $params['template_code'] . '.html.twig', $responseData);
		return $response;
	}

	private function JSON404($message)
	{
		$result = array(
			'STATUS' => false,
			'MESSAGE' => $message,
		);
		$response = new Response();
		$response->setStatusCode(404);
		$response->setContent(json_encode($result));
		return $response;
	}

	public function DeleteProductAction($params = array(), Request $request)
	{
		$result = array(
			'STATUS' => false,
			'MESSAGE' => 'Unknown error',
		);

		$productId = $request->get('PRODUCT_ID');
		if (!is_numeric($productId)) {
			return $this->JSON404('Not product id');
		}
		$response = new Response();
		$Product = $this->get('Product');
		$product = $Product->getProduct($productId);
		if (count($product) != 1) {
			return $this->JSON404('Product not found');
		}
		$result['MESSAGE'] = 'Product deleted ' . $product->getName();
		$Product->removeEntity($product);
		$result['STATUS'] = true;
		$cart = $Product->getCart();
		$result['DATA'] = array(
			'CART' => array(
				'PRODUCTS' => $cart->getProductsCount(),
				'ELEMENTS_COUNT' => $cart->getElementsCount(),
				'TOTAL' => $cart->getTotal(),
			)
		);
		$response->headers->set('Content-Type', 'application/json; charset=UTF-8');
		$response->setContent(json_encode($result));
		return $response;
	}

	public function SmallCartAction($params = array(), Request $request)
	{
		$Site = $this->get('Site');
		$currentSite = $Site->getCurrentSite();
		$Cart = $this->get('Cart');
		$cart = $Cart->GetCurrent();
		$responseData = array(
			'cart' => $cart,
		);
		$response = $this->render('@templates/' . $currentSite['code'] . '/Shop/smallCart.html.twig', $responseData);
		return $response;
	}

	public function CartAction($params, Request $request)
	{
		$page_class = $this->get('Page');
		$page = $page_class->GetById($params['page_id']);
		$Cart = $this->get('Cart');
		$cart = $Cart->GetCurrent();

		$form = $this->createForm(OrderType::class, null, array(
			'action' => $this->generateUrl('order'),
			'method' => 'POST',
			'attr' => array('class' => ''),
		));
		$formView = $form->createView();

		$responseData = array(
			'page' => $page,
			'cart' => $cart,
			'form' => $formView,
		);
		$response = $this->render('@templates/' . $params['params']['template_directory'] . '/Shop/' . $params['template_code'] . '.html.twig', $responseData);
		return $response;
	}


	private function JsonResponse($array){
		$response = new JsonResponse();
		$response->setData($array);
		$response->setStatusCode(404);
		return $response;
	}

	public function AddToCartJSONAction(Request $request, Response $response)
	{
		$productRequest = $request->get('product');
		$result = array(
			'STATUS' => false,
			'DATA' => null,
			'MESSAGE' => 'Unknown error',
		);
		$response = new Response();
		$response->headers->set('Content-Type', 'application/json; charset=UTF-8');
		if ($request->isXmlHttpRequest() !== true) {
			$result['MESSAGE'] = 'Not ajax';
			return $this->JsonResponse($result);
		}
		$Element = $this->get('Element');
		$element = $Element->getById($productRequest['element_id']);
		if ($element === false) {
			$result['MESSAGE'] = 'The element to add to the cart was not found (' . $productRequest['element_id'] . ')';
			return $this->JsonResponse($result);
		}

		$user_id = null;
		$securityContext = $this->container->get('security.authorization_checker');
		if ($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
			$user = $this->container->get('security.token_storage')
				->getToken()
				->getUser();
			$user_id = $user->getId();
		}

		$Cart = $this->get('Cart');
		$cart = $Cart->GetCurrent();
		$createdTime = $cart->getCreated();


		if (array_key_exists('weight', $productRequest) == false)
			$productRequest['weight'] = 0;

		$Product = $this->get('Product');
		$product = $Product->IfStoredInCart(
			$element->getId(),
			$cart->getId()
		);
		if (array_key_exists('quantity', $productRequest) === false || $productRequest['quantity'] < 1 || is_numeric($productRequest['quantity']) === false)
			$productRequest['quantity'] = 1;
		if ($product == false) {
			$product = new Product();
			$product->setName($productRequest['name']);
			$product->setUrl($productRequest['url']);
			$product->setQuantity($productRequest['quantity']);
			$product->setRoute('false');
			$product->setPrice($productRequest['price']);
			$product->setCart($cart);
			$product->setElement($element);
			$product->setWeight($productRequest['weight']);
			$product->setCreated($createdTime);
		} else {
			$product->setQuantity($product->getQuantity() + $productRequest['quantity']);
		}


		$em = $this->getDoctrine()->getManager();
		$em->persist($cart);
		$em->persist($product);
		$em->flush();

		$result['STATUS'] = true;
		$result['MESSAGE'] = 'Product added to cart';
		$result['DATA'] = array(
			'ID' => $product->getId(),
			'PRODUCT_QUANTITY' => $product->getQuantity(),
			'CART_PRODUCTS' => $cart->getProductsCount(),
			'CART_ELEMENTS_COUNT' => $cart->getElementsCount(),
			'CART_TOTAL' => $cart->getTotal(),
			'CART_ID' => $product->getCart()->getId(),
			'CART_CODE' => $product->getCart()->getCode(),
			'ELEMENT_ID' => $product->getElement()->getId(),
			//'USER_ID' => $product->getCart()->getUser()->getId(),
		);

		$cookie = new Cookie('cart', $cart->getCode(), date("Y-m-d", strtotime(date("Y-m-d") . " + 1 year")));
		$response->setContent(json_encode($result));
		$response->headers->setCookie($cookie);
		return $response;
	}

	public function LoginAction($params, Request $request)
	{
		$page_class = $this->get('Page');
		$page = $page_class->GetById($params['page_id']);
		$form = $this->createForm(new LoginType(), null, array(
			'action' => $this->generateUrl('cmf_page_frontend', array('name' => $page->getUrl())),
			'method' => 'POST',
			'attr' => array('class' => 'login-form'),
		));
		$form->handleRequest($request);
		if ($form->isValid()) {
			$userManager = $this->container->get('fos_user.user_manager');
			$user = $userManager->findUserByUsername($form->get('username')->getData());
			$count_by_username = count($user);
			if (!$count_by_username) {
				$form->addError(new FormError('Пользователя с таким именем не существует'));
			} else {
				$enabled = $user->isEnabled();
				if (!$enabled)
					$form->addError(new FormError('Учетная запись не активирована. Подтвердите регистрацию по электронной почте ' . $user->getEmailCanonical()));
			}
			if (count($form->getErrors()) == 0) {
				/**
				 * Аутентификация
				 */
				$encoder_service = $this->get('security.encoder_factory');
				$encoder = $encoder_service->getEncoder($user);
				$encoded_pass = $encoder->encodePassword($form->get('plainPassword')->getData(), $user->getSalt());
				if ($user->getPassword() == $encoded_pass) {
					/**
					 * Авторизация
					 */
					$token = new UsernamePasswordToken($user, null, "main", $user->getRoles());
					$this->get("security.context")->setToken($token); //now the user is logged in
					//now dispatch the login event
					$request = $this->get("request");
					$event = new InteractiveLoginEvent($request, $token);
					$this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
					$url = $this->generateUrl('login');
					$response = new RedirectResponse($url);
					return $response;
				} else {
					$form->addError(new FormError('Пароль указан не верно'));
				}
			}
		}
		$responseData = array(
			'form' => $form->createView(),
			'page' => $page,
		);
		$response = $this->render('@templates/' . $params['params']['template_directory'] . '/Security/' . $params['template_code'] . '.html.twig', $responseData);
		return $response;
	}


	public function ConfirmedAction(Request $request, $params)
	{
		$page_class = $this->get('Page');
		$page = $page_class->GetById($params['page_id']);
		$user = $this->container->get('security.context')
			->getToken()
			->getUser();
		$stringClassName = 'Novuscom\CMFUserBundle\Entity\User';
		if (!is_object($user) || !$user instanceof $stringClassName) {
			throw new NotFoundHttpException('Пользователь не найден');
		}
		$responseData = array(
			'page' => $page,
			'user' => $user,
		);
		$response = $this->render('@templates/' . $params['params']['template_directory'] . '/Registration/' . $params['template_code'] . '.html.twig', $responseData);
		return $response;
	}

	public function ConfirmAction(Request $request, $TOKEN)
	{
		/** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
		$userManager = $this->get('fos_user.user_manager');

		$user = $userManager->findUserByConfirmationToken($TOKEN);

		if (null === $user) {
			//throw new NotFoundHttpException(sprintf('The user with confirmation token "%s" does not exist', $TOKEN));
			throw new NotFoundHttpException('Пользователь не найден');
		}

		/** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
		$dispatcher = $this->get('event_dispatcher');

		$user->setConfirmationToken(null);
		$user->setEnabled(true);

		$event = new GetResponseUserEvent($user, $request);
		$dispatcher->dispatch(FOSUserEvents::REGISTRATION_CONFIRM, $event);

		$userManager->updateUser($user);
		$routeName = $request->get('_route');
		if (null === $response = $event->getResponse()) {
			$url = $this->generateUrl('registration__confirmed');
			$response = new RedirectResponse($url);
		}

		$dispatcher->dispatch(FOSUserEvents::REGISTRATION_CONFIRMED, new FilterUserResponseEvent($user, $request, $response));

		return $response;
	}

	public function RegistrationAction(
		$params = false,
		Request $request
	)
	{

		$routeParams = $request->get('_route_params');
		$routeName = $request->get('_route');


		$page_class = $this->get('Page');
		$page = $page_class->GetById($params['page_id']);


		$userManager = $this->container->get('fos_user.user_manager');
		$user = $userManager->createUser();
		$user->setEnabled(false);

		$form = $this->createForm(new RegisterType(), null, array(
			'action' => $this->generateUrl('cmf_page_frontend', array('name' => $page->getUrl())),
			'method' => 'POST',
			'attr' => array('class' => 'test'),
		));
		$form->setData($user);


		$form->handleRequest($request);
		if ($form->isValid()) {
			$data = $form->getData();
			$by_email = $userManager->findUserByEmail($form->get('email')->getData());
			$by_username = $userManager->findUserByUsername($form->get('username')->getData());
			if (count($by_email)) {
				$form->get('email')->addError(new FormError('Пользовтаель с такой электронной почтой уже зарегистрирован на сайте'));
			}
			if (count($by_username)) {
				$form->get('username')->addError(new FormError('Пользователь с таким именем уже зарегистрирован на сайте'));
			}
			if (count($by_email) == 0 && count($by_username) == 0) {

				$em = $this->getDoctrine()->getManager();
				//$group = $em->getRepository('NovuscomCMFUserBundle:Group')->find(1); // задаем группу
				//$user->addGroup($group);
				$gen = new TokenGenerator();
				$token = $gen->generateToken();
				$user->setConfirmationToken($token);
				$userManager->updateUser($user);


				/*
				 * Регистрируем событие
				 */
				$dispatcher = $this->container->get('event_dispatcher');
				$event = new CMFUserEvent($user, $params, $request);
				$dispatcher->dispatch(UserEvents::USER_REGISTER, $event);


				$this->get('session')->getFlashBag()->add(
					'ok',
					'Спасибо! Вы зарегистрированы на сайте. Теперь вам необходимо подтвердить регистрацию по электронной почте'
				);

				return $this->redirect($this->generateUrl($routeName, array('name' => $page->getUrl())));
			}


		}

		$responseData = array(
			'form' => $form->createView(),
			'page' => $page
		);

		$response = $this->render('@templates/' . $params['params']['template_directory'] . '/Registration/' . $params['template_code'] . '.html.twig', $responseData);

		return $response;
	}

	private function routeToControllerName($routename)
	{
		$routes = $this->get('router')->getRouteCollection();
		return $routes->get($routename)->getDefaults()['_controller'];
	}

	public function CrumbsAction($params = false, Request $request)
	{
		$response = new Response();
		$Crumbs = $this->get('Crumbs');
		$res = $Crumbs->getForSite($params);
		return $response->setContent($res);
	}

	



	private function getEnv()
	{
		$env = $this->get('kernel')->getEnvironment();
		return $env;
	}

	private function getCahceDir()
	{
		$env = $this->getEnv();
		return $this->get('kernel')->getRootDir() . '/cache/' . $env . '/sys/SectionAction/';
	}

	public function ElementAction(
		$params = array(),
		$CODE = false,
		$ID = false,
		$SECTION_CODE = false,
		Request $request
	)
	{

		$logger = $this->get('logger');
		$logger->notice('Начал работу контроллер ElementAction');
		if (array_key_exists('BLOCK_ID', $params) === false) {
			$params['BLOCK_ID'] = false;
		}
		$env = $this->get('kernel')->getEnvironment();

		/*
		 * Кэш в зависиомсти от окружения нужен для того чтобы правильные ссылки кешировались
		 */

		$cacheId = json_encode(array($params, $CODE, $ID));
		if (false) {
			//if ($fooString = $cacheDriver->fetch($cacheId)) {
			//echo '<pre>' . print_r('кешированные данные', true) . '</pre>';
			$cacheData = unserialize($fooString);
		} else {

			/**
			 * Проверяем закрыт ли сайт на обслуживание
			 */
			if ($this->checkConstruction()) {
				return $this->constructionResponse();
			};

			/**
			 * Параметры
			 */
			$block_id = $params['BLOCK_ID'];

			/**
			 * Переменные
			 */
			$em = $this->getDoctrine()->getManager();
			$page_repository = $em->getRepository('NovuscomCMFBundle:Page');

			/**
			 * Информация о текущем сайте
			 */
			$site = $this->getAlias()->getSite();
			//echo '<pre>' . print_r($site->getId(), true) . '</pre>';

			/**
			 * Получаем информацию об инфоблоке
			 */
			$block = false;
			if ($block_id !== false)
				$block = $em->getRepository('NovuscomCMFBundle:Block')->findOneBy(
					array(
						'id' => $block_id,
					)
				);

			/**
			 * Получаем ид инфоблоков которые принадлежат текущему сайту
			 */
			$sites_blocks_id = array();

			foreach ($site->getBlocks() as $SiteBlock) {
				$sites_blocks_id[] = $SiteBlock->getId();
			}
			//$sites_blocks_id = array_unique($sites_blocks_id);
			//echo '<pre>' . print_r($sites_blocks_id, true) . '</pre>';
			//echo '<pre>' . print_r($site->getId(), true) . '</pre>';

			/**
			 * Если инфоблок не принадлежит данному сайту - выдаем ошибку
			 */
			if ($block && !in_array($block->getId(), $sites_blocks_id)) {
				throw $this->createNotFoundException('Инфоблок не принадлежит данному сайту');
			}

			/*
			 * Получаем раздел
			 */
			$section = false;
			$elementsId = array();

			$SectionClass = $this->get('SectionClass');

			//Utils::msg($SECTION_CODE); exit;
			//Utils::msg($CODE); exit;


			/**
			 * Получение информации о страницах
			 */
			$page = false;
			if (array_key_exists('page_id', $params))
				$page = $page_repository->find($params['page_id']);




			if ($SECTION_CODE) {
				$section = $SectionClass->GetSectionByPath($SECTION_CODE, $block_id, $params['params']);
				$ElementSection = $em->getRepository('NovuscomCMFBundle:ElementSection')->findBy(array('section' => $section));
				foreach ($ElementSection as $es) {
					$elementsId[] = $es->getElement()->getId();
				}
				if (!$elementsId) {
					$logger->notice('Не найдены элементы в разделе [' . $SECTION_CODE . ']');
					throw $this->createNotFoundException('Элемент не найден');
				}
			}
			else {
				//Utils::msg($params);
				//exit;
				$fb = array();
				if ($page && $page->getLvl()!=0) {
					$fb['section'] = null;
				}
				$ElementSection = $em->getRepository('NovuscomCMFBundle:ElementSection')->findBy($fb);
				foreach ($ElementSection as $es) {
					$elementsId[] = $es->getElement()->getId();
				}
			}

			/*
			 * Получаем информацию об элементе
			 */
			$filter = array(
				'active' => true
			);
			if ($CODE) {
				$filter['code'] = $CODE;
			}
			if ($ID) {
				$filter['id'] = $ID;
			}
			if ($block_id)
				$filter['block'] = $block_id;
			/*if ($elementsId) {
				$filter['id'] = $elementsId;
			}*/
			$element = $em->getRepository('NovuscomCMFBundle:Element')->findOneBy(
				$filter
			);
			if (!$element) {
				$logger->notice('Элемент не найден по фильтру: <pre>' . print_r($filter, true) . '</pre>');
				throw $this->createNotFoundException('Элемент не найден по фильтру');
			}
			if ($section === false) {
				$section = array();
				foreach ($element->getSection() as $s) {
					$section[] = $s;
				}
			}




			/**
			 * Свойства элемента
			 */
			$properties_codes = array();
			$properties_by_code = array();
			$ElementProperties = $em->getRepository('NovuscomCMFBundle:Property')->findBy(
				array(
					'block' => $block,
				)
			);
			$properties_by_type = array();
			foreach ($ElementProperties as $ep) {
				$properties_codes[$ep->getId()] = $ep->getCode();
				$property_info = array(
					'name' => $ep->getName(),
					'code' => $ep->getCode(),
					'id' => $ep->getId(),
					'type' => $ep->getType(),
					'params' => json_decode($ep->getInfo(), true),
					'value' => null,
				);
				$properties_by_code[$ep->getCode()] = $property_info;
				$properties_by_type[$ep->getType()][$ep->getId()] = $property_info;
			}

			/**
			 * Значения свойств элемента
			 */
			$PropertyValues = $em->getRepository('NovuscomCMFBundle:ElementProperty')->findBy(
				array(
					'element' => $element,
				)
			);
			$values_by_code = array();
			foreach ($PropertyValues as $pv) {
				$code = $properties_codes[$pv->getProperty()->getId()];
				$values_by_code[$code][] = $pv->getValue();
			}
			$logger->info('<pre>' . print_r($values_by_code, true) . '</pre>');
			foreach ($values_by_code as $code => $n) {
				$prop = $properties_by_code[$code];
				if (is_array($prop['params']) && array_key_exists('MULTIPLE', $prop['params']) && $prop['params']['MULTIPLE']) {
					$value = $n;
				} else {
					$value = $n[0];
				}
				$properties_by_code[$code]['value'] = $value;
			}


			/**
			 * Значения свойства типа "файл"
			 */
			$PropertyValuesF = $em->getRepository('NovuscomCMFBundle:ElementPropertyF')->findBy(
				array(
					'element' => $element,
				)
			);
			$values_by_property = array();
			foreach ($PropertyValuesF as $pv) {
				//echo '<pre>' . print_r($pv->getFile()->getName(), true) . '</pre>';
				$values_by_property[$pv->getProperty()->getId()]['id'][] = $pv->getFile()->getId();
				$values_by_property[$pv->getProperty()->getId()]['name'][] = $pv->getFile()->getName();
				$values_by_property[$pv->getProperty()->getId()]['src'][] = $pv->getFile()->getImagePath();
			}
			foreach ($values_by_property as $property_id => $value) {
				$code = $properties_codes[$property_id];
				$properties_by_code[$code] = $value;
			}

			/**
			 * Значения свойств типа "список"
			 */
			$list = array();
			if (array_key_exists('LIST', $properties_by_type)) {
				$PropertyList = $em->getRepository('NovuscomCMFBundle:PropertyList')->findBy(
					array(
						'property' => array_keys($properties_by_type['LIST']),
					)
				);
				foreach ($PropertyList as $pl) {
					$list[$pl->getId()] = array(
						'value' => $pl->getValue(),
						'code' => $pl->getCode(),
						'id' => $pl->getId(),
					);
				}
			}

			$list = array();
			if (array_key_exists('LIST', $properties_by_type)) {
				$PropertyList = $em->getRepository('NovuscomCMFBundle:PropertyList')->findBy(
					array(
						'property' => array_keys($properties_by_type['LIST']),
					)
				);
				foreach ($PropertyList as $pl) {
					$list[$pl->getId()] = array(
						'value' => $pl->getValue(),
						'code' => $pl->getCode(),
						'id' => $pl->getId(),
					);
				}
			}

			//echo '<pre>' . print_r($properties_by_code, true) . '</pre>';

			/**
			 * Собираем все свойства
			 */
			//echo '<pre>' . print_r($properties_by_code, true) . '</pre>';
			foreach ($properties_by_code as $code => $n) {
				if (array_key_exists('type', $n)) {
					if ($n['type'] == 'LIST' && array_key_exists($n['value'], $list)) {
						$lv = $list[$n['value']];
						$n['value'] = $lv['value'];
						$n['code'] = $lv['code'];
						$n['id'] = $lv['id'];
					} else {

					}
				}
				$element->addProperty($code, $n);
			}


			/**
			 * Элемент
			 */
			$entity_element = $element;
			$element = array(
				'id' => $entity_element->getId(),
				'name' => $entity_element->getName(),
				'properties' => $entity_element->getProperties(),
				'detailText' => $entity_element->getDetailText(),
				'previewText' => $entity_element->getPreviewText(),
				'lastModified' => $entity_element->getLastModified(),
				'previewPicture' => array(),
				'detailPicture' => array(),
				'header' => $entity_element->getHeader(),
				'title' => $entity_element->getTitle(),
				'keywords' => $entity_element->getKeywords(),
				'description' => $entity_element->getDescription(),
			);
			if ($previewPicture = $entity_element->getPreviewPicture()) {
				$element['previewPicture']['path'] = $previewPicture->getImagePath();
				$element['previewPicture']['name'] = $previewPicture->getName();
				$element['previewPicture']['description'] = $previewPicture->getDescription();
			}
			if ($entity_element->getDetailPicture()) {
				$element['detailPicture']['path'] = $entity_element->getDetailPicture()->getImagePath();
				$element['detailPicture']['name'] = $entity_element->getDetailPicture()->getName();
			}

			//echo '<pre>' . print_r($element, true) . '</pre>';

			$response_data = array(
				'element' => $element,
				'title' => $entity_element->getTitle(),
				'header' => $entity_element->getHeader(),
				'description' => $entity_element->getDescription(),
				'keywords' => $entity_element->getKeywords(),
				'section' => $section,
				'site' => $site,
			);
			if (!$response_data['title'])
				$response_data['title'] = $entity_element->getName();
			if (!$response_data['header'])
				$response_data['header'] = $entity_element->getName();
			if (!$response_data['description'])
				$response_data['description'] = $entity_element->getName();
			if (!$response_data['keywords'])
				$response_data['keywords'] = $entity_element->getName();
			if ($page) {
				$response_data['page'] = $page;
			}
			if (isset($params['params']['template_code'])) {
				$template_code = $params['params']['template_code'];
			} else {
				$template_code = $params['template_code'];
			}
			$template_dir = $site->getCode();
			$template = '@templates/' . $template_dir . '/Element/' . $template_code . '.html.twig';
			if ($this->get('templating')->exists($template) === false) {
				$template = 'NovuscomCMFBundle:DefaultTemplate/Element:default.html.twig';
			}
			$response = $this->render($template, $response_data);
			$cacheData = array(
				'response' => $response,
				'lastModified' => $element['lastModified'],
			);
			//echo '<pre>' . print_r('НЕ кешированные данные', true) . '</pre>';
		}
		//$cacheData['response']->setLastModified($cacheData['lastModified']);
		if ($env == 'prod') {
			//$cacheData['response']->setETag(md5($cacheData['response']->getContent()));
			//$cacheData['response']->setPublic(); // make sure the response is public/cacheable
			//$cacheData['response']->isNotModified($request);
			//$cacheData['response']->setSharedMaxAge(600);
			//$cacheData['response']->setMaxAge(600);
		}


		return $cacheData['response'];
	}


	private function getElementsList($blockId, $sectionId = false, $params)
	{
		$ElementsList = $this->get('ElementsList');
		$Element = $this->get('Element');
		$ElementsList->setBlockId($blockId);
		$ElementsList->setSectionId($sectionId);
		$ElementsList->setSelect(array('code', 'last_modified', 'preview_picture', 'preview_text'));
		$ElementsList->setOrder(array('sort' => 'asc', 'name' => 'asc', 'id' => 'desc'));
		$ElementsList->selectProperties(array('price'));
		//echo '<pre>' . print_r($params, true) . '</pre>';
		if (isset($params['LIMIT']) && is_numeric($params['LIMIT'])) {
			$ElementsList->setLimit($params['LIMIT']);
		}
		if ($params && array_key_exists('params', $params) && array_key_exists('INCLUDE_SUB_SECTIONS', $params['params']))
			$ElementsList->setIncludeSubSections($params['params']['INCLUDE_SUB_SECTIONS']);
		if ($params && array_key_exists('params', $params) && array_key_exists('INCLUDE_SUBSECTIONS', $params['params']))
			$ElementsList->setIncludeSubSections($params['params']['INCLUDE_SUBSECTIONS']);

		$elements = $ElementsList->getResult();

		foreach ($elements as $e) {
			//$Element->getFullCode($e['id']);
		}

		return $elements;
	}


	private function setSectionsPictures($sections)
	{
		$em = $this->getDoctrine()->getManager();
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


	/**
	 * Список разделов инфоблока
	 * @param array $params Параметры компонента
	 * @param Request $request
	 * @return mixed
	 */
	public function SectionsListAction($params, Request $request)
	{
		if ($this->checkConstruction()) {
			return $this->constructionResponse();
		};

		/**
		 * Переменные
		 */
		$response = new Response();
		$em = $this->getDoctrine()->getManager();
		$page_repository = $em->getRepository('NovuscomCMFBundle:Page');
		$host = $request->headers->get('host');
		$cacheId = json_encode($params);
		$BLOCK_ID = $params['BLOCK_ID'];
		$section_id = null;
		if (array_key_exists('SECTION_ID', $params)) {
			$section_id = $params['SECTION_ID'];
		}
		$template_code = 'default';
		if (array_key_exists('template_code', $params)) {
			$template_code = $params['template_code'];
		}
		$env = $this->get('kernel')->getEnvironment();
		//echo '<pre>' . print_r($params, true) . '</pre>';

		/**
		 * Кэш
		 */
		//$cacheDriver = new \Doctrine\Common\Cache\FilesystemCache($_SERVER['DOCUMENT_ROOT'] . '/../app/cache/' . $env . '/sys/' . $host . '/components/SectionsList/');
		//$cacheDriver = new \Doctrine\Common\Cache\ApcuCache();
		if (false) {
			//if ($fooString = $cacheDriver->fetch($cacheId)) {
			//echo '<pre>' . print_r('Кешированные данные', true) . '</pre>';
			$render = unserialize($fooString);
		} else {

			$SectionClass = $this->get('SectionClass');

			/**
			 * Получение необходимой информации
			 */
			$page = $page_repository->find($params['page_id']);


			//$block = $em->getRepository('NovuscomCMFBundle:Block')->find($BLOCK_ID);


			/*
			 * Разделы
			 */
			$sections = $SectionClass->SectionsList(array(
				'block_id' => $BLOCK_ID,
			));


			/*
			 * Картинки разделов
			 */
			$sections = $this->setSectionsPictures($sections);

			/**
			 * Данные попадающие в шаблон
			 */
			$response_data = array(
				'sections' => $sections,
			);
			$response_data['page'] = $page;
			$response_data['params'] = $params;
			$response_data['header'] = $page->getHeader();
			//$response_data['elements'] = $this->getElementsList($BLOCK_ID, false, $params);
			$response_data['title'] = $page->getTitle();
			//echo '<pre>' . print_r($sections, true) . '</pre>';
			$Site = $this->get('Site');
			$site = $Site->getCurrent();

			$render = $this->render('@templates/' . $site['code'] . '/SectionsList/' . $template_code . '.html.twig', $response_data, $response);

			//$cacheDriver->save($cacheId, serialize($render));
		}
		return $render;
	}


	private function constructionResponse()
	{
		$response = $this->forward('NovuscomCMFBundle:Default:closed', array());
		return $response;
	}

	private function checkConstruction()
	{
		$alias = $this->getAlias();
		$site = $alias->getSite();
		$securityContext = $this->container->get('security.authorization_checker');
		$result = ($site->getClosed() && !$securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED'));
		return $result;
	}

	public function SectionAction($params, $SECTION_CODE, Request $request, $PAGE = 1)
	{
		$logger = $this->get('logger');
		$logger->info('SectionAction');
		$logger->info(print_r($params, true));
		$env = $this->getEnv();
		$route_name = $request->get('_route');
		$route_params = $request->get('_route_params');
		$Site = $this->get('Site');
		$site = $Site->getCurrent();
		$routeOptions = array();
		if (array_key_exists('params', $params) === true)
			$routeOptions = $params['params'];

		$cacheDir = $this->getCahceDir();
		$logger->info('Директория кеша = ' . $cacheDir);
		$fullCode = trim($SECTION_CODE, '/');
		$cacheId = $fullCode . '[page=' . $PAGE . ']';
		$logger->info('Cache id = ' . print_r($cacheId, true));
		if (false) {
			//if ($fooString = $cacheDriver->fetch($cacheId)) {
			$logger->info('Информацию берем из кеша');
			$response = unserialize($fooString);
		} else {
			if ($this->checkConstruction()) {
				return $this->constructionResponse();
			};
			$em = $this->getDoctrine()->getManager();

			/*
			 * Страница
			 */
			$page_class = $this->get('Page');
			$page = $page_class->GetById($params['page_id']);


			/*
			 * Раздел
			 */
			$SectionClass = $this->get('SectionClass');
			$section = $SectionClass->GetSectionByPath($SECTION_CODE, $params['BLOCK_ID'], $routeOptions);
			if (!$section) {
				$logger->notice('Раздел не найден по пути ' . $SECTION_CODE . '');
				throw $this->createNotFoundException('Раздел не найден по пути ' . $SECTION_CODE);
			}
			$section->setFullCode($fullCode);

			$parentFullCode = null;
			$explode = explode('/', $fullCode);
			array_pop($explode);
			if ($explode)
				$parentFullCode = trim(implode($explode, '/'), '/');
			/*
			 * Параллельные разделы и подразделы
			 */
			//echo '<pre>' . print_r(($section->getRgt() - $section->getLft()), true) . '</pre>';
			if ($section->getParent()) {
				//echo '<pre>' . print_r('parent', true) . '</pre>';
				$sections = $SectionClass->SectionsList(array(
					'block_id' => $params['BLOCK_ID'],
					'section_id' => $section->getParent()->getId()
				), $parentFullCode);
			} else {
				$sections = $SectionClass->SectionsList(array(
					'block_id' => $params['BLOCK_ID'],
					//'section_id' => $section->getId(), // не нужен, т.к. берем все корневые
				), $fullCode);
			}
			$subSections = $SectionClass->SectionsList(array(
				'block_id' => $params['BLOCK_ID'],
				'section_id' => $section->getId()
			), $fullCode);

			/*
			 * Картинки разделов
			 */
			$sections = $this->setSectionsPictures($sections);
			$subSections = $this->setSectionsPictures($subSections);

			//echo '<pre>'.print_r($section->getId(), true).'</pre>';

			/*
			 * Элементы
			 */
			$ElementsList = $this->get('ElementsList');
			$ElementsList->setBlockId($params['BLOCK_ID']);
			$ElementsList->setSectionId($section->getId());
			$ElementsList->setSelect(array('code', 'last_modified', 'preview_picture', 'preview_text'));
			// Здесь сделать выборку всех доступных свойств ифноблока
			$properties = $section->getBlock()->getProperty();
			$propCodes = array();
			foreach ($properties as $p) {
				$propCodes[] = $p->getCode();
			}
			$ElementsList->selectProperties($propCodes);
			$ElementsList->setOrder(array('sort' => 'asc', 'name' => 'asc', 'id' => 'desc'));
			//echo '<pre>' . print_r($params, true) . '</pre>';
			if ($params && array_key_exists('params', $params) && array_key_exists('INCLUDE_SUB_SECTIONS', $params['params']))
				$ElementsList->setIncludeSubSections($params['params']['INCLUDE_SUB_SECTIONS']);
			$elements = $ElementsList->getResult();

			//echo '<pre>' . print_r($elements, true) . '</pre>';
			/*
			 * Обработка элементов перед выдачей
			 */
			$elementRoute = (array_key_exists('ELEMENT_ROUTE', $routeOptions));
			foreach ($elements as &$e) {
				$e['url'] = false;
				if ($elementRoute)
					$e['url'] = $this->generateUrl($params['params']['ELEMENT_ROUTE'], array(
						'SECTION_CODE' => $e['parent_section_full_code'],
						'CODE' => $e['code']
					));
			}

			// Здесь надо сделать редирект с первой страницы на раздел
			/*$url = $this->generateUrl('cmf_page_frontend', array(
				'name' => $parentFullCode,
			));*/
			//echo '<pre>' . print_r($url, true) . '</pre>';

			/*
			 * Пагинация
			 */
			$pagination = $this->getPagination($elements, $PAGE, $params, $site, $fullCode);
			if ($this->paginationRedirect !== false)
				return new RedirectResponse($this->paginationRedirect);

			function sectionsElements(&$sections, $elements){
				foreach ($elements as $e) {
					$sections[$e['parent_section']]['elements'][$e['id']] = $e;
				}
				return $sections;
			};

			/*
			 * Массив данных
			 */
			$response_data = array(
				'page' => $page,
				'section' => $section,
				'elements' => $elements,
				'sections' => $sections,
				'subSections' => $subSections,
				'title' => $section->getTitle(),
				'description' => $section->getDescription(),
				'keywords' => $section->getKeywords(),
				'header' => $section->getName(),
				'pagination' => $pagination,
				'sectionsElements' => sectionsElements($sections, $elements),
			);

			if (!$response_data['title'])
				$response_data['title'] = $section->getName();
			if (!$response_data['description'])
				$response_data['description'] = $section->getName();
			if (!$response_data['keywords'])
				$response_data['keywords'] = $section->getName();


			/*
			 * Ответ
			 */
			$response = $this->render('@templates/' . $site['code'] . '/Section/' . $params['template_code'] . '.html.twig', $response_data);
		}


		return $response;
	}


	public function ElementsListAction($params, Request $request, $PAGE = 1)
	{
		if (is_numeric($PAGE) === false || $PAGE < 1) {
			throw $this->createNotFoundException('Не может быть страницы меньше нуля для постраничной навигации и должно быть числом');
		}
		if ($this->checkConstruction()) {
			return $this->constructionResponse();
		};
		$logger = $this->get('logger');
		/**
		 * Переменные
		 */
		$response = new Response();
		$em = $this->getDoctrine()->getManager();
		$page_repository = $em->getRepository('NovuscomCMFBundle:Page');
		$host = $request->headers->get('host');
		$cacheId = json_encode($params);
		$section_id = null;
		if (array_key_exists('section_id', $params)) {
			$section_id = $params['section_id'];
		}
		$template_code = 'default';
		//$this->msg($params);
		if (array_key_exists('template_code', $params)) {
			$template_code = $params['template_code'];
			/*if (array_key_exists('params', $params) && array_key_exists('template_code', $params['params']))
				$template_code = $params['params']['template_code'];*/
		}
		$env = $this->get('kernel')->getEnvironment();
		$exist_page_id = array_key_exists('page_id', $params);
		if (!$exist_page_id) {
			$params['page_id'] = false;
		}
		if (!array_key_exists('LIMIT', $params)) {
			$params['LIMIT'] = null;
		}
		if (!array_key_exists('OPTIONS', $params)) {
			$params['OPTIONS'] = null;
		}
		$Site = $this->get('Site');
		$site = $Site->getCurrent();
		/**
		 * Кэш
		 */
		//$cacheDriver = new \Doctrine\Common\Cache\FilesystemCache($_SERVER['DOCUMENT_ROOT'] . '/../app/cache/' . $env . '/sys/' . $host . '/components/ElementsList/');
		$cacheDriver = new \Doctrine\Common\Cache\ApcuCache();
		$cacheDriver->setNamespace('ElementsListAction_' . $env . '_' . $params['BLOCK_ID']);
		if (false) {
			//if ($fooString = $cacheDriver->fetch($cacheId)) {
			//echo '<pre>' . print_r('есть такое в кеше', true) . '</pre>';
			$render = unserialize($fooString);
		} else {
			$response_data = array();

			$logger->info('Выбор списка элементов из инфоблока ' . $params['BLOCK_ID']);


			/**
			 * Элементы
			 */
			$elements = $this->getElementsList($params['BLOCK_ID'], false, $params);
			$response_data['title'] = '';

			$pageEntity = $page_repository->find($params['page_id']);
			if ($pageEntity) {
				$pagination = $this->getPagination($elements, $PAGE, $params, $site);
				if ($this->paginationRedirect !== false)
					return new RedirectResponse($this->paginationRedirect);
				$response_data['pagination'] = $pagination;
				$response_data['title'] = $pageEntity->getTitle();
				$response_data['header'] = $pageEntity->getHeader();
				$response_data['description'] = $pageEntity->getDescription();
				$response_data['page'] = $pageEntity;
			}


			/**
			 * Данные попадающие в шаблон
			 */

			$response_data['elements'] = $elements;
			$response_data['options'] = $params['OPTIONS'];
			$response_data['params'] = $params;

			$template = '@templates/' . $site['code'] . '/ElementsList/' . $template_code . '.html.twig';
			if ($this->get('templating')->exists($template) === false) {
				$template = 'NovuscomCMFBundle:DefaultTemplate/ElementsList:default.html.twig';
			}
			$render = $this->render($template, $response_data, $response);


		}
		return $render;
	}

	private $paginationRedirect = false;

	private function getPagination($elements, $PAGE, $routeParams, $site, $sectionFullCode = false)
	{
		$paginator = $this->get('knp_paginator');
		$pagination = $paginator->paginate(
			$elements,
			$PAGE,
			16
		);
		$pagination_route = preg_replace('/^(.+?)(_pagination)*$/', '\\1_pagination', $routeParams['template_code']);
		$pagination->setUsedRoute($pagination_route);
		if (!empty($sectionFullCode)) {
			//echo '<pre>' . print_r($sectionFullCode, true) . '</pre>';
			$pagination->setParam('SECTION_CODE', $sectionFullCode);
		}
		$pagination->setParam('params', null); // очищаем params - непонятно откуда берется на первой странице для других страниц
		$pagination->setTemplate('@templates/' . $site['code'] . '/Pagination/' . $routeParams['template_code'] . '.html.twig');
		if ($PAGE > 1 && count($pagination) < 1) {
			throw $this->createNotFoundException('Не найдено элементов на странице ' . $PAGE);
		}
		if (preg_match('/^(.+?)(_pagination)+$/', $routeParams['template_code'], $matches) && $PAGE == 1) {
			//$this->msg($matches);
			if ($sectionFullCode)
				$url = $this->get('router')->generate($matches[1], array('SECTION_CODE' => $sectionFullCode));
			else
				$url = $this->get('router')->generate($matches[1]);
			//$this->msg($url);
			$this->paginationRedirect = $url;
		}
		return $pagination;
	}

	private function getAlias()
	{
		$request = Request::createFromGlobals();
		$em = $this->getDoctrine()->getManager();
		$host = $request->headers->get('host');
		$alias = $em->getRepository('NovuscomCMFBundle:Alias')->findOneBy(array('name' => $host));
		return $alias;
	}
}
