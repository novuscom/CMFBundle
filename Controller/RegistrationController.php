<?php

namespace Novuscom\CMFBundle\Controller;

/*use Novuscom\CMFBundle\Services\Utils;
use Symfony\Component\HttpFoundation\RedirectResponse;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
*/
use FOS\UserBundle\Controller\RegistrationController as BaseController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Novuscom\CMFBundle\Services\Utils;
use FOS\UserBundle\Form\Factory\FactoryInterface;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Form\Extension\Core\Type\TextType;



class RegistrationController extends BaseController
{

	/*public function registerAction(Request $request)
	{
		if ($this->getUser() instanceof UserInterface) {
			return $this->redirectToRoute($this->getParameter('fos_user.user.default_route'));
		}


		$formFactory = $this->get('fos_user.registration.form.factory');

		$userManager = $this->get('fos_user.user_manager');

		$dispatcher = $this->get('event_dispatcher');

		$user = $userManager->createUser();
		$user->setEnabled(true);

		$event = new GetResponseUserEvent($user, $request);
		$dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);

		if (null !== $event->getResponse()) {
			return $event->getResponse();
		}

		$form = $formFactory->createForm();


		$em = $this->getDoctrine()->getManager();
		$userCount = $em
			->createQuery('SELECT COUNT(n.id) FROM NovuscomCMFBundle:User n')
			->getSingleScalarResult();
		if ($userCount == 0) {
			$form->add('check', PasswordType::class, array(
				'label' => 'Секретный ключ',
				'attr' => array(
					'class' => 'form-control'
				),
				'mapped' => false,
				'constraints' => new Constraints\EqualTo(array(
					'value' => $this->container->getParameter('secret'),
					'message' => 'Секретный ключ указан неверно'
				))
			));
		}

		$form->setData($user);

		$form->handleRequest($request);

		if ($form->isSubmitted()) {
			if ($form->isValid()) {

				$user->addRole('ROLE_USER');
				$userAdmin = false;
				if ($form->has('check') && $form->get('check')->isValid()) {
					$user->addRole('ROLE_ADMIN');
					$userAdmin = true;
				}

				$event = new FormEvent($form, $request);
				$dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);

				$userManager->updateUser($user);

				if (null === $response = $event->getResponse()) {
					$url = $this->generateUrl('fos_user_registration_confirmed');
					$response = new RedirectResponse($url);
				}

				$dispatcher->dispatch(FOSUserEvents::REGISTRATION_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

				return $response;
			}

			$event = new FormEvent($form, $request);
			$dispatcher->dispatch(FOSUserEvents::REGISTRATION_FAILURE, $event);

			if (null !== $response = $event->getResponse()) {
				return $response;
			}
		}
		$templating = $this->get('novuscom.cmf.templating');
		$path = $templating->getPath('Registration');


		return $this->render($path, array(
			'form' => $form->createView(),
			'title' => 'Регистрация нового пользователя',
			'header' => 'Регистрация нового пользователя',
		));
	}*/

	public function registerAction(Request $request)
	{
		/** @var $formFactory FactoryInterface */
		$formFactory = $this->get('fos_user.registration.form.factory');
		/** @var $userManager UserManagerInterface */
		$userManager = $this->get('fos_user.user_manager');
		/** @var $dispatcher EventDispatcherInterface */
		$dispatcher = $this->get('event_dispatcher');

		$user = $userManager->createUser();
		$user->setEnabled(true);

		$event = new GetResponseUserEvent($user, $request);
		$dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);

		if (null !== $event->getResponse()) {
			return $event->getResponse();
		}

		$form = $formFactory->createForm();
		$form->setData($user);

		$form->handleRequest($request);

		if ($form->isSubmitted()) {
			if ($form->isValid()) {
				$event = new FormEvent($form, $request);
				$dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);

				$userManager->updateUser($user);

				if (null === $response = $event->getResponse()) {
					$url = $this->generateUrl('fos_user_registration_confirmed');
					$response = new RedirectResponse($url);
				}

				$dispatcher->dispatch(FOSUserEvents::REGISTRATION_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

				return $response;
			}

			$event = new FormEvent($form, $request);
			$dispatcher->dispatch(FOSUserEvents::REGISTRATION_FAILURE, $event);

			if (null !== $response = $event->getResponse()) {
				return $response;
			}
		}

		$templating = $this->get('novuscom.cmf.templating');
		$path = $templating->getPath('Registration');


		return $this->render($path, array(
			'form' => $form->createView(),
			'title' => 'Регистрация нового пользователя',
			'header' => 'Регистрация нового пользователя',
		));
	}


}