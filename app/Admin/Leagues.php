<?php

use App\Match;
use App\League;
use SleepingOwl\Admin\Model\ModelConfiguration;

AdminSection::registerModel(League::class, function (ModelConfiguration $model) {
    $model->setTitle('Leagues');
    // Display
    $model->disableDeleting();
    $model->onDisplay(function () {
        $display = AdminDisplay::datatables()->setHtmlAttribute('class', 'table-primary');
        $display->setOrder([[1, 'desc']]);
        $display->setColumns([
            AdminColumn::link('title')->setLabel('Title')->setWidth('400px'),
        ]);
        return $display;
    });
})
    ->addMenuPage(League::class, 0)
    ->setPriority(1)
    ->setIcon('fa fa-globe');