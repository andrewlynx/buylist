<?php

namespace App\Menu;

use App\Entity\User;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
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
     * @var Request
     */
    private $request;

    /**
     * @param FactoryInterface $factory
     * @param Security $security
     * @param RequestStack $requestStack
     */
    public function __construct(FactoryInterface $factory, Security $security, RequestStack $requestStack)
    {
        $this->factory = $factory;
        $this->security = $security;
        $this->request = $requestStack->getCurrentRequest();
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
            ->setLinkAttribute('class', 'iconly-brokenUser menu-item');
        $menu
            ->addChild('menu.lists_favourites', ['route' => 'task_list_favourites'])
            ->setLinkAttribute('class', 'iconly-brokenHeart menu-item');
        $menu
            ->addChild('menu.archive', ['route' => 'task_list_archive'])
            ->setLinkAttribute('class', 'iconly-brokenBookmark menu-item');
        $menu
            ->addChild('menu.calendar', ['route' => 'calendar_index'])
            ->setLinkAttribute('class', 'iconly-brokenCalendar menu-item');
        $menu
            ->addChild('menu.settings', ['route' => 'user_settings'])
            ->setLinkAttribute('class', 'iconly-brokenSetting menu-item');
        if ($user->hasRole(User::ROLE_ADMIN)) {
            $menu
                ->addChild('Admin', ['route' => 'admin_panel'])
                ->setLinkAttribute('class', 'iconly-brokenStar menu-item');
        }

        // Set active "settings" menu for submenus
        if (in_array($this->request->attributes->get('_route'), ['user_settings', 'user_users'])) {
            ($menu['menu.settings'])->setCurrent(true);
        }

        return $menu;
    }

    /**
     * @param array $options
     *
     * @return ItemInterface
     */
    public function createOptionsMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('options_menu');

        $menu->addChild('menu.credentials', ['route' => 'user_settings']);
        $menu->addChild('menu.users', ['route' => 'user_users']);

        return $menu;
    }
}
