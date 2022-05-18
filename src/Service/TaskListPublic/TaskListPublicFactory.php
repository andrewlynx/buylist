<?php

namespace App\Service\TaskListPublic;

use App\Entity\TaskListPublic;
use App\Repository\TaskListPublicRepository;
use App\Service\HashGenerator\HashGenerator;

class TaskListPublicFactory
{
    /**
     * @var TaskListPublicRepository
     */
    private $repo;

    /**
     * @param TaskListPublicRepository $repo
     */
    public function __construct(TaskListPublicRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * @return TaskListPublic
     */
    public function make(): TaskListPublic
    {
        $exist = true;
        while ($exist === true) {
            $id = HashGenerator::generate();
            if ($this->repo->find($id) === null) {
                $exist = false;
            }
        }
        $taskListPublic = new TaskListPublic($id);

        return $taskListPublic;
    }
}
