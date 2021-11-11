<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class OAuthController extends AbstractController
{
    /**
     * @Route("/connect/google", name="connect_google_start")
     *
     * @param ClientRegistry $clientRegistry
     *
     * @return RedirectResponse
     */
    public function redirectToGoogleConnect(ClientRegistry $clientRegistry)
    {
        return $clientRegistry
            ->getClient('google')
            ->redirect(
                [
                    'email', 'profile'
                ],
                []
            );
    }

    /**
     * @Route("/google/auth", name="google_auth")
     *
     * @param Request $request
     *
     * @return JsonResponse|RedirectResponse
     */
    public function connectGoogleCheck(Request $request)
    {
        if (!$this->getUser()) {
            return new JsonResponse(['status' => false, 'message' => "User not found!"]);
        } else {
            return $this->redirectToRoute('task_list_index');
        }
    }
}