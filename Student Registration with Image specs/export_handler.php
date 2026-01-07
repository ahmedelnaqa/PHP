<?php
// Data Export Handler for Admin Panel
// Enables batch export of student data as Excel + Images in ZIP format

class DataExporter
{
    private $batchSize;
    private $exportsDir;
    private $imagesDir;

    public function __construct(int $batchSize = 50)
    {
        $this->batchSize = $batchSize;
        $this->exportsDir = __DIR__ . '/exports/';
        $this->imagesDir = __DIR__ . '/uploads/';

        // Ensure directories exist
        if (!is_dir($this->exportsDir)) {
            mkdir($this->exportsDir, 0755, true);
        }
        if (!is_dir($this->imagesDir)) {
            mkdir($this->imagesDir, 0755, true);
        }
    }

    /**
     * Get list of all students
     */
    private function getAllStudents(): array
    {
        require_once __DIR__ . '/config/database.php';
        require_once __DIR__ . '/models/Student.php';

        return Student::all(PHP_INT_MAX, 0);
    }

    /**
     * Chunk students into batches of specified size
     */
    private function getStudentBatches(array $students): array
    {
        return array_chunk($students, $this->batchSize, true);
    }

    /**
     * Create Excel file from student data
     */
    private function createExcelFile(array $students, string $filename): string
    {
        $excelFilename = $this->exportsDir . $filename . '_data.csv';

        $file = fopen($excelFilename, 'w');

        // UTF-8 BOM for Excel
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

        // Write headers
        $headers = [
            'Serial Number',
            'NID',
            'Name (Arabic)',
            'Name (English)',
            'Name on Card',
            'Home Number',
            'Mobile Number',
            'Address (Arabic)',
            'Address (English)',
            'Gender',
            'Birthday',
            'Email',
            'Faculty Name',
            'Created At',
            'Updated At',
            'Image Path'
        ];
        fputcsv($file, $headers);

        // Write data
        $serial = 1;
        foreach ($students as $student) {
            $row = [
                $serial,
                $student->getNid(),
                $student->getNameAr(),
                $student->getNameEn(),
                $student->getNameOnCard(),
                $student->getHomeNumber(),
                $student->getMobileNumber(),
                $student->getAddressAr(),
                $student->getAddressEn(),
                $student->getGender(),
                $student->getBirthday(),
                $student->getEmail(),
                $student->getFacultyName(),
                $student->getCreatedAt(),
                $student->getUpdatedAt(),
                $student->getImagePath()
            ];
            fputcsv($file, $row);
            $serial++;
        }

        fclose($file);
        return $excelFilename;
    }

    /**
     * Get list of image files for students
     */
    private function getStudentImages(array $students): array
    {
        $images = [];
        foreach ($students as $student) {
            $imagePath = $student->getImagePath();
            if ($imagePath && file_exists($this->imagesDir . basename($imagePath))) {
                $images[] = $this->imagesDir . basename($imagePath);
            }
        }
        return $images;
    }

