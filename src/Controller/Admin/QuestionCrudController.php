<?php

namespace App\Controller\Admin;

use App\Entity\Question;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

class QuestionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Question::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $previewQuestion = Action::new('previewQuestion', 'Preview Qusetion')
            ->linkToCrudAction('previewQuestion');


        return $actions
            ->add(Crud::PAGE_INDEX, $previewQuestion);
    }

    public function previewQuestion(AdminContext $context)
    {
        $question = $context->getEntity()->getInstance();

        return $this->render('question/show.html.twig', [
            'question' => $question,
        ]);
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
