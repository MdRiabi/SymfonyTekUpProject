<?php

namespace App\Service;

use App\Entity\TaskNote;
use App\Entity\Tache;
use App\Entity\Utilisateur;
use App\Repository\TaskNoteRepository;
use Doctrine\ORM\EntityManagerInterface;

class TaskNoteService
{
    public function __construct(
        private EntityManagerInterface $em,
        private TaskNoteRepository $taskNoteRepository
    ) {
    }

    /**
     * Create a new work log note
     */
    public function createNote(Tache $task, Utilisateur $user, string $content): TaskNote
    {
        $note = new TaskNote();
        $note->setTache($task);
        $note->setAuteur($user);
        $note->setContenu($content);

        $this->em->persist($note);
        $this->em->flush();

        return $note;
    }

    /**
     * Update an existing note
     */
    public function updateNote(TaskNote $note, string $content): TaskNote
    {
        $note->setContenu($content);
        $this->em->flush();

        return $note;
    }

    /**
     * Delete a note
     */
    public function deleteNote(TaskNote $note): void
    {
        $this->em->remove($note);
        $this->em->flush();
    }

    /**
     * Get all notes for a task
     */
    public function getTaskNotes(Tache $task): array
    {
        return $this->taskNoteRepository->findByTask($task);
    }

    /**
     * Get recent notes by a user
     */
    public function getRecentNotesByUser(Utilisateur $user, int $limit = 10): array
    {
        return $this->taskNoteRepository->findRecentByUser($user, $limit);
    }

    /**
     * Count notes for a task
     */
    public function countTaskNotes(Tache $task): int
    {
        return $this->taskNoteRepository->countByTask($task);
    }

    /**
     * Check if a user can edit a note (only the author can edit)
     */
    public function canEdit(TaskNote $note, Utilisateur $user): bool
    {
        return $note->getAuteur() === $user;
    }

    /**
     * Check if a user can delete a note (only the author can delete)
     */
    public function canDelete(TaskNote $note, Utilisateur $user): bool
    {
        return $note->getAuteur() === $user;
    }
}
