<?php

use App\Match;
use App\League;
use SleepingOwl\Admin\Model\ModelConfiguration;

AdminSection::registerModel(Match::class, function (ModelConfiguration $model) {
    $model->setTitle('Matches');
    // Display
    $model->onDisplay(function () {
        $display = AdminDisplay::table()->paginate(10);

        $display
            ->setFilters(
                AdminDisplayFilter::field('league_id')->setTitle('Category ID [:value]')
            );
        $display->setColumns([
            AdminColumn::link('title')->setLabel('Title'),
            AdminColumn::relatedLink('league.title')
                ->setLabel('League'),
        ]);

        return $display;
    });
    /*// Create And Edit
    $model->onCreateAndEdit(function() {
        return $form = AdminForm::panel()->addBody(
            AdminFormElement::text('title', 'Title')->required()->unique(),
            AdminFormElement::textarea('address', 'Address')->setRows(2),
            AdminFormElement::text('phone', 'Phone')
        );
        return $form;
    });*/
})
    ->addMenuPage(Match::class, 0)
    ->setIcon('fa fa-bank');
