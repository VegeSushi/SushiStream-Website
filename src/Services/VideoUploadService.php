<?php
namespace Sushi\SushiStreamWebsite\Services;

use MongoDB\Database;

class VideoUploadService
{
    private Database $db;
    private $videos;
    private string $uploadDir;
    private $authService;

    public function __construct(Database $db, AuthService $authService, string $uploadDir)
    {
        $this->db = $db;
        $this->videos = $db->videos;
        $this->authService = $authService;
        $this->uploadDir = rtrim($uploadDir, DIRECTORY_SEPARATOR);
    }

    /**
     * Handles video upload and MJPEG preview generation using raw FFmpeg.
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

        $videoDir = $this->uploadDir . DIRECTORY_SEPARATOR . 'videos';
        if (!is_dir($videoDir) && !mkdir($videoDir, 0775, true)) {
            error_log('Upload failed: could not create videos directory');
            return false;
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $uniqueName = uniqid('video_', true) . '.' . $extension;
        $destination = $videoDir . DIRECTORY_SEPARATOR . $uniqueName;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            error_log('Upload failed: could not move uploaded file');
            return false;
        }

        // Generate MJPEG preview using raw FFmpeg
        $previewName = uniqid('preview_', true) . '.mjpeg';
        $previewPath = $videoDir . DIRECTORY_SEPARATOR . $previewName;

        $ffmpegCmd = sprintf(
            '/usr/bin/ffmpeg -i %s -vf "scale=240:135,fps=30" -pix_fmt yuvj420p -c:v mjpeg -q:v 5 -an -f mjpeg %s 2>&1',
            escapeshellarg($destination),
            escapeshellarg($previewPath)
        );

        exec($ffmpegCmd, $output, $returnVar);

        if ($returnVar !== 0) {
            error_log('FFmpeg MJPEG generation failed: ' . implode("\n", $output));
            unlink($destination);
            return false;
        }

        try {
            $result = $this->videos->insertOne([
                'title'        => $title,
                'filename'     => $uniqueName,
                'path'         => $destination,
                'size'         => $file['size'],
                'uploader'     => $username,
                'preview_path' => $previewPath,
                'uploaded_at'  => new \MongoDB\BSON\UTCDateTime()
            ]);
        } catch (\Exception $e) {
            error_log('MongoDB insert failed: ' . $e->getMessage());
            unlink($destination);
            unlink($previewPath);
            return false;
        }

        return (string)$result->getInsertedId();
    }
}
