<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class TaskDTO
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    public string $title;

    #[Assert\Length(max: 1000)]
    public ?string $description = null;

    public bool $completed = false;

    #[Assert\Range(min: 1, max: 5)]
    public ?int $priority = null;
}
