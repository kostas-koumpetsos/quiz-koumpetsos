<?php

namespace App\Controller;

use App\Entity\PlotQuestion;
use App\Repository\PlotQuestionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\FunctionEvaluator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Psr\Log\LoggerInterface;

#[Route('/plotter')]
class PlotterController extends AbstractController
{
    #[Route('/', name: 'app_plotter')]
    public function index(Request $request, PlotQuestionRepository $plotQuestionRepository, FunctionEvaluator $functionEvaluator, LoggerInterface $logger): Response
    {
        // Check if the quiz is already generated and stored in the session
        $N_QUESTIONS = 3;
        $points = 33;

        if (!$request->getSession()->has('plotter')) {
            $total_question_number = $plotQuestionRepository->count([]);
            $starting_id = 1;
            $question_index_pool = range($starting_id, $starting_id + $total_question_number-1);
            $logger->debug('POOL'.json_encode($question_index_pool));
            $random_idx = array_rand($question_index_pool, $N_QUESTIONS);
            $logger->debug('RIDX'.json_encode($random_idx));

            $question_ids = array();
            foreach($random_idx as $ri)
                $question_ids[] = $question_index_pool[$ri];

            $logger->debug('QIDS'.json_encode($question_ids));
            $questions = $plotQuestionRepository->findBy(array('id' => $question_ids)); 
            $logger->debug('Created random plotter: '.json_encode($questions));

            // Store the quiz in the session
            $request->getSession()->set('plotter', $questions);

            // create placeholders for answers
            $answers = array_fill(0, count($questions), null);
            $request->getSession()->set('plotter_results', $answers);
        } else {
            // Retrieve the quiz from the session
            $questions = $request->getSession()->get('plotter');
        }

        // Get the current question index from the session
        $currentQuestionIndex = $request->getSession()->get('plotterCurrentQuestionIndex', 0);
        $request->getSession()->set('plotterCurrentQuestionIndex', $currentQuestionIndex);
        // $currentQuestionIndex = 2;

        // Check if the quiz is completed
        if ($currentQuestionIndex >= count($questions)) {
            // Quiz completed, redirect to a summary page or any other desired action
            return $this->redirectToRoute('app_plotter_completed');
        }

        // Get the current question based on the index
        $currentQuestion = $questions[$currentQuestionIndex];
        $domain = $functionEvaluator->createDomain(
            $currentQuestion->getDomainStart(), 
            $currentQuestion->getDomainEnd(), 
            $points
        );

        $solution = $functionEvaluator->applyStringFunction($currentQuestion->getFunctionText(), $domain);
        $request->getSession()->set('solution', $solution);
        $mmax = max($solution);
        $mmin = min($solution);
        $mmdiff = $mmax - $mmin;
        $pad = 0.15 * $mmdiff;

        if($pad < 0.1)
            $pad = 1;

        
        // Render the quiz view with the current question
        return $this->render('plotter/index.html.twig', [
            'question' => $currentQuestion,
            'domain' => json_encode($domain),
            'minY' => $mmin - $pad,
            'maxY' => $mmax + $pad, 
            'solution' => json_encode($solution),
            'questionIndex' => $currentQuestionIndex,
            'totalQuestions' => count($questions), 
        ]); 
    }

    #[Route('/submit', name: 'app_plotter_submit', methods: ['POST'])]
    public function submit(Request $request, FunctionEvaluator $functionEvaluator, LoggerInterface $logger): Response
    {
        // Get the current question index from the session
        $currentQuestionIndex = $request->getSession()->get('plotterCurrentQuestionIndex', 0);
        $results = $request->getSession()->get('plotter_results');

        $data = $request->toArray();

        $solution = $request->getSession()->get('solution');
        // Get the user's answer from the submitted form data
        $userAnswer = $data['user_answer'];

        // Validate and process the user's answer as needed
        $result = $functionEvaluator->getSimilarityResult($userAnswer, $solution);

        $results[$currentQuestionIndex] = $result;
        $request->getSession()->set('plotter_results', $results);
        return new JsonResponse($result);

        // $request->getSession()->set('plotter_results', $answers);

        // Increment the question index for the next question
        // $currentQuestionIndex++;

        // Update the session with the new question index
        // $request->getSession()->set('plotterCurrentQuestionIndex', $currentQuestionIndex);
    }
    #
    #[Route('/next_plot', name: 'app_plotter_next')]
    public function next_plot(Request $request, LoggerInterface $logger): Response
    {
        $currentQuestionIndex = $request->getSession()->get('plotterCurrentQuestionIndex', 0);
        $currentQuestionIndex++;

        // Update the session with the new question index
        $request->getSession()->set('plotterCurrentQuestionIndex', $currentQuestionIndex);

        return $this->redirectToRoute('app_plotter');
    }

    #[Route('/completed', name: 'app_plotter_completed')]
    public function completed(Request $request, LoggerInterface $logger): Response
    {
        $questions = $request->getSession()->get('plotter');
        $results = $request->getSession()->get('plotter_results');

        if(is_null($results)) {
            $request->getSession()->clear();
            return $this->redirectToRoute('new_plotter');
        }

        // calculate scores
        $score = 0;
        $logger->debug(json_encode($results));
        foreach($results as $result) {
            if($result['success']) {
                $score++;
            }

        }

        return $this->render('plotter/completed.html.twig', [
            'questions' => $questions,
            'results' => $results,
            'score' => $score,
            'totalQuestions' => count($questions),
        ]);
    }
    #
    #[Route('/new_plotter', name: 'app_new_plotter')]
    public function newPlotter(Request $request, LoggerInterface $logger): Response
    {
        $request->getSession()->remove('plotter_results');
        $request->getSession()->remove('plotter');
        $request->getSession()->remove('plotterCurrentQuestionIndex');

        return $this->redirectToRoute('app_plotter');
    }
}
