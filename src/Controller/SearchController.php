<?php

namespace App\Controller;

use App\Controller\Extendable\TranslatableController;
use App\Entity\User;
use App\Repository\TaskListRepository;
use App\Repository\UserRepository;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Exception\ValidatorException;

/**
 * @Route("/{_locale}/search", name="search_", locale="en", requirements={"_locale": "[a-z]{2}"})
 */
class SearchController extends TranslatableController
{
    /**
     * @Route("/", name="index")
     *
     * @param TaskListRepository $taskListRepository
     * @param UserRepository     $userRepository
     * @param Request            $request
     *
     * @return Response
     */
    public function index(
        TaskListRepository $taskListRepository,
        UserRepository $userRepository,
        Request $request
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if (!$this->isCsrfTokenValid('search', $request->request->get('_token'))) {
            throw new ValidatorException('validation.invalid_csrf');
        }

        $searchPhrase = $request->request->get('value');

        $taskLists = $taskListRepository->searchLists($user, $searchPhrase);
        $taskListItems = $taskListRepository->searchListItems($user, $searchPhrase);

        // users
        // authors lists
        // shared lists
        // archived

        return $this->render(
            'search/index.html.twig',
            [
                'search_phrase' => $searchPhrase,
                'task_lists' => array_unique(
                    array_merge($taskLists, $taskListItems),
                    SORT_REGULAR
                ),
            ]
        );
    }
}
