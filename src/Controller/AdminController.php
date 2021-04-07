<?php

namespace App\Controller;

use App\Controller\Extendable\TranslatableController;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("{_locale}/master", name="admin_", requirements={"_locale": "[a-z]{2}"})
 */
class AdminController extends TranslatableController
{
    /**
     * @Route("/panel", name="panel")
     *
     * @param Request        $request
     * @param UserRepository $userRepository
     *
     * @return Response
     */
    public function panel(Request $request, UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render(
            'admin/panel.html.twig',
            [
                'users' => $users,
            ]
        );
    }
}
