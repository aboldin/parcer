<?php

use App\Match;
use App\League;
use SleepingOwl\Admin\Model\ModelConfiguration;

AdminSection::registerModel(Match::class, function (ModelConfiguration $model) {
    $model->setTitle('Matches');

    $model->disableDeleting();
    $model->onDisplay(function () {
        $display = AdminDisplay::datatables()->setHtmlAttribute('class', 'table-primary');
        $display->setOrder([[1, 'desc']]);
        $display->setColumns([
            AdminColumn::link('title')->setLabel('Title'),
            AdminColumn::relatedLink('league.title')
                ->setLabel('League'),
        ]);
        return $display;
    });
})
    ->addMenuPage(Match::class, 0)
    ->setPriority(2)
    ->setIcon('fa fa-bank');
