<?php

namespace App\Service;

use App\Entity\TaskAttachment;
use App\Entity\Tache;
use App\Entity\Utilisateur;
use App\Repository\TaskAttachmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class TaskAttachmentService
{
    private const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10 MB
    private const ALLOWED_MIME_TYPES = [
        // Images
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        // Documents
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        // Archives
        'application/zip',
        'application/x-rar-compressed',
    ];

    public function __construct(
        private EntityManagerInterface $em,
        private TaskAttachmentRepository $taskAttachmentRepository,
        private string $uploadDirectory
    ) {
    }

    /**
     * Upload a file and create attachment record
     */
    public function uploadFile(Tache $task, Utilisateur $user, UploadedFile $file): TaskAttachment
    {
        // Validate file size
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new \Exception('Le fichier est trop volumineux. Taille maximale: 10 MB');
        }

        // Validate MIME type
        if (!in_array($file->getMimeType(), self::ALLOWED_MIME_TYPES)) {
            throw new \Exception('Type de fichier non autorisé');
        }

        // Generate unique filename
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        // Move file to upload directory
        $file->move($this->uploadDirectory, $newFilename);

        // Create attachment record
        $attachment = new TaskAttachment();
        $attachment->setTache($task);
        $attachment->setUploadedBy($user);
        $attachment->setFilename($file->getClientOriginalName());
        $attachment->setFilepath($newFilename);
        $attachment->setFilesize($file->getSize());
        $attachment->setMimeType($file->getMimeType());

        $this->em->persist($attachment);
        $this->em->flush();

        return $attachment;
    }

    /**
     * Delete a file and its record
     */
    public function deleteFile(TaskAttachment $attachment): void
    {
        // Delete physical file
        $filePath = $this->uploadDirectory . '/' . $attachment->getFilepath();
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Delete database record
        $this->em->remove($attachment);
        $this->em->flush();
    }

    /**
     * Get all attachments for a task
     */
    public function getTaskAttachments(Tache $task): array
    {
        return $this->taskAttachmentRepository->findByTask($task);
    }

    /**
     * Check if file is an image (for preview)
     */
    public function isImageFile(TaskAttachment $attachment): bool
    {
        return str_starts_with($attachment->getMimeType() ?? '', 'image/');
    }

    /**
     * Get file icon based on MIME type
     */
    public function getFileIcon(TaskAttachment $attachment): string
    {
        $mimeType = $attachment->getMimeType();

        return match (true) {
            str_starts_with($mimeType, 'image/') => 'fa-file-image',
            $mimeType === 'application/pdf' => 'fa-file-pdf',
            str_contains($mimeType, 'word') => 'fa-file-word',
            str_contains($mimeType, 'excel') || str_contains($mimeType, 'spreadsheet') => 'fa-file-excel',
            str_contains($mimeType, 'zip') || str_contains($mimeType, 'rar') => 'fa-file-archive',
            default => 'fa-file',
        };
    }

    /**
     * Check if a user can delete an attachment (only uploader or task assignee)
     */
    public function canDelete(TaskAttachment $attachment, Utilisateur $user): bool
    {
        return $attachment->getUploadedBy() === $user || 
               $attachment->getTache()->getAssigne() === $user;
    }

    /**
     * Get total size of attachments for a task
     */
    public function getTotalSize(Tache $task): int
    {
        return $this->taskAttachmentRepository->getTotalSizeByTask($task);
    }

    /**
     * Format file size for display
     */
    public function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $bytes;
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2) . ' ' . $units[$unitIndex];
    }
}
