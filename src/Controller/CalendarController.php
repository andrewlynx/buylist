<?php

namespace App\Controller;

use App\Controller\Extendable\TranslatableController;
use App\Controller\Traits\FormsTrait;
use App\Entity\User;
use App\Repository\TaskListRepository;
use App\Service\Calendar\Calendar;
use DateTime;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/{_locale}/calendar", name="calendar_", locale="en", requirements={"_locale": "[a-z]{2}"})
 */
class CalendarController extends TranslatableController
{
    use FormsTrait;

    /**
     * @var Calendar
     */
    private $calendar;

    /**
     * @param TranslatorInterface $translator
     * @param Calendar            $calendar
     */
    public function __construct(TranslatorInterface $translator, Calendar $calendar)
    {
        parent::__construct($translator);
        $this->calendar = $calendar;
    }

    /**
     * @Route("/", name="index")
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render(
            'v1/calendar/index.html.twig',
            [
                'calendar' => $this->calendar->createMonth(new DateTime(), $user)->getDays(),
            ]
        );
    }

    /**
     * @Route("/day/{date}", name="day", requirements={"date": "[0-9]{4}-[0-9]{2}-[0-9]{2}"})
     *
     * @param string $date
     * @param TaskListRepository $taskListRepository
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function day(string $date, TaskListRepository $taskListRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $taskLists = $taskListRepository->getByDates($user, new DateTime($date), new DateTime($date));

        return $this->render(
            'v1/calendar/day.html.twig',
            [
                'task_lists' => $taskLists,
                'archive_item_forms' => $this->getArchiveListFormsViews($taskLists),
                'load_more_link' => '',
            ]
        );
    }
}
