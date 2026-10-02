<?php

namespace App\Services;

use Closure;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Throwable;
use ZipArchive;

class InvitationTemplateService
{
    private const ALLOWED_EXTENSIONS = [
        'html', 'css', 'js', 'jpg', 'jpeg', 'png', 'webp', 'gif', 'svg',
        'mp3', 'wav', 'ogg', 'mp4', 'webm', 'woff', 'woff2', 'ttf', 'json',
    ];

    private const MAX_ARCHIVE_ENTRIES = 500;

    private const MAX_ARCHIVE_FILE_BYTES = 25 * 1024 * 1024;

    private const MAX_ARCHIVE_TOTAL_BYTES = 100 * 1024 * 1024;

    /** @param array<int, array{file: UploadedFile, path: string}> $uploads */
    public function installTemplate(string $slug, ?UploadedFile $archive, array $uploads): void
    {
        $stagingDirectory = $archive ? $this->extractArchive($archive) : $this->temporaryPath();
        $destination = $this->invitationDirectory($slug);

        if (file_exists($destination) || is_link($destination)) {
            $this->removeTree($stagingDirectory);
            throw ValidationException::withMessages(['slug' => 'Folder undangan sudah tersedia.']);
        }

        try {
            if (! is_dir($stagingDirectory) && ! mkdir($stagingDirectory, 0755, true) && ! is_dir($stagingDirectory)) {
                throw new RuntimeException('Folder staging template gagal dibuat.');
            }

            $fileChanges = [];
            $this->writeUploads($stagingDirectory, $uploads, $fileChanges, false);

            if (! rename($stagingDirectory, $destination)) {
                throw new RuntimeException('Template undangan gagal dipasang.');
            }
        } catch (Throwable $exception) {
            $this->removeTree($stagingDirectory);
            throw $exception;
        }
    }

    /** @param array<int, array{file: UploadedFile, path: string}> $uploads */
    public function synchronize(
        string $oldSlug,
        string $newSlug,
        ?UploadedFile $archive,
        array $uploads,
        Closure $persist,
    ): void {
        $stagingDirectory = $archive ? $this->extractArchive($archive) : null;
        $sourceDirectory = $this->invitationDirectory($oldSlug);
        $targetDirectory = $this->invitationDirectory($newSlug);
        $sourceExists = is_dir($sourceDirectory);
        $directoryNeedsMove = $oldSlug !== $newSlug;
        $directoryNeedsBackup = $archive !== null || $directoryNeedsMove;
        $backupDirectory = null;
        $fileChanges = [];
        $targetCreated = false;
        $archiveInstalled = false;
        $directoryMoved = false;
        $sourceBackedUp = false;

        try {
            if (is_link($sourceDirectory) || (file_exists($sourceDirectory) && ! $sourceExists)) {
                throw ValidationException::withMessages(['template_zip' => 'Folder template lama tidak aman.']);
            }
            if ($directoryNeedsMove && (file_exists($targetDirectory) || is_link($targetDirectory))) {
                throw ValidationException::withMessages(['slug' => 'Folder tujuan sudah digunakan.']);
            }

            if (($directoryNeedsBackup || $directoryNeedsMove) && $sourceExists) {
                $backupDirectory = $this->temporaryPath();
                if (! rename($sourceDirectory, $backupDirectory)) {
                    throw new RuntimeException('Folder template lama gagal dipindahkan.');
                }
                $sourceBackedUp = true;
            }

            if ($stagingDirectory !== null) {
                if (! rename($stagingDirectory, $targetDirectory)) {
                    throw new RuntimeException('Template baru gagal dipasang.');
                }
                $stagingDirectory = null;
                $archiveInstalled = true;
                $targetCreated = true;
            } elseif ($directoryNeedsMove && $sourceBackedUp) {
                if (! rename($backupDirectory, $targetDirectory)) {
                    throw new RuntimeException('Folder template gagal mengikuti slug baru.');
                }
                $backupDirectory = null;
                $sourceBackedUp = false;
                $directoryMoved = true;
                $targetCreated = true;
            }

            if ($uploads !== []) {
                if (! is_dir($targetDirectory)) {
                    if (! mkdir($targetDirectory, 0755, true) && ! is_dir($targetDirectory)) {
                        throw new RuntimeException('Folder undangan gagal dibuat.');
                    }
                    $targetCreated = true;
                }

                $this->writeUploads($targetDirectory, $uploads, $fileChanges, ! $archiveInstalled);
            }

            $persist();
        } catch (Throwable $exception) {
            $this->restoreUploads($targetDirectory, $fileChanges);

            if ($archiveInstalled) {
                $this->removeTree($targetDirectory);
            } elseif ($directoryMoved) {
                @rename($targetDirectory, $sourceDirectory);
            } elseif ($targetCreated && is_dir($targetDirectory)) {
                $this->removeTree($targetDirectory);
            }

            if ($sourceBackedUp && $backupDirectory !== null && is_dir($backupDirectory)) {
                @rename($backupDirectory, $sourceDirectory);
            }

            throw $exception;
        } finally {
            if ($stagingDirectory !== null && is_dir($stagingDirectory)) {
                $this->removeTree($stagingDirectory);
            }
            if ($backupDirectory !== null && is_dir($backupDirectory)) {
                $this->removeTree($backupDirectory);
            }
            foreach ($fileChanges as $change) {
                if ($change['backup'] !== null && is_file($change['backup'])) {
                    @unlink($change['backup']);
                }
            }
        }
    }

