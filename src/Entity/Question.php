<?php

namespace App\Entity;

use App\Repository\QuestionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
class Question
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $question = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $answer_1 = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $answer_2 = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $answer_3 = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $answer_4 = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $correct_answer = null;


    public function __toString(): string 
    {
        return $this->question;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestion(): ?string
    {
        return $this->question;
    }

    public function setQuestion(string $question): self
    {
        $this->question = $question;

        return $this;
    }

    public function getAnswer1(): ?string
    {
        return $this->answer_1;
    }

    public function setAnswer1(string $answer_1): self
    {
        $this->answer_1 = $answer_1;

        return $this;
    }

    public function getAnswer2(): ?string
    {
        return $this->answer_2;
    }

    public function setAnswer2(string $answer_2): self
    {
        $this->answer_2 = $answer_2;

        return $this;
    }

    public function getAnswer3(): ?string
    {
        return $this->answer_3;
    }

    public function setAnswer3(string $answer_3): self
    {
        $this->answer_3 = $answer_3;

        return $this;
    }

    public function getAnswer4(): ?string
    {
        return $this->answer_4;
    }

    public function setAnswer4(string $answer_4): self
    {
        $this->answer_4 = $answer_4;

        return $this;
    }

    public function getCorrectAnswer(): ?int
    {
        return $this->correct_answer;
    }

    public function setCorrectAnswer(int $correct_answer): self
    {
        $this->correct_answer = $correct_answer;

        return $this;
    }
}
