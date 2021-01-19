<?php

namespace App\Controller;

use App\Form\PasswordRestoreType;
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

    /**
     * @param Environment $twig
     */
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

        return $this->renderLocalizedTemplate('welcome', $request->getLocale());
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

        return $this->renderLocalizedTemplate('about', $request->getLocale());
    }

    /**
     * @Route("/{_locale}/restore-passwordaa", name="restore_passwordaa")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function restorePassword(Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('task_list_index');
        }

        $form = $this->createForm(PasswordRestoreType::class)
            ->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

        }

        return $this->render('public/restore-password.html.twig', [
            'form' => $form->createView(),
        ]);
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

    /**
     * @param string $template
     * @param string $locale
     *
     * @return Response
     */
    private function renderLocalizedTemplate(string $template, string $locale): Response
    {
        return $this->render(
            $this->twig->getLoader()->exists(
                $this->getPublicUrl($template, $locale)
            )
                ? $this->getPublicUrl($template, $locale)
                : $this->getPublicUrl($template)
        );
    }
}