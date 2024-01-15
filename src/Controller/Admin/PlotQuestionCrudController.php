<?php

namespace App\Controller\Admin;

use App\Entity\PlotQuestion;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class PlotQuestionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PlotQuestion::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
