<?php

namespace App\Entity;

use App\Repository\IslamicHolidayRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IslamicHolidayRepository::class)]
class IslamicHoliday
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $nameFr = null;

    #[ORM\Column(length: 255)]
    private ?string $dateHijri = null;

    #[ORM\Column]
    private ?\DateTime $dateGregorian = null;

    #[ORM\Column]
    private ?bool $passed = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getNameFr(): ?string
    {
        return $this->nameFr;
    }

    public function setNameFr(string $nameFr): static
    {
        $this->nameFr = $nameFr;

        return $this;
    }

    public function getDateHijri(): ?string
    {
        return $this->dateHijri;
    }

    public function setDateHijri(string $dateHijri): static
    {
        $this->dateHijri = $dateHijri;

        return $this;
    }

    public function getDateGregorian(): ?\DateTime
    {
        return $this->dateGregorian;
    }

    public function setDateGregorian(\DateTime $dateGregorian): static
    {
        $this->dateGregorian = $dateGregorian;

        return $this;
    }

    public function isPassed(): ?bool
    {
        return $this->passed;
    }

    public function setPassed(bool $passed): static
    {
        $this->passed = $passed;

        return $this;
    }
}
