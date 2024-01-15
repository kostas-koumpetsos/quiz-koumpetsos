<?php

namespace App\Entity;

use App\Repository\PlotQuestionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlotQuestionRepository::class)]
class PlotQuestion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $function_text = null;

    #[ORM\Column]
    private ?float $domain_start = null;

    #[ORM\Column]
    private ?float $domain_end = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFunctionText(): ?string
    {
        return $this->function_text;
    }

    public function setFunctionText(string $function_text): self
    {
        $this->function_text = $function_text;

        return $this;
    }

    public function getDomainStart(): ?float
    {
        return $this->domain_start;
    }

    public function setDomainStart(float $domain_start): self
    {
        $this->domain_start = $domain_start;

        return $this;
    }

    public function getDomainEnd(): ?float
    {
        return $this->domain_end;
    }

    public function setDomainEnd(float $domain_end): self
    {
        $this->domain_end = $domain_end;

        return $this;
    }
}
