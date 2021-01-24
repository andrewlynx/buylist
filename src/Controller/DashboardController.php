<?php

namespace App\Controller;

use App\Controller\Extendable\TranslatableController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/")
 */
class DashboardController extends TranslatableController
{
    /**
     * @Route("", name="index")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request): Response
    {

        return $this->redirectToRoute('welcome', ['_locale' => $request->getLocale() ?? $request->getDefaultLocale()]);
    }
}