    public function hasIndex(string $slug): bool
    {
        return $this->resolveFile($slug, 'index.html') !== null;
    }

    public function files(string $slug): array
    {
        $directory = $this->invitationDirectory($slug);
        if (! is_dir($directory) || is_link($directory)) {
            return [];
        }

        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && ! $file->isLink()) {
                $files[] = str_replace(DIRECTORY_SEPARATOR, '/', substr($file->getPathname(), strlen($directory) + 1));
            }
        }

        sort($files);

        return $files;
    }

    public function resolveFile(string $slug, string $relativePath): ?string
    {
        if (! $this->isSafeRelativePath($relativePath)) {
            return null;
        }

        $directory = $this->invitationDirectory($slug);
        $realDirectory = realpath($directory);
        if ($realDirectory === false || is_link($directory)) {
            return null;
        }

        $candidate = $directory.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        $cursor = $directory;
        foreach (explode('/', $relativePath) as $segment) {
            $cursor .= DIRECTORY_SEPARATOR.$segment;
            if (is_link($cursor)) {
                return null;
            }
        }

        $realFile = realpath($candidate);
        if ($realFile === false || ! is_file($realFile) || ! str_starts_with($realFile, $realDirectory.DIRECTORY_SEPARATOR)) {
            return null;
        }

        return $realFile;
    }

    public function deleteFile(string $slug, string $relativePath): bool
    {
        if ($relativePath === 'index.html') {
            return false;
        }

        $path = $this->resolveFile($slug, $relativePath);

        return $path !== null && unlink($path);
    }

    public function deleteTemplate(string $slug): void
    {
        $directory = $this->invitationDirectory($slug);
        if (is_link($directory)) {
            throw new RuntimeException('Folder template tidak aman.');
        }
        if (! file_exists($directory)) {
            return;
        }

        $this->removeTree($directory);
    }

    private function extractArchive(UploadedFile $uploadedArchive): string
    {
        $archive = new ZipArchive;
        if ($archive->open($uploadedArchive->getRealPath()) !== true) {
            throw ValidationException::withMessages(['template_zip' => 'Arsip ZIP tidak dapat dibuka.']);
        }

        $stagingDirectory = $this->temporaryPath();
        try {
            $entries = $this->validateArchive($archive);
            if (! mkdir($stagingDirectory, 0755, true) && ! is_dir($stagingDirectory)) {
                throw new RuntimeException('Folder staging template gagal dibuat.');
            }

            foreach ($entries as $entry) {
                $target = $stagingDirectory.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $entry['path']);
                if ($entry['directory']) {
                    if (! is_dir($target) && ! mkdir($target, 0755, true) && ! is_dir($target)) {
                        throw new RuntimeException('Folder di dalam ZIP gagal dibuat.');
                    }

                    continue;
                }

                $parent = dirname($target);
                if (! is_dir($parent) && ! mkdir($parent, 0755, true) && ! is_dir($parent)) {
                    throw new RuntimeException('Folder file template gagal dibuat.');
                }

                $input = $archive->getStream($entry['name']);
                $output = fopen($target, 'xb');
                if ($input === false || $output === false) {
                    if (is_resource($input)) {
                        fclose($input);
                    }
                    throw new RuntimeException('File di dalam ZIP gagal dibaca.');
                }

                $written = stream_copy_to_stream($input, $output, $entry['size'] + 1);
                fclose($input);
                fclose($output);
                if ($written !== $entry['size']) {
                    throw new RuntimeException('Ukuran file hasil ekstraksi tidak sesuai.');
                }
                chmod($target, 0644);
            }

            $archive->close();

            return $stagingDirectory;
        } catch (Throwable $exception) {
            $archive->close();
            $this->removeTree($stagingDirectory);
            if ($exception instanceof ValidationException) {
                throw $exception;
            }

            throw ValidationException::withMessages(['template_zip' => 'Isi ZIP tidak dapat diekstrak dengan aman.']);
        }
    }

    /** @return array<int, array{name: string, path: string, size: int, directory: bool}> */
    private function validateArchive(ZipArchive $archive): array
    {
        if ($archive->numFiles < 1 || $archive->numFiles > self::MAX_ARCHIVE_ENTRIES) {
            throw ValidationException::withMessages(['template_zip' => 'ZIP harus berisi antara 1 dan 500 entry.']);
        }

        $entries = [];
        $seenPaths = [];
        $totalBytes = 0;
        $hasIndex = false;

        for ($index = 0; $index < $archive->numFiles; $index++) {
            $name = $archive->getNameIndex($index);
            $stat = $archive->statIndex($index);
            if (! is_string($name) || $stat === false || ! $this->isSafeRelativePath($name)) {
                throw ValidationException::withMessages(['template_zip' => 'ZIP berisi path file yang tidak aman.']);
            }

            $directory = str_ends_with($name, '/');
            $path = rtrim($name, '/');
            $normalizedKey = mb_strtolower($path);
            if (isset($seenPaths[$normalizedKey])) {
                throw ValidationException::withMessages(['template_zip' => 'ZIP berisi nama file duplikat.']);
            }
            $seenPaths[$normalizedKey] = true;

            $operationsSystem = 0;
            $attributes = 0;
            if ($archive->getExternalAttributesIndex($index, $operationsSystem, $attributes)
                && $operationsSystem === ZipArchive::OPSYS_UNIX) {
                $fileType = ($attributes >> 16) & 0170000;
                if (! in_array($fileType, [0, 0100000, 0040000], true)) {
                    throw ValidationException::withMessages(['template_zip' => 'ZIP tidak boleh berisi symbolic link atau file khusus.']);
                }
            }

            $size = (int) ($stat['size'] ?? 0);
            if (! $directory) {
                $extension = mb_strtolower(pathinfo($path, PATHINFO_EXTENSION));
                if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
                    throw ValidationException::withMessages(['template_zip' => "Ekstensi file .{$extension} tidak diizinkan."]);
                }
                if ($size > self::MAX_ARCHIVE_FILE_BYTES) {
                    throw ValidationException::withMessages(['template_zip' => 'Ukuran salah satu file ZIP melebihi 25 MB.']);
                }

                $totalBytes += $size;
                if ($totalBytes > self::MAX_ARCHIVE_TOTAL_BYTES) {
                    throw ValidationException::withMessages(['template_zip' => 'Total isi ZIP melebihi 100 MB.']);
                }
                $hasIndex = $hasIndex || $path === 'index.html';
            }

            $entries[] = ['name' => $name, 'path' => $path, 'size' => $size, 'directory' => $directory];
        }

        if (! $hasIndex) {
            throw ValidationException::withMessages(['template_zip' => 'ZIP harus memiliki file index.html di folder utama.']);
        }

        return $entries;
    }

    /** @param array<int, array{file: UploadedFile, path: string}> $uploads
     * @param  array<string, array{backup: ?string}>  $changes
     */
    private function writeUploads(string $root, array $uploads, array &$changes, bool $backupExisting): void
    {
        if (count($uploads) > self::MAX_ARCHIVE_ENTRIES) {
            throw ValidationException::withMessages(['asset_files' => 'Maksimal 500 file dapat diunggah sekaligus.']);
        }

        $validatedUploads = [];
        $seenPaths = [];
        $totalBytes = 0;
        foreach ($uploads as $upload) {
            $file = $upload['file'] ?? null;
            $path = $upload['path'] ?? '';
            if (! $file instanceof UploadedFile || ! $file->isValid() || ! $this->isSafeRelativePath($path)) {
                throw ValidationException::withMessages(['asset_files' => 'File atau path unggahan tidak aman.']);
            }

            $extension = mb_strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
                throw ValidationException::withMessages(['asset_files' => "Ekstensi file .{$extension} tidak diizinkan."]);
            }

            $pathKey = mb_strtolower($path);
            if (isset($seenPaths[$pathKey])) {
                throw ValidationException::withMessages(['asset_files' => 'Dua file unggahan memiliki path yang sama.']);
            }
            $seenPaths[$pathKey] = true;

            $size = (int) $file->getSize();
            if ($size > self::MAX_ARCHIVE_FILE_BYTES) {
                throw ValidationException::withMessages(['asset_files' => 'Ukuran salah satu file melebihi 25 MB.']);
            }
            $totalBytes += $size;
            if ($totalBytes > self::MAX_ARCHIVE_TOTAL_BYTES) {
                throw ValidationException::withMessages(['asset_files' => 'Total ukuran file melebihi 100 MB.']);
            }

            $validatedUploads[] = ['file' => $file, 'path' => $path];
        }

        foreach ($validatedUploads as $upload) {
            $path = $upload['path'];
            $target = $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
            $this->ensureUploadDirectory($root, dirname($path));

            if (is_link($target) || (file_exists($target) && ! is_file($target))) {
                throw ValidationException::withMessages(['asset_files' => 'Path file tujuan tidak aman.']);
            }

            $backup = null;
            if ($backupExisting && is_file($target)) {
                $backup = $this->temporaryPath();
                if (! copy($target, $backup)) {
                    throw new RuntimeException('File lama gagal dicadangkan.');
                }
            }
            $changes[$path] = ['backup' => $backup];

            if (! copy($upload['file']->getRealPath(), $target)) {
                throw new RuntimeException('File unggahan gagal disimpan.');
            }
            chmod($target, 0644);
        }
    }

    /** @param array<string, array{backup: ?string}> $changes */
    private function restoreUploads(string $root, array $changes): void
    {
        foreach (array_reverse($changes, true) as $path => $change) {
            $target = $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
            if ($change['backup'] !== null && is_file($change['backup'])) {
                copy($change['backup'], $target);
            } elseif (is_file($target) && ! is_link($target)) {
                @unlink($target);
            }
        }
    }

    private function ensureUploadDirectory(string $root, string $relativeDirectory): void
    {
        if ($relativeDirectory === '.' || $relativeDirectory === '') {
            return;
        }

        $cursor = $root;
        foreach (explode('/', $relativeDirectory) as $segment) {
            $cursor .= DIRECTORY_SEPARATOR.$segment;
            if (is_link($cursor) || (file_exists($cursor) && ! is_dir($cursor))) {
                throw ValidationException::withMessages(['asset_files' => 'Folder tujuan file tidak aman.']);
            }
            if (! is_dir($cursor) && ! mkdir($cursor, 0755) && ! is_dir($cursor)) {
                throw new RuntimeException('Folder file template gagal dibuat.');
            }
        }
    }

    private function isSafeRelativePath(string $path): bool
    {
        if ($path === '' || str_contains($path, "\0") || str_contains($path, '\\') || str_starts_with($path, '/') || preg_match('/\A[A-Za-z]:/', $path)) {
            return false;
        }

        $segments = explode('/', rtrim($path, '/'));
        foreach ($segments as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..') {
                return false;
            }
        }

        return true;
    }

    private function uploadedAssetName(UploadedFile $asset): string
    {
        $filename = basename(str_replace('\\', '/', $asset->getClientOriginalName()));
        $extension = mb_strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if ($filename === '' || str_starts_with($filename, '.') || ! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw ValidationException::withMessages(['asset_file' => 'Nama atau ekstensi file tidak diizinkan.']);
        }

        return $filename;
    }

    private function invitationDirectory(string $slug): string
    {
        if (! preg_match('/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/', $slug)) {
            throw new RuntimeException('Slug folder undangan tidak valid.');
        }

        return $this->invitationRoot().DIRECTORY_SEPARATOR.$slug;
    }

    private function invitationRoot(): string
    {
        $diskRoot = Storage::disk('public')->path('');
        if (! is_dir($diskRoot) && ! mkdir($diskRoot, 0755, true) && ! is_dir($diskRoot)) {
            throw new RuntimeException('Public storage tidak dapat diakses.');
        }

        $realDiskRoot = realpath($diskRoot);
        if ($realDiskRoot === false) {
            throw new RuntimeException('Public storage tidak dapat diakses.');
        }

        $root = $realDiskRoot.DIRECTORY_SEPARATOR.'invitations';
        if (is_link($root)) {
            throw new RuntimeException('Folder penyimpanan undangan tidak aman.');
        }
        if (! is_dir($root) && ! mkdir($root, 0755, true) && ! is_dir($root)) {
            throw new RuntimeException('Folder penyimpanan undangan gagal dibuat.');
        }

        $realRoot = realpath($root);
        if ($realRoot === false || ! str_starts_with($realRoot, $realDiskRoot.DIRECTORY_SEPARATOR)) {
            throw new RuntimeException('Folder penyimpanan undangan berada di luar public storage.');
        }

        return $realRoot;
    }

    private function temporaryPath(): string
    {
        $temporaryRoot = storage_path('app/private/invitation-templates');
        if (! is_dir($temporaryRoot) && ! mkdir($temporaryRoot, 0755, true) && ! is_dir($temporaryRoot)) {
            throw new RuntimeException('Folder staging template gagal dibuat.');
        }

        return $temporaryRoot.DIRECTORY_SEPARATOR.Str::uuid()->toString();
    }

    private function removeTree(string $path): void
    {
        if (is_link($path) || is_file($path)) {
            @unlink($path);

            return;
        }
        if (! is_dir($path)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );
        foreach ($iterator as $item) {
            if ($item->isLink() || $item->isFile()) {
                @unlink($item->getPathname());
            } elseif ($item->isDir()) {
                @rmdir($item->getPathname());
            }
        }
        @rmdir($path);
    }
}
