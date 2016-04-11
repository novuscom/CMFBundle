<?php
namespace Novuscom\CMFBundle;
final class UserEvents
{
    /**
     * The store.order event is thrown each time an order is created
     * in the system.
     *
     * The event listener receives an
     * Acme\StoreBundle\Event\FilterOrderEvent instance.
     *
     * @var string
     */
    const USER_REGISTER = 'user.register';
}