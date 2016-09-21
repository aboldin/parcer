<?php

use App\Match;
use App\League;
use App\Profit;
use SleepingOwl\Admin\Model\ModelConfiguration;

AdminSection::registerModel(Profit::class, function (ModelConfiguration $model) {
    $model->setTitle('Profits');
    $model->disableDeleting();

    $model->onDisplay(function () {
        $display = AdminDisplay::datatables()->setHtmlAttribute('class', 'table-primary');
        $display->setOrder([[4, 'desc']]);
        $display->setColumns([
            AdminColumn::relatedLink('match.league.sportType.name')->setLabel('Sport'),
            AdminColumn::relatedLink('match.league.title')->setLabel('League'),
            AdminColumn::relatedLink('match.title')->setLabel('Match'),
            AdminColumn::link('type')->setLabel('Bet type'),
            AdminColumn::link('profit')->setLabel('Profit (%)'),
            AdminColumn::link('text')->setLabel('Info'),
            AdminColumn::relatedLink('match.full_link')->setLabel('Link to BMBets'),
        ]);
        return $display;
    });
})
    ->addMenuPage(Profit::class, 0)
    ->setPriority(3)
    ->setIcon('fa fa-credit-card');
