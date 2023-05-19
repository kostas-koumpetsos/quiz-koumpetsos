<?php

namespace App\Controller;

use App\Entity\Question;
use App\Form\QuestionType;
use App\Repository\QuestionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Psr\Log\LoggerInterface;

#[Route('/quiz')]
class QuizController extends AbstractController
{
    #[Route('/', name: 'app_quiz')]
    public function index(Request $request, QuestionRepository $questionRepository, LoggerInterface $logger): Response
    {
        // Check if the quiz is already generated and stored in the session
        if (!$request->getSession()->has('quiz')) {
            // Generate a new quiz with 10 random questions
            $total_question_number = $questionRepository->count([]);
            $question_index_pool = range(1, $total_question_number);
            $random_idx = array_rand($question_index_pool, 10);

            $question_ids = array();
            foreach($random_idx as $ri)
                $question_ids[] = $question_index_pool[$ri];

            // $question_ids[0] = 2;
            $logger->debug('Created random quiz with ids: '.json_encode($question_ids));
            $questions = $questionRepository->findBy(array('id' => $question_ids));

            // Store the quiz in the session
            $request->getSession()->set('quiz', $questions);

            // create placeholders for answers
            $answers = array_fill(0, count($questions), null);
            $request->getSession()->set('answers', $answers);
        } else {
            // Retrieve the quiz from the session
            $questions = $request->getSession()->get('quiz');
        }

        // Get the current question index from the session
        $currentQuestionIndex = $request->getSession()->get('currentQuestionIndex', 0);

        // Check if the quiz is completed
        if ($currentQuestionIndex >= count($questions)) {
            // Quiz completed, redirect to a summary page or any other desired action
            return $this->redirectToRoute('app_quiz_completed');
        }

        // Get the current question based on the index
        $currentQuestion = $questions[$currentQuestionIndex];

        // Render the quiz view with the current question
        return $this->render('quiz/index.html.twig', [
            'question' => $currentQuestion,
            'questionIndex' => $currentQuestionIndex,
            'totalQuestions' => count($questions),
        ]);
    }

    #[Route('/submit', name: 'app_quiz_submit', methods: ['POST'])]
    public function submit(Request $request, QuestionRepository $questionRepository, LoggerInterface $logger): Response
    {
        // Get the current question index from the session
        $currentQuestionIndex = $request->getSession()->get('currentQuestionIndex', 0);

        // Get the quiz questions
        $questions = $request->getSession()->get('quiz');
        $answers = $request->getSession()->get('answers');


        // Get the current question based on the index
        $currentQuestion = $questions[$currentQuestionIndex];

        // Get the user's answer from the submitted form data
        $userAnswer = $request->request->get('answer');

        // Validate and process the user's answer as needed
        $answers[$currentQuestionIndex] = $userAnswer;
        $logger->debug('User answer:'. $userAnswer);
        $request->getSession()->set('answers', $answers);

        // Increment the question index for the next question
        $currentQuestionIndex++;

        // Update the session with the new question index
        $request->getSession()->set('currentQuestionIndex', $currentQuestionIndex);

        // Redirect to the quiz page to display the next question
        return $this->redirectToRoute('app_quiz');
    }

    #[Route('/completed', name: 'app_quiz_completed')]
    public function completed(Request $request, LoggerInterface $logger): Response
    {
        $questions = $request->getSession()->get('quiz');
        $answers = $request->getSession()->get('answers');

        if(is_null($answers)) {
            $request->getSession()->clear();
            return $this->redirectToRoute('new_quiz');
        }

        // calculate scores
        $score = 0;
        foreach($questions as $i => $question) {
            $userAns = $answers[$i];
            if($userAns == $question->getCorrectAnswer()) {
                $score++;
            }

        }

        return $this->render('quiz/completed.html.twig', [
            'questions' => $questions,
            'answers' => $answers,
            'score' => $score,
            'totalQuestions' => count($questions),
        ]);
    }
    #
    #[Route('/new_quiz', name: 'app_new_quiz')]
    public function newQuiz(Request $request, LoggerInterface $logger): Response
    {
        $request->getSession()->remove('answers');
        $request->getSession()->remove('quiz');
        $request->getSession()->remove('currentQuestionIndex');

        return $this->redirectToRoute('app_quiz');
    }
}
