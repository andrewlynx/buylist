<?php

namespace App\Controller;

use App\Entity\TaskListPublic;
use App\Repository\TaskListPublicRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

/**
 * @Route("/{_locale}", locale="en", requirements={"_locale": "[a-z]{2}"})
 */
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
     * @Route("/welcome", name="welcome")
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
     * @Route("/about", name="about")
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
     * @Route("/list-public/{id}", name="list_public")
     *
     * @param string                   $id
     * @param Request                  $request
     * @param TaskListPublicRepository $repo
     *
     * @return Response
     */
    public function taskListPublic(string $id, Request $request, TaskListPublicRepository $repo): Response
    {
        /** @var TaskListPublic|null $taskListPublic */
        $taskListPublic = $repo->find($id);
        if ($taskListPublic && $taskListPublic->isPublic()) {
            return $this->render(
                $this->getPublicUrl('task-list', $request->getLocale()),
                [
                    'task_list' => $taskListPublic->getTaskList(),
                ]
            );
        }

        throw $this->createNotFoundException();
    }

    /**
     * @param string $template
     * @param string $locale
     *
     * @return string
     */
    private function getPublicUrl(string $template, string $locale = 'en'): string
    {
        return 'v1/public/'.$locale.'/'.$template.'.html.twig';
    }

    /**
     * @param string $template
     * @param string $locale
     *
     * @return Response
     */
    private function renderLocalizedTemplate(string $template, string $locale): Response
    {
        // Try to find localized template or load default
        return $this->render(
            $this->twig->getLoader()->exists($this->getPublicUrl($template, $locale))
            ? $this->getPublicUrl($template, $locale)
            : $this->getPublicUrl($template)
        );
    }
}
