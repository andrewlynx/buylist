<?php

namespace App\Menu;

use App\Entity\User;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;
use Symfony\Component\Security\Core\Security;

final class Builder implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var Security
     */
    private $security;

    /**
     * @param FactoryInterface $factory
     * @param Security $security
     */
    public function __construct(FactoryInterface $factory, Security $security)
    {
        $this->factory = $factory;
        $this->security = $security;
    }

    /**
     * @param array $options
     *
     * @return ItemInterface
     */
    public function createMainMenu(array $options): ItemInterface
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $menu = $this->factory->createItem('main_menu');

        $menu
            ->addChild('menu.lists', ['route' => 'task_list_index'])
            ->setLinkAttribute('class', 'iconly-brokenDocument menu-item');
        $menu
            ->addChild('menu.lists_shared', ['route' => 'task_list_index_shared'])
            ->setLinkAttribute('class', 'iconly-brokenPaper-Plus menu-item');
        $menu
            ->addChild('menu.archive', ['route' => 'task_list_archive'])
            ->setLinkAttribute('class', 'iconly-brokenWork menu-item');
        $menu
            ->addChild('menu.settings', ['route' => 'user_settings'])
            ->setLinkAttribute('class', 'iconly-brokenSetting menu-item');
        if ($user->hasRole(User::ROLE_ADMIN)) {
            $menu
                ->addChild('Admin', ['route' => 'admin_panel'])
                ->setLinkAttribute('class', 'iconly-brokenSetting menu-item');
        }

        return $menu;
    }
}
