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

        // IMPORTANT: Capture file metadata BEFORE moving the file
        $originalFilename = $file->getClientOriginalName();
        $fileSize = $file->getSize();
        $mimeType = $file->getMimeType();
        
        // Generate unique filename
        $safeFilename = $this->sanitizeFilename(pathinfo($originalFilename, PATHINFO_FILENAME));
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        // Move file to upload directory
        $file->move($this->uploadDirectory, $newFilename);

        // Create attachment record with captured metadata
        $attachment = new TaskAttachment();
        $attachment->setTache($task);
        $attachment->setUploadedBy($user);
        $attachment->setFilename($originalFilename);
        $attachment->setFilepath($newFilename);
        $attachment->setFilesize($fileSize);
        $attachment->setMimeType($mimeType);

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

    /**
     * Sanitize filename by removing special characters and accents
     */
    private function sanitizeFilename(string $filename): string
    {
        // Replace accented characters
        $unwanted_array = [
            'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A',
            'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I',
            'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U',
            'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a',
            'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i',
            'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u',
            'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y'
        ];
        $filename = strtr($filename, $unwanted_array);
        
        // Remove any remaining special characters, keep only alphanumeric, dash, and underscore
        $filename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $filename);
        
        // Convert to lowercase
        $filename = strtolower($filename);
        
        // Remove multiple underscores
        $filename = preg_replace('/_+/', '_', $filename);
        
        // Trim underscores from start and end
        $filename = trim($filename, '_');
        
        // If filename is empty after sanitization, use a default
        return $filename ?: 'file';
    }
}