    /**
     * Create ZIP file containing Excel and images
     */
    private function createZipFile(string $excelFile, array $images, string $zipFilename): string
    {
        $zipFilePath = $this->exportsDir . $zipFilename . '.zip';

        // Check if ZipArchive is available
        if (class_exists('ZipArchive')) {
            // Use native ZipArchive
            $zip = new ZipArchive();
            if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
                throw new Exception("Cannot create ZIP file: $zipFilePath");
            }

            // Add Excel file
            $excelName = basename($excelFile);
            $zip->addFile($excelFile, $excelName);

            // Add images in an images folder
            if (!empty($images)) {
                foreach ($images as $imagePath) {
                    if (file_exists($imagePath)) {
                        $imageName = 'images/' . basename($imagePath);
                        $zip->addFile($imagePath, $imageName);
                    }
                }
            }

            $zip->close();
            return $zipFilePath;
        } else {
            // Fallback: Create folder structure instead of ZIP
            $batchFolder = $this->exportsDir . $zipFilename;
            if (!is_dir($batchFolder)) {
                mkdir($batchFolder, 0755, true);
            }

            // Copy Excel file
            $excelDest = $batchFolder . '/' . basename($excelFile);
            copy($excelFile, $excelDest);

            // Create images folder and copy images
            if (!empty($images)) {
                $imagesFolder = $batchFolder . '/images';
                if (!is_dir($imagesFolder)) {
                    mkdir($imagesFolder, 0755, true);
                }

                foreach ($images as $imagePath) {
                    if (file_exists($imagePath)) {
                        $imageDest = $imagesFolder . '/' . basename($imagePath);
                        copy($imagePath, $imageDest);
                    }
                }

                // Create an index.html file for easy browsing
                $indexContent = '<!DOCTYPE html>
                <html lang="ar" dir="rtl">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>تصدير البيانات - ' . $zipFilename . '</title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
                </head>
                <body class="bg-light">
                    <div class="container py-4">
                        <div class="row">
                            <div class="col-12">
                                <h3><i class="bi bi-folder text-warning me-2"></i>مجلد البيانات المصدرة</h3>
                                <p class="text-muted">تم إنشاء هذا المجلد لأن إضافة الـ Zip غير متوفرة في الخادم</p>
                                <div class="list-group mt-3">
                                    <a href="' . basename($excelDest) . '" class="list-group-item list-group-item-action">
                                        <i class="bi bi-file-earmark-spreadsheet text-success me-2"></i>
                                        ملف البيانات الـ Excel (CSV): ' . basename($excelDest) . '
                                    </a>
                                    <a href="./images/" class="list-group-item list-group-item-action">
                                        <i class="bi bi-images text-primary me-2"></i>
                                        مجلد الصور ('.count($images).' صورة)
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </body>
                </html>';
                file_put_contents($batchFolder . '/index.html', $indexContent);
            }

            // Since we can't create ZIP, return the folder path
            // We'll modify the download logic later
            return $batchFolder; // Return folder path instead of zip
        }
    }

    /**
     * Generate all export files for all student batches
     */
    public function generateAllZipFiles(): array
    {
        $students = $this->getAllStudents();
        $batches = $this->getStudentBatches($students);

        $generatedFiles = [];

        $this->cleanupOldExports();

        foreach ($batches as $batchIndex => $batch) {
            $batchNum = $batchIndex + 1;
            $totalBatches = count($batches);

            $filename = 'students_batch_' . $batchNum . '_of_' . $totalBatches;

            try {
                // Create Excel file
                $excelFile = $this->createExcelFile($batch, $filename);

                // Get images for this batch
                $images = $this->getStudentImages($batch);

                // Create export file/folder
                $exportFile = $this->createZipFile($excelFile, $images, $filename);

                // Determine file type and size
                if (is_dir($exportFile)) {
                    // It's a folder
                    $fileSize = $this->getFolderSize($exportFile);
                    $fileType = 'folder';
                    $url = 'exports/' . basename($exportFile) . '/index.html';
                } else {
                    // It's a ZIP file
                    $fileSize = filesize($exportFile);
                    $fileType = 'zip';
                    $url = 'exports/' . basename($exportFile);
                }

                $generatedFiles[] = [
                    'filename' => basename($exportFile),
                    'batch' => $batchNum,
                    'total_batches' => $totalBatches,
                    'record_count' => count($batch),
                    'has_images' => !empty($images),
                    'created_at' => date('Y-m-d H:i:s'),
                    'file_size' => $fileSize,
                    'file_type' => $fileType,
                    'url' => $url
                ];

                // Clean up temporary Excel file (export contains a copy)
                unlink($excelFile);

            } catch (Exception $e) {
                error_log("Error creating batch $batchNum: " . $e->getMessage());
                continue;
            }
        }

        return $generatedFiles;
    }

    /**
     * Calculate folder size recursively
     */
    private function getFolderSize(string $folder): int
    {
        $size = 0;
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder));

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }

        return $size;
    }

    /**
     * Get list of existing export files (ZIP or folders)
     */
    public function getExistingZipFiles(): array
    {
        $exportFiles = [];

        // Get ZIP files
        if (class_exists('ZipArchive')) {
            $zipFiles = glob($this->exportsDir . "*.zip");
            foreach ($zipFiles as $file) {
                $filename = basename($file);
                $filePath = $this->exportsDir . $filename;

                // Parse filename to extract batch info
                if (preg_match('/students_batch_(\d+)_of_(\d+)/', $filename, $matches)) {
                    $batch = (int)$matches[1];
                    $totalBatches = (int)$matches[2];

                    $exportFiles[] = [
                        'filename' => $filename,
                        'batch' => $batch,
                        'total_batches' => $totalBatches,
                        'file_size' => filesize($filePath),
                        'created_at' => date('Y-m-d H:i:s', filemtime($filePath)),
                        'file_type' => 'zip',
                        'url' => 'exports/' . $filename
                    ];
                }
            }
        }

        // Get folders (when ZipArchive is not available)
        $folders = scandir($this->exportsDir);
        foreach ($folders as $folder) {
            if ($folder === '.' || $folder === '..') continue;

            $folderPath = $this->exportsDir . $folder;
            if (is_dir($folderPath)) {
                // Parse folder name to extract batch info
                if (preg_match('/students_batch_(\d+)_of_(\d+)/', $folder, $matches)) {
                    $batch = (int)$matches[1];
                    $totalBatches = (int)$matches[2];
                    $folderSize = $this->getFolderSize($folderPath);

                    $exportFiles[] = [
                        'filename' => $folder,
                        'batch' => $batch,
                        'total_batches' => $totalBatches,
                        'file_size' => $folderSize,
                        'created_at' => date('Y-m-d H:i:s', filemtime($folderPath)),
                        'file_type' => 'folder',
                        'url' => 'exports/' . $folder . '/index.html'
                    ];
                }
            }
        }

        // Sort by batch number
        usort($exportFiles, function($a, $b) {
            return $a['batch'] <=> $b['batch'];
        });

        return $exportFiles;
    }

    /**
     * Clean up old export files (keep only last 10)
     */
    private function cleanupOldExports(): void
    {
        $exportItems = [];

        // Get both ZIP files and folders
        if (class_exists('ZipArchive')) {
            $zipFiles = glob($this->exportsDir . "*.zip");
            foreach ($zipFiles as $file) {
                if (preg_match('/students_batch_\d+_of_\d+/', basename($file))) {
                    $exportItems[] = $file;
                }
            }
        }

        $folders = scandir($this->exportsDir);
        foreach ($folders as $folder) {
            if ($folder === '.' || $folder === '..') continue;

            $folderPath = $this->exportsDir . $folder;
            if (is_dir($folderPath) && preg_match('/students_batch_\d+_of_\d+/', $folder)) {
                $exportItems[] = $folderPath;
            }
        }

        if (count($exportItems) <= 10) {
            return;
        }

        // Sort by modification time (newest first)
        usort($exportItems, function($a, $b) {
            return filemtime($b) <=> filemtime($a);
        });

        // Remove items beyond the first 10
        $itemsToRemove = array_slice($exportItems, 10);
        foreach ($itemsToRemove as $item) {
            if (is_dir($item)) {
                // Remove entire folder and its contents
                $this->removeDirectory($item);
            } elseif (is_file($item)) {
                unlink($item);
            }
        }
    }

    /**
     * Recursively remove directory and its contents
     */
    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;

            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }

    /**
     * Get export statistics
     */
    public function getExportStats(): array
    {
        $students = $this->getAllStudents();
        $batches = $this->getStudentBatches($students);
        $existingZips = $this->getExistingZipFiles();

        return [
            'total_students' => count($students),
            'batch_size' => $this->batchSize,
            'total_batches' => count($batches),
            'existing_zips' => count($existingZips),
            'next_batch' => count($existingZips) + 1
        ];
    }
}

// Prevent direct access
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    header('HTTP/1.1 403 Forbidden', true, 403);
    echo 'Direct access not allowed';
    exit;
}
?>
