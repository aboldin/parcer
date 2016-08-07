<?php

use App\Match;
use App\League;
use SleepingOwl\Admin\Model\ModelConfiguration;

AdminSection::registerModel(League::class, function (ModelConfiguration $model) {
    $model->setTitle('Leagues');
    // Display
    $model->onDisplay(function () {
        $display = AdminDisplay::table()->paginate(10);
        $display->setColumns([
            AdminColumn::link('title')->setLabel('Title')->setWidth('400px'),
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
    ->addMenuPage(League::class, 0)
    ->setIcon('fa fa-bank');