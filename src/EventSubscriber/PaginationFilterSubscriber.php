<?php

namespace App\EventSubscriber;

use Knp\Component\Pager\Event\BeforeEvent;
use Knp\Component\Pager\Event\ItemsEvent;
use Knp\Component\Pager\Event\Subscriber\Filtration\Doctrine\ORM\Query\WhereWalker;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PaginationFilterSubscriber implements EventSubscriberInterface
{
    public function onBeforePagination(BeforeEvent $paginationEvent)
    {
        $request = $paginationEvent->getRequest();

        if ($request->query->get('filterValue')) {
            $request->query->set('filterValue',
                sprintf('%%%s%%', trim($request->query->get('filterValue')))
            );
        }
    }

    public function onItemsPagination(ItemsEvent $event)
    {
        $event->target
            ->setHint(WhereWalker::HINT_PAGINATOR_FILTER_CASE_INSENSITIVE, true);
    }

    public static function getSubscribedEvents()
    {
        return [
            'knp_pager.before' => 'onBeforePagination',
            'knp_pager.items' => ['onItemsPagination', 1],
        ];
    }
}
