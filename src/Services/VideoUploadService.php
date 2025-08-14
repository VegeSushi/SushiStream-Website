<?php

namespace Sushi\SushiStreamWebsite\Services;

use MongoDB\Database;

class VideoUploadService
{
    public function __construct(Database $db, AuthService $authService, string $uploadDir)
    {
        $this->db = $db;
        $this->videos = $db->videos;
        $this->authService = $authService;
        $this->uploadDir = rtrim($uploadDir, DIRECTORY_SEPARATOR);
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /**
     * Handles MJPEG video upload.
     *
     * @param array  $file  The $_FILES['file'] array for the uploaded video.
     * @param string $title The title of the video.
     * @return string|false Returns inserted video ID on success, false on failure.
     */
    public function uploadVideo(array $file, string $title)
    {
        $username = $this->authService->getLoggedInUsername();
        if (!$this->authService->isAuthorized() || !$username) {
            error_log('Upload failed: user not authorized');
            return false;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            error_log('Upload failed: PHP file error code ' . $file['error']);
            return false;
        }

        $videoDir = $this->uploadDir . DIRECTORY_SEPARATOR . 'user-content' . DIRECTORY_SEPARATOR . 'videos';
        if (!is_dir($videoDir) && !mkdir($videoDir, 0775, true)) {
            error_log('Upload failed: could not create videos directory');
            return false;
        }

        // Save uploaded file with original extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $uniqueBase = uniqid('video_', true);
        $originalPath = $videoDir . DIRECTORY_SEPARATOR . $uniqueBase . '.' . $extension;

        if (!move_uploaded_file($file['tmp_name'], $originalPath)) {
            error_log('Upload failed: could not move uploaded file');
            return false;
        }

        // Convert to MJPEG using ffmpeg
        $mjpegFilename = $uniqueBase . '.mjpeg';
        $mjpegPath = $videoDir . DIRECTORY_SEPARATOR . $mjpegFilename;
        $ffmpegCmd = sprintf(
            'ffmpeg -y -i %s -vf "scale=240:135,fps=30" -pix_fmt yuvj420p -c:v mjpeg -q:v 5 -an -f mjpeg %s 2>&1',
            escapeshellarg($originalPath),
            escapeshellarg($mjpegPath)
        );

        exec($ffmpegCmd, $output, $returnVar);
        if ($returnVar !== 0 || !file_exists($mjpegPath)) {
            error_log('FFmpeg conversion failed: ' . implode("\n", $output));
            unlink($originalPath);
            return false;
        }

        // Delete the original uploaded file
        unlink($originalPath);

        $videoUrl = $this->baseUrl . '/user-content/videos/' . $mjpegFilename;

        try {
            $result = $this->videos->insertOne([
                'title'       => $title,
                'filename'    => $mjpegFilename,
                //'path'        => $mjpegPath,
                'url'         => $videoUrl,
                'size'        => filesize($mjpegPath),
                'uploader'    => $username,
                'uploaded_at' => new \MongoDB\BSON\UTCDateTime()
            ]);
        } catch (\Exception $e) {
            error_log('MongoDB insert failed: ' . $e->getMessage());
            unlink($mjpegPath);
            return false;
        }

        return (string)$result->getInsertedId();
    }
}
