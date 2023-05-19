<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Question;

#[AsCommand(
    name: 'app:generate-test-questions',
    description: 'Generate 100 test questions.',
    hidden: false,
)]
class GenerateTestQuestionsCommand extends Command
{
    protected static $defaultName = 'app:generate-test-questions';

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Generate 100 Test Questions')
            ->setHelp('This command generates 100 Test Questions with randomized answers and corresponding correct answers.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Generating Test Questions...');

        for ($i = 1; $i <= 100; $i++) {
            $correctAnswer = rand(1, 4);
            $questionText = 'TestQuestion ' . $i . ' (ans. ' . $correctAnswer . ')';

            $testQuestion = new Question();
            $testQuestion->setQuestion($questionText);
            $testQuestion->setAnswer1('Answer 1');
            $testQuestion->setAnswer2('Answer 2');
            $testQuestion->setAnswer3('Answer 3');
            $testQuestion->setAnswer4('Answer 4');
            $testQuestion->setCorrectAnswer($correctAnswer);

            $this->entityManager->persist($testQuestion);
        }

        $this->entityManager->flush();

        $output->writeln('Test Questions generated successfully.');

        return Command::SUCCESS;
    }
}
