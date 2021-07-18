<?php

namespace App\Controller;

use App\Controller\Extendable\TranslatableController;
use App\DTO\User\Settings;
use App\Entity\User;
use App\Form\UserSettingsType;
use App\Repository\UserRepository;
use App\UseCase\User\RegistrationHandler;
use App\UseCase\User\UserHandler;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

/**
 * @Route("{_locale}/user", name="user_", locale="en", requirements={"_locale": "[a-z]{2}"})
 */
class UserController extends TranslatableController
{
    /**
     * @Route("/settings", name="settings")
     *
     * @param Request             $request
     * @param RegistrationHandler $registrationHandler
     *
     * @return Response
     */
    public function settings(Request $request, RegistrationHandler $registrationHandler): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(UserSettingsType::class, $user)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $settingData = new Settings();
                $settingData->locale = $form->get('locale')->getData();
                $settingData->oldPassword = $form->get('current_password')->getData();
                $settingData->newPassword = $form->get('new_password')->getData();

                $user = $registrationHandler->updateSettings($user, $settingData);

                return $this->redirectToRoute('user_settings', ['_locale' => $user->getLocale()]);
            } catch (Throwable $e) {
                $this->addFlash(
                    'danger',
                    $this->translator->trans($e->getMessage())
                );
            }
        }

        return $this->render(
            'user/settings.html.twig',
            [
                'task_lists' => $user,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/users", name="users")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function users(Request $request): Response
    {
        /** @var UserRepository $userRepo */
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $users = $userRepo->findFriends($currentUser);

        return $this->render(
            'user/users.html.twig',
            [
                'users' => $users,
            ]
        );
    }

    /**
     * @Route("/user/{email}", name="user")
     *
     * @param string $email
     * @param Request $request
     *
     * @return Response
     *
     * @throws NonUniqueResultException
     */
    public function user(string $email, Request $request): Response
    {
        /** @var UserRepository $userRepo */
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $user = $userRepo->findUser($email);
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        if ($user && !$user->isBanned($currentUser)) {
            return $this->render(
                'user/user.html.twig',
                [
                    'user' => $user,
                ]
            );
        } else {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @Route("/add-to-favourites/{email}", name="add_to_favourites")
     *
     * @param string      $email
     * @param Request     $request
     * @param UserHandler $userHandler
     *
     * @return RedirectResponse
     *
     * @throws NonUniqueResultException
     */
    public function addToFavourites(string $email, Request $request, UserHandler $userHandler): Response
    {
        /** @var UserRepository $userRepo */
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $friend = $userRepo->findUser($email);
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        if ($friend) {
            $userHandler->addToFavourites($currentUser, $friend);
        } else {
            $this->addFlash('danger', 'user.not_found');
        }

        $referer = $request->headers->get('referer');

        return $referer ? $this->redirect($referer) : $this->redirectToRoute('task_list_index');
    }

    /**
     * @Route("/remove-from-favourites/{email}", name="remove_from_favourites")
     *
     * @param string      $email
     * @param Request     $request
     * @param UserHandler $userHandler
     *
     * @return RedirectResponse
     *
     * @throws NonUniqueResultException
     */
    public function removeFromFavourites(string $email, Request $request, UserHandler $userHandler): Response
    {
        /** @var UserRepository $userRepo */
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $friend = $userRepo->findUser($email);
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        if ($friend) {
            $userHandler->removeFromFavourites($currentUser, $friend);
        } else {
            $this->addFlash('danger', 'user.not_found');
        }

        $referer = $request->headers->get('referer');

        return $referer ? $this->redirect($referer) : $this->redirectToRoute('task_list_index');
    }

    /**
     * @Route("/block/{email}", name="block")
     *
     * @param string      $email
     * @param Request     $request
     * @param UserHandler $userHandler
     *
     * @return RedirectResponse
     *
     * @throws NonUniqueResultException
     */
    public function blockUser(string $email, Request $request, UserHandler $userHandler): Response
    {
        /** @var UserRepository $userRepo */
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $friend = $userRepo->findUser($email);
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        if ($friend) {
            $userHandler->blockUser($currentUser, $friend);
        } else {
            $this->addFlash('danger', 'user.not_found');
        }

        $referer = $request->headers->get('referer');

        return $referer ? $this->redirect($referer) : $this->redirectToRoute('task_list_index');
    }

    /**
     * @Route("/unblock/{email}", name="unblock")
     *
     * @param string      $email
     * @param Request     $request
     * @param UserHandler $userHandler
     *
     * @return RedirectResponse
     *
     * @throws NonUniqueResultException
     */
    public function unblock(string $email, Request $request, UserHandler $userHandler): Response
    {
        /** @var UserRepository $userRepo */
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $friend = $userRepo->findUser($email);
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        if ($friend) {
            $userHandler->unblockUser($currentUser, $friend);
        } else {
            $this->addFlash('danger', 'user.not_found');
        }

        $referer = $request->headers->get('referer');

        return $referer ? $this->redirect($referer) : $this->redirectToRoute('task_list_index');
    }
}
