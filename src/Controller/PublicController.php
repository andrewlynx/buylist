<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class PublicController extends AbstractController
{
    /**
     * @var Environment
     */
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @Route("/{_locale}/welcome", name="welcome")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function welcome(Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('task_list_index');
        }

        return $this->render(
            $this->twig->getLoader()->exists(
                $this->getPublicUrl('welcome', $request->getLocale())
            )
            ? $this->getPublicUrl('welcome', $request->getLocale())
            : $this->getPublicUrl('welcome')
        );
    }

    /**
     * @Route("/{_locale}/about", name="about")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function about(Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('task_list_index');
        }

        return $this->render(
            $this->twig->getLoader()->exists(
                $this->getPublicUrl('about', $request->getLocale())
            )
                ? $this->getPublicUrl('about', $request->getLocale())
                : $this->getPublicUrl('about')
        );
    }

    /**
     * @param string $template
     * @param string $locale
     *
     * @return string
     */
    private function getPublicUrl(string $template, string $locale = 'en'): string
    {
        return 'public/'.$locale.'/'.$template.'.html.twig';
    }
}