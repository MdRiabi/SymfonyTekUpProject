<?php

namespace App\Twig;

use App\Service\ProjectService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ProjectExtension extends AbstractExtension
{
    public function __construct(
        private ProjectService $projectService
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('configured_projects_count', [$this, 'getConfiguredProjectsCount']),
            new TwigFunction('active_projects_count', [$this, 'getActiveProjectsCount']),
            new TwigFunction('completed_projects_count', [$this, 'getCompletedProjectsCount']),
            new TwigFunction('has_configured_projects', [$this, 'hasConfiguredProjects']),
            new TwigFunction('project_stats', [$this, 'getProjectStats']),
        ];
    }

    public function getConfiguredProjectsCount(): int
    {
        return $this->projectService->getConfiguredProjectsCount();
    }

    public function getActiveProjectsCount(): int
    {
        return $this->projectService->getActiveProjectsCount();
    }

    public function getCompletedProjectsCount(): int
    {
        return $this->projectService->getCompletedProjectsCount();
    }

    public function hasConfiguredProjects(): bool
    {
        return $this->projectService->hasConfiguredProjects();
    }

    public function getProjectStats(): array
    {
        return $this->projectService->getProjectStats();
    }
}
