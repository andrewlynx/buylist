<?php

namespace App\Tests\Entity;

use App\Entity\TaskItem;
use App\Entity\TaskList;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskItemTest extends WebTestCase
{
    public function testEntity()
    {
        $taskList = new TaskList();
        $taskItem = new TaskItem();
        $taskItem
            ->setTaskList($taskList)
            ->setQty('45')
            ->setName('name')
            ->setCompleted(true);

        $this->assertNull($taskItem->getId());
        $this->assertSame($taskList, $taskItem->getTaskList());
        $this->assertSame('45', $taskItem->getQty());
        $this->assertSame('name', $taskItem->getName());
        $this->assertTrue($taskItem->isCompleted());

        $taskItem->incrementQty();
        $this->assertSame('46', $taskItem->getQty());
    }
}
