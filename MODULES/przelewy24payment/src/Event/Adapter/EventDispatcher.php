<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Przelewy24 powered by Waynet
 * @copyright Przelewy24
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace Przelewy24\Event\Adapter;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Przelewy24\Event\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as ContractsEventDispatcherInterface;

final class EventDispatcher
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var bool
     */
    private $isLegacyDispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
        $this->isLegacyDispatcher = !$this->dispatcher instanceof ContractsEventDispatcherInterface;
    }

    public function dispatch(Event $event, string $eventName = null): Event
    {
        $eventName = $eventName ?? get_class($event);

        return $this->isLegacyDispatcher
            ? $this->dispatcher->dispatch($eventName, $event)
            : $this->dispatcher->dispatch($event, $eventName);
    }

    public function addListener(string $eventName, callable $listener, int $priority = 0): void
    {
        $this->dispatcher->addListener($eventName, $listener, $priority);
    }

    /**
     * Forwards calls to the inner dispatcher.
     *
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        return $this->dispatcher->$name(...$arguments);
    }
}
