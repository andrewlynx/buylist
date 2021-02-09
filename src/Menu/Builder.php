<?php

namespace App\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

final class Builder implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    private $factory;

    /**
     * @param FactoryInterface $factory
     */
    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param array $options
     *
     * @return ItemInterface
     */
    public function createMainMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('main_menu');

        $menu->addChild('menu.lists', ['route' => 'task_list_index']);
        $menu->addChild('menu.lists_shared', ['route' => 'task_list_index_shared']);
        $menu->addChild('menu.archive', ['route' => 'task_list_archive']);
        $menu->addChild('menu.settings', ['route' => 'user_settings']);

        return $menu;
    }
}
