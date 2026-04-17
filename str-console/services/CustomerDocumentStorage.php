<?php

declare(strict_types=1);

final class CustomerDocumentStorage
{
    private const MAX_BYTES = 8388608; // 8 MiB

    /** @var list<string> */
    private const ALLOWED_EXT = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];

    public static function baseDir(): string
    {
        return STR_CONSOLE_ROOT . '/storage/uploads/customers';
    }

    /**
     * @param array{name: string, type: string, tmp_name: string, error: int, size: int} $file
     * @return array{relative_path: string, mime: string, size: int, original_name: string}
     */
    public static function store(int $customerId, array $file): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            throw new InvalidArgumentException('No file uploaded.');
        }
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Upload failed.');
        }

        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > self::MAX_BYTES) {
            throw new InvalidArgumentException('File must be between 1 byte and 8 MB.');
        }

        $original = (string) ($file['name'] ?? 'document');
        $original = str_replace(["\0", '/', '\\'], '', $original);
        if ($original === '') {
            $original = 'document';
        }

        $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
        if (!in_array($ext, self::ALLOWED_EXT, true)) {
            throw new InvalidArgumentException('Allowed types: PDF, JPG, PNG, WebP.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            throw new RuntimeException('Invalid upload.');
        }
        $mime = $finfo->file($tmp) ?: 'application/octet-stream';
        $allowedMimes = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/webp',
        ];
        if (!in_array($mime, $allowedMimes, true)) {
            throw new InvalidArgumentException('File content does not match an allowed type.');
        }

        $dir = self::baseDir() . '/' . $customerId;
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException('Could not create storage directory.');
        }

        $safeBase = bin2hex(random_bytes(16)) . '.' . $ext;
        $absolute = $dir . '/' . $safeBase;
        if (!move_uploaded_file($tmp, $absolute)) {
            throw new RuntimeException('Could not save file.');
        }

        $relative = 'customers/' . $customerId . '/' . $safeBase;

        return [
            'relative_path' => $relative,
            'mime' => $mime,
            'size' => $size,
            'original_name' => $original,
        ];
    }

    public static function absolutePathFromRelative(string $relative): string
    {
        $relative = str_replace(['..', '\\'], '', $relative);
        return STR_CONSOLE_ROOT . '/storage/uploads/' . ltrim($relative, '/');
    }
}
