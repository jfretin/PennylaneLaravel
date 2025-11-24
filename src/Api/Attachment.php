<?php

namespace Ashraam\PennylaneLaravel\Api;

class Attachment extends BaseApi
{
    protected $defaultNamespace = self::API_NAMESPACE_V2;

    /**
     * Upload a new attachment.
     *
     * @param string $file Base64 content, raw binary string, local path or remote URL.
     * @param string|null $filename Optional filename hint
     * @return array
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public function create(string $file, ?string $filename = null): array
    {
        [$contents, $resolvedFilename, $contentType] = $this->prepareAttachmentFile($file, $filename);
        $effectiveFilename = $resolvedFilename ?: $this->generateAttachmentFilename();
        $contentType = $contentType ?: $this->guessMimeTypeFromExtension($effectiveFilename) ?: 'application/octet-stream';

        $multipart = [[
            'name' => 'file',
            'contents' => $contents,
            'filename' => $effectiveFilename,
            'headers' => [
                'Content-Type' => $contentType,
            ],
        ]];

        if (!empty($resolvedFilename)) {
            $multipart[] = [
                'name' => 'filename',
                'contents' => $resolvedFilename,
            ];
        }

        $response = $this->client->request('post', $this->getNamespace() . "file_attachments", [
            'multipart' => $multipart,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Normalize the file payload for multipart/form-data requests.
     *
     * @param string $file
     * @param string|null $filename
     * @return array{0:resource|string,1:?string,2:?string}
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    protected function prepareAttachmentFile(string $file, ?string $filename = null): array
    {
        if (is_file($file)) {
            $guessedFilename = $filename ?? basename($file);
            $mime = $this->detectMimeTypeFromFile($file) ?: $this->guessMimeTypeFromExtension($guessedFilename);
            $contents = @file_get_contents($file);
            if ($contents !== false) {
                return [$this->toAttachmentStream($contents), $guessedFilename, $mime];
            }
        }

        if (filter_var($file, FILTER_VALIDATE_URL)) {
            $remoteName = $this->extractFilenameFromUrl($file);
            $guessedFilename = $filename ?? $remoteName;
            $mime = $this->guessMimeTypeFromExtension($guessedFilename);
            $handle = @fopen($file, 'r');
            if ($handle !== false) {
                return [$handle, $guessedFilename, $mime];
            }
            $contents = @file_get_contents($file);
            if ($contents !== false) {
                return [$this->toAttachmentStream($contents), $guessedFilename, $mime];
            }
        }

        $decoded = $this->decodeAttachmentBase64($file);
        if ($decoded !== null) {
            $mime = $decoded['mime'] ?? $this->guessMimeTypeFromExtension($filename);
            return [$this->toAttachmentStream($decoded['data']), $filename, $mime];
        }

        return [$this->toAttachmentStream($file), $filename, $this->guessMimeTypeFromExtension($filename)];
    }

    /**
     * Decode a base64 string (with optional data URI prefix).
     *
     * @param string $value
     * @return array{data:string,mime:?string}|null
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    private function decodeAttachmentBase64(string $value): ?array
    {
        if ($value === '') {
            return null;
        }

        $mime = null;
        if (preg_match('#^data:([^;]+);base64,#', $value, $matches)) {
            $mime = $matches[1];
        }
        $clean = preg_replace('#^data:[^;]+;base64,#', '', $value);
        if ($clean === null) {
            $clean = $value;
        }
        $clean = trim($clean);
        if ($clean === '') {
            return null;
        }

        $decoded = base64_decode($clean, true);
        if ($decoded === false) {
            return null;
        }

        return [
            'data' => $decoded,
            'mime' => $mime,
        ];
    }

    /**
     * Best-effort filename extraction from a URL path.
     *
     * @param string $url
     * @return string|null
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    protected function extractFilenameFromUrl(string $url): ?string
    {
        $path = parse_url($url, PHP_URL_PATH);
        if (!$path) {
            return null;
        }

        $name = basename($path);
        return $name !== '' ? $name : null;
    }

    /**
     * Convert arbitrary string contents into a stream for multipart upload.
     *
     * @param string $contents
     * @return resource|string
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    protected function toAttachmentStream(string $contents)
    {
        $resource = fopen('php://temp', 'r+');
        if ($resource === false) {
            return $contents;
        }
        fwrite($resource, $contents);
        rewind($resource);

        return $resource;
    }

    /**
     * Generate a fallback filename when none is supplied.
     *
     * @return string
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    protected function generateAttachmentFilename(): string
    {
        return 'attachment_' . date('Ymd_His') . '.bin';
    }

    /**
     * Try to guess MIME type from filename extension.
     *
     * @param string|null $filename
     * @return string|null
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    protected function guessMimeTypeFromExtension(?string $filename): ?string
    {
        if (!$filename) {
            return null;
        }
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return [
            'pdf'  => 'application/pdf',
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            'bmp'  => 'image/bmp',
            'tif'  => 'image/tiff',
            'tiff' => 'image/tiff',
        ][$extension] ?? null;
    }

    /**
     * Detect MIME type using the filesystem when possible.
     *
     * @param string $filePath
     * @return string|null
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    protected function detectMimeTypeFromFile(string $filePath): ?string
    {
        if (!function_exists('mime_content_type')) {
            return null;
        }
        $mime = @mime_content_type($filePath);
        if ($mime === false) {
            return null;
        }
        return $mime;
    }
}
