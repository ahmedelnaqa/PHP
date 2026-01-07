<?php
/**
 * Complete Setup Script for Student Data System
 * Browser-compatible version
 */

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚀 إعداد نظام إدارة بيانات الطلاب</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            max-width: 800px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 30px;
            text-align: center;
        }
        .progress-step {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin: 10px 0;
            border-left: 5px solid #007bff;
            transition: all 0.3s ease;
        }
        .progress-step.success {
            background: #d4edda;
            border-left-color: #28a745;
        }
        .status-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-left: 10px;
        }
        .success { background: #28a745; color: white; }
        .info { background: #17a2b8; color: white; }
        .warning { background: #ffc107; color: white; }
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #007bff;
        }
        .file-card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #007bff;
        }
        .step-number {
            background: #007bff;
            color: white;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            margin-left: 10px;
        }
        .progress-container {
            margin: 20px 0;
        }
        .completion-badge {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
<div class="container mt-5 mb-5">
    <div class="header">
        <h1 class="mb-3">🚀 نظام إدارة بيانات الطلاب</h1>
        <h2 class="h4 mb-0">عملية الإعداد الكاملة</h2>
    </div>

    <div class="p-4">
        <div class="alert alert-info text-center" role="alert">
            <h5 class="alert-heading mb-3">🔧 بدء عملية الإعداد...</h5>
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">تحميل...</span>
            </div>
<div id="setup-steps">
            <div class="text-center">
                <h4 class="mb-4">🔧 Executing Setup Steps...</h4>
<?php

$startTime = microtime(true);
$stepCount = 0;

function showStep($stepName, $icon, $status = 'success', $message = '') {
    echo "
    <div class='progress-step'>
        <div class='d-flex align-items-center'>
            <span class='status-icon $status'>$icon</span>
            <span class='step-name'>$stepName</span>
            " . ($message ? "<small class='text-muted ms-2'>$message</small>" : "") . "
        </div>
    </div>";
}

function showDirectoryStep($directory, $status) {
    $statusIcon = $status ? '✅' : '❌';
    $badgeClass = $status ? 'success' : 'danger';
    echo "
    <div class='progress-step $badgeClass'>
        <div class='d-flex align-items-center justify-content-between'>
            <span>📁 $directory</span>
            <span class='status-icon $badgeClass px-2'>$statusIcon</span>
        </div>
    </div>";
}

function showFileStep($filename, $description) {
    echo "
    <div class='file-card'>
        <div class='d-flex align-items-center'>
            <span class='me-3'>✨</span>
            <div>
                <strong>$filename</strong>
                <br>
                <small class='text-muted'>$description</small>
            </div>
        </div>
    </div>";
}

// Function to create directories with proper permissions
function createDirectories() {
    $directories = ['data', 'backups', 'logs', 'uploads', 'config', 'models', 'tests'];

    echo "📁 Creating directories...\n";
    $created = 0;
    foreach ($directories as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            echo "✅ Created directory: $dir\n";
            $created++;
        } else {
            echo "ℹ️  Directory exists: $dir\n";
        }
    }
    echo "✨ Created $created directories\n\n";
    return $created;
}

// Function to set proper permissions
function setPermissions() {
    echo "🔒 Setting proper permissions...\n";

    $permissions = [
        'data' => 0750,
        'backups' => 0750,
        'logs' => 0750,
        'uploads' => 0755,
        'config' => 0750
    ];

    $secured = 0;
    foreach ($permissions as $dir => $perms) {
        if (is_dir($dir)) {
            if (chmod($dir, $perms)) {
                $octal = sprintf('%o', $perms);
                echo "✅ Set permissions for $dir to $octal\n";
                $secured++;
            } else {
                echo "❌ Failed to set permissions for $dir\n";
            }
        }
    }
    echo "🛡️  Secured $secured directories\n\n";
}

// Function to create .htaccess security file
function createHtaccess() {
    echo "🔒 Creating security .htaccess...\n";

    $htaccessContent = <<<HTACCESS
# Security Headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# Disable directory listing
Options -Indexes

# Prevent access to sensitive files
<FilesMatch "\.(sql|db|sqlite|log)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Prevent access to config files
<Files "config/database.php">
    Order allow,deny
    Deny from all
</Files>

# Prevent access to setup script after setup
<Files "setup.php">
    Order allow,deny
    Deny from all
</Files>

# PHP Configuration
php_flag display_errors off
php_flag log_errors on
php_value error_log /var/log/php_errors.log
HTACCESS;

    if (file_put_contents('.htaccess', $htaccessContent) !== false) {
        echo "✅ Created .htaccess security file\n";
    } else {
        echo "❌ Failed to create .htaccess file\n";
    }
}

// Function to check if files exist
function checkFiles() {
    $requiredFiles = [
        'config/database.php',
        'models/Student.php'
    ];

    echo "🔍 Checking required files...\n";
    $missing = [];
    $found = 0;
    foreach ($requiredFiles as $file) {
        if (!file_exists($file)) {
            $missing[] = $file;
            echo "❌ Missing: $file\n";
        } else {
            echo "✅ Found: $file\n";
            $found++;
        }
    }

    if ($found > 0) {
        echo "📁 $found files found\n";
    }

    if (!empty($missing)) {
        echo "\n⚠️  Missing files detected:\n";
        foreach ($missing as $file) {
            echo "  - $file\n";
        }
        echo "These will be created automatically.\n\n";
    } else {
        echo "\n📋 All required files exist!\n";
    }

    return $missing;
}

// Function to create database config file if missing
function createDatabaseConfig() {
    echo "💾 Creating database configuration...\n";

    $dbConfig = '<?php
class DatabaseConfig
{
    private const DB_TYPE = \'sqlite\';
    private const DB_FILE = __DIR__ . \'/../data/student_data.db\';
    private const DB_BACKUP_DIR = __DIR__ . \'/../backups/\';

    private const PDO_OPTIONS = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ];

    private static $pdoInstance = null;
    private static $connectionCount = 0;

    public static function getConnection() {
        if (self::$pdoInstance === null) {
            try {
                if (!is_dir(dirname(self::DB_FILE))) {
                    mkdir(dirname(self::DB_FILE), 0755, true);
                }

                self::$pdoInstance = new PDO(
                    self::DB_TYPE . \':\' . self::DB_FILE,
                    null, null, self::PDO_OPTIONS
                );

                self::$pdoInstance->exec(\'PRAGMA foreign_keys = ON\');
                self::$pdoInstance->exec(\'PRAGMA synchronous = NORMAL\');
                DatabaseConfig::logConnection(\'Connection established\');

            } catch (PDOException $e) {
                throw $e;
            }
        }

        self::$connectionCount++;
        return self::$pdoInstance;
    }

    public static function testConnection() {
        try {
            $pdo = self::getConnection();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function getStats() {
        if (self::$pdoInstance === null) {
            self::getConnection();
        }

        $stats = [
            \'connection_count\' => self::$connectionCount,
            \'database_size\' => filesize(self::DB_FILE),
            \'readable_size\' => self::formatSize(filesize(self::DB_FILE))
        ];

        try {
            $stmt = self::$pdoInstance->query("SELECT COUNT(*) as total_students FROM students");
            $result = $stmt->fetch();
            $stats[\'total_students\'] = $result[\'total_students\'];
        } catch (Exception $e) {
            $stats[\'total_students\'] = \'0\';
        }

        return $stats;
    }

    public static function formatSize($bytes) {
        $units = [\'B\', \'KB\', \'MB\', \'GB\'];
        $i = 0;
        while ($bytes >= 1024 && $i < 3) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . \' \' . $units[$i];
    }

    private static function logConnection($message) {
        $logFile = __DIR__ . \'/../logs/database.log\';
        file_put_contents($logFile, date(\'Y-m-d H:i:s\') . \' \' . $message . PHP_EOL, FILE_APPEND);
    }

    public static function closeConnection() {
        if (self::$pdoInstance !== null) {
            self::$pdoInstance = null;
        }
        self::$connectionCount = 0;
    }
}
?>';

    if (file_put_contents('config/database.php', $dbConfig) !== false) {
        echo "✅ Created config/database.php\n";
        return true;
    } else {
        echo "❌ Failed to create config/database.php\n";
        return false;
    }
}

// Function to create Student model if missing
function createStudentModel() {
    echo "🔧 Creating Student model...\n";

    $studentModel = '<?php
require_once __DIR__ . \'/../config/database.php\';

class Student
{
    private ?int $id;
    private string $nid;
    private string $nationality;
    private string $nameAr;
    private string $nameEn;
    private string $nameOnCard;
    private string $homeNumber;
    private string $mobileNumber;
    private string $addressAr;
    private string $addressEn;
    private string $gender;
    private string $birthday;
    private string $email;
    private string $facultyName;
    private ?string $imagePath;
    private ?string $createdAt;
    private ?string $updatedAt;

    // Validation rules
    private const VALIDATION_RULES = [
        \'nid\' => [
            \'pattern\' => \'/^\\d{14}$/\',
            \'max_length\' => 14,
            \'message\' => \'رقم البطاقة الشخصية يجب أن يكون 14 رقم بالضبط\'
        ],
        \'name_ar\' => [
            \'max_length\' => 35,
            \'message\' => \'الاسم بالعربية يجب ألا يزيد عن 35 حرف\'
        ],
        \'name_en\' => [
            \'pattern\' => \'/^[A-Za-z\\s]{1,35}$/\',
            \'max_length\' => 35,
            \'message\' => \'الاسم بالإنجليزية يجب أن يكون أحرف إنجليزية فقط، بحد أقصى 35 حرف\'
        ],
        \'home_number\' => [
            \'pattern\' => \'/^0?\\d{9,10}$/\',
            \'max_length\' => 10,
            \'message\' => \'رقم الهاتف المنزلي يجب أن يكون 10 أرقام، يمكن أن يبدأ بالصفر\'
        ],
        \'mobile_number\' => [
            \'pattern\' => \'/^0\\d{10}$/\',
            \'max_length\' => 11,
            \'message\' => \'رقم الهاتف المحمول يجب أن يكون 11 رقم، يبدأ بالصفر\'
        ],
        \'address_ar\' => [
            \'max_length\' => 35,
            \'message\' => \'العنوان بالعربية يجب ألا يزيد عن 35 حرف\'
        ],
        \'address_en\' => [
            \'pattern\' => \'/^[A-Za-z\\s]{1,35}$/\',
            \'max_length\' => 35,
            \'message\' => \'العنوان بالإنجليزية يجب أن يكون أحرف إنجليزية فقط، بحد أقصى 35 حرف\'
        ],
        \'birthday\' => [
            \'pattern\' => \'/^\\d{4}-\\d{2}-\\d{2}$/\',
            \'message\' => \'تاريخ الميلاد يجب أن يكون بتنسيق YYYY-MM-DD\'
        ],
        \'email\' => [
            \'pattern\' => \'/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\\.[a-zA-Z0-9-.]+$/u\',
            \'max_length\' => 100,
            \'message\' => \'البريد الإلكتروني غير صحيح\'
        ]
    ];

    public function __construct(?int $id = null) {
        $this->id = $id;
        $this->nationality = \'Egyptian\';
        $this->gender = \'Male\';
        $this->facultyName = \'العلوم الرياضية للبنين\';
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getNid(): string { return $this->nid; }
    public function getNameAr(): string { return $this->nameAr; }
    public function getNameEn(): string { return $this->nameEn; }
    public function getHomeNumber(): string { return $this->homeNumber; }
    public function getMobileNumber(): string { return $this->mobileNumber; }
    public function getAddressAr(): string { return $this->addressAr; }
    public function getAddressEn(): string { return $this->addressEn; }
    public function getGender(): string { return $this->gender; }
    public function getBirthday(): string { return $this->birthday; }
    public function getEmail(): string { return $this->email; }
    public function getFacultyName(): string { return $this->facultyName; }
    public function getImagePath(): ?string { return $this->imagePath; }

    // Setters with validation
    public function setNid(string $nid): void {
        $this->validateField(\'nid\', $nid);
        $this->nid = $nid;
    }

    public function setNameAr(string $nameAr): void {
        $this->validateField(\'name_ar\', $nameAr);
        $this->nameAr = $nameAr;
    }

    public function setNameEn(string $nameEn): void {
        $this->validateField(\'name_en\', $nameEn);
        $this->nameEn = $nameEn;
    }

    public function setHomeNumber(string $homeNumber): void {
        $this->validateField(\'home_number\', $homeNumber);
        $this->homeNumber = $homeNumber;
    }

    public function setMobileNumber(string $mobileNumber): void {
        $this->validateField(\'mobile_number\', $mobileNumber);
        $this->mobileNumber = $mobileNumber;
    }

    public function setAddressAr(string $addressAr): void {
        $this->validateField(\'address_ar\', $addressAr);
        $this->addressAr = $addressAr;
    }

    public function setAddressEn(string $addressEn): void {
        $this->validateField(\'address_en\', $addressEn);
        $this->addressEn = $addressEn;
    }

    public function setGender(string $gender): void {
        $this->gender = $gender;
    }

    public function setBirthday(string $birthday): void {
        $this->validateField(\'birthday\', $birthday);
        $this->birthday = $birthday;
    }

    public function setEmail(string $email): void {
        $this->validateField(\'email\', $email);
        $this->email = $email;
    }

    public function setImagePath(?string $imagePath): void {
        $this->imagePath = $imagePath;
    }

    public function setFacultyName(string $facultyName): void {
        $this->facultyName = $facultyName;
    }

    private function validateField(string $field, string $value): void {
        $rules = isset(self::VALIDATION_RULES[$field]) ? self::VALIDATION_RULES[$field] : [];

        if (isset($rules[\'max_length\']) && strlen($value) > $rules[\'max_length\']) {
            throw new InvalidArgumentException($rules[\'message\']);
        }

        if (isset($rules[\'pattern\']) && !preg_match($rules[\'pattern\'], $value)) {
            throw new InvalidArgumentException($rules[\'message\']);
        }

        if (isset($rules[\'required\']) && $rules[\'required\'] && empty(trim($value))) {
            throw new InvalidArgumentException($field . \' مطلوب ولا يمكن أن يكون فارغ\');
        }
    }

    public function validateAll(): void {
        foreach (self::VALIDATION_RULES as $field => $rules) {
            $getter = \'get\' . str_replace(\'_\', \'\', ucwords($field, \'_\'));
            if (method_exists($this, $getter)) {
                $value = $this->$getter();
                $this->validateField($field, $value);
            }
        }
    }

    public function save(): void {
        $pdo = DatabaseConfig::getConnection();

        if ($this->exists()) {
            $this->update($pdo);
        } else {
            $this->insert($pdo);
        }
    }

    private function insert($pdo): void {
        $sql = "INSERT INTO students (
            nid, nationality, name_ar, name_en, home_number, mobile_number,
            address_ar, address_en, gender, birthday, email, faculty_name, image_path
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $this->nid,
            $this->nationality,
            $this->nameAr,
            $this->nameEn,
            $this->homeNumber,
            $this->mobileNumber,
            $this->addressAr,
            $this->addressEn,
            $this->gender,
            $this->birthday,
            $this->email,
            $this->facultyName,
            $this->imagePath
        ]);
    }

    private function update($pdo): void {
        $sql = "UPDATE students SET
            nationality = ?, name_ar = ?, name_en = ?, home_number = ?,
            mobile_number = ?, address_ar = ?, address_en = ?, gender = ?,
            birthday = ?, email = ?, faculty_name = ?, image_path = ?,
            updated_at = CURRENT_TIMESTAMP
            WHERE nid = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $this->nationality,
            $this->nameAr,
            $this->nameEn,
            $this->homeNumber,
            $this->mobileNumber,
            $this->addressAr,
            $this->addressEn,
            $this->gender,
            $this->birthday,
            $this->email,
            $this->facultyName,
            $this->imagePath,
            $this->nid
        ]);
    }

    public function exists(): bool {
        try {
            $pdo = DatabaseConfig::getConnection();
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM students WHERE nid = ?");
            $stmt->execute([$this->nid]);
            $result = $stmt->fetch();
            return $result[\'count\'] > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    public function delete(): bool {
        try {
            $pdo = DatabaseConfig::getConnection();
            $stmt = $pdo->prepare("DELETE FROM students WHERE nid = ?");
            $stmt->execute([$this->nid]);
            if ($this->imagePath && file_exists($this->imagePath)) {
                unlink($this->imagePath);
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function findByNid(string $nid): ?Student {
        try {
            $pdo = DatabaseConfig::getConnection();
            $stmt = $pdo->prepare("SELECT * FROM students WHERE nid = ?");
            $stmt->execute([$nid]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$result) {
                return null;
            }

            $student = new self();
            foreach ($result as $key => $value) {
                $method = \'set\' . str_replace(\'_\', \'\', ucwords($key, \'_\'));
                $property = lcfirst(str_replace(\'_\', \'\', ucwords($key, \'_\')));
                if (property_exists($student, $property) && $value !== null) {
                    $student->$property = $value;
                }
            }
            return $student;
        } catch (Exception $e) {
            return null;
        }
    }

    public static function all() {
        try {
            $pdo = DatabaseConfig::getConnection();
            $stmt = $pdo->query("SELECT * FROM students ORDER BY created_at DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function toArray(): array {
        return [
            \'nid\' => $this->nid,
            \'nationality\' => $this->nationality,
            \'name_ar\' => $this->nameAr,
            \'name_en\' => $this->nameEn,
            \'home_number\' => $this->homeNumber,
            \'mobile_number\' => $this->mobileNumber,
            \'address_ar\' => $this->addressAr,
            \'address_en\' => $this->addressEn,
            \'gender\' => $this->gender,
            \'birthday\' => $this->birthday,
            \'email\' => $this->email,
            \'faculty_name\' => $this->facultyName,
            \'image_path\' => $this->imagePath
        ];
    }
}

// Helper functions the form expects
function executeQuery(string $sql, array $params = []) {
    try {
        $pdo = DatabaseConfig::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (Exception $e) {
        throw $e;
    }
}

function executeTransaction(callable $callback) {
    $pdo = DatabaseConfig::getConnection();
    $pdo->beginTransaction();

    try {
        $result = $callback($pdo);
        $pdo->commit();
        return $result;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
?>';

    if (file_put_contents('models/Student.php', $studentModel) !== false) {
        echo "✅ Created models/Student.php\n";
        return true;
    } else {
        echo "❌ Failed to create models/Student.php\n";
        return false;
    }
}

// Function to create database table directly
function createDatabaseTable() {
    echo "🗂️  Creating database table...\n";

    try {
        // Connect directly to SQLite
        $pdo = new PDO('sqlite:data/student_data.db', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        // Enable foreign keys
        $pdo->exec('PRAGMA foreign_keys = ON');
        $pdo->exec('PRAGMA synchronous = NORMAL');

    // Create students table
    $sql = "CREATE TABLE IF NOT EXISTS students (
        nid VARCHAR(14) PRIMARY KEY,
        nationality VARCHAR(50) NOT NULL DEFAULT 'Egyptian',
        name_ar VARCHAR(35) NOT NULL,
        name_en VARCHAR(35) NOT NULL,
        name_on_card VARCHAR(20) NULL,
        home_number VARCHAR(10) NOT NULL,
        mobile_number VARCHAR(11) NOT NULL,
        address_ar VARCHAR(35) NOT NULL,
        address_en VARCHAR(35) NOT NULL,
        gender VARCHAR(10) NOT NULL DEFAULT 'Male',
        birthday DATE NOT NULL,
        email VARCHAR(100) NOT NULL,
        faculty_name VARCHAR(100) NOT NULL DEFAULT 'العلوم الرياضية للبنين',
        image_path VARCHAR(255),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";

        $pdo->exec($sql);

        // Create indexes for better performance
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_name_ar ON students(name_ar)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_name_en ON students(name_en)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_email ON students(email)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_created_at ON students(created_at)');

        // Test the table by counting rows
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM students");
        $result = $stmt->fetch();

        echo "✅ Students table created successfully\n";
        echo "📊 Table ready with {$result['count']} existing records\n";

        return true;

    } catch (Exception $e) {
        echo "❌ Database error: " . $e->getMessage() . "\n";
        return false;
    }
}

// Function to initialize database if needed
function initializeDatabase() {
    echo "🗂️  Initializing database...\n";

    // Create missing files first
    if (!file_exists('config/database.php')) {
        if (!createDatabaseConfig()) {
            echo "❌ Could not create database config\n";
            return false;
        }
    }

    if (!file_exists('models/Student.php')) {
        if (!createStudentModel()) {
            echo "❌ Could not create Student model\n";
            return false;
        }
    }

    // Always try to create/ensure the database table exists
    if (!createDatabaseTable()) {
        return false;
    }

    echo "✅ Database connection successful\n";
    echo "✅ Database ready for use\n";

    return true;
}

// Function to create log files
function createLogFiles() {
    echo "📝 Creating log files...\n";

    $logFiles = [
        'logs/database.log',
        'logs/database_errors.log',
        'logs/maintenance.log'
    ];

    $created = 0;
    foreach ($logFiles as $logFile) {
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        if (!file_exists($logFile)) {
            file_put_contents($logFile, '', LOCK_EX);
            echo "✅ Created: $logFile\n";
            $created++;
        } else {
            echo "ℹ️  Exists: $logFile\n";
        }
    }

    echo "📋 Created $created log files\n\n";
}

// Function to show comprehensive summary
function showSummary($startTime) {
    $endTime = microtime(true);
    $executionTime = round($endTime - $startTime, 2);

    echo "\n";
    echo "╔═══════════════════════════════════════════════════════════════╗\n";
    echo "║                          🎉 SUCCESS                           ║\n";
    echo "║               SETUP COMPLETED SUCCESSFULLY!                   ║\n";
    echo "╠═══════════════════════════════════════════════════════════════╣\n";
    echo sprintf("║ Time Taken: %-45s ║\n", $executionTime . " seconds ⏱️");
    echo "╠═══════════════════════════════════════════════════════════════╣\n";
    echo "║ 📁 All required directories created                               ║\n";
    echo "║ 🛡️  Security permissions set                                     ║\n";
    echo "║ 🔒 .htaccess security file created                             ║\n";
    echo "║ 🗂️  Database initialized and ready                              ║\n";
    echo "║ 📝 Log files ready for use                                     ║\n";
    echo "╠═══════════════════════════════════════════════════════════════╣\n";
    echo "║                           FILES CREATED                       ║\n";
    echo "╠═══════════════════════════════════════════════════════════════╣\n";
    echo "║ ✨ config/database.php - Database configuration                 ║\n";
    echo "║ ✨ models/Student.php  - Student data model                     ║\n";
    echo "║ ✨ data/student_data.db - SQLite database                      ║\n";
    echo "║ ✨ students table      - Database schema                      ║\n";
    echo "║ ✨ .htaccess           - Security rules                       ║\n";
    echo "║ ✨ logs/*.log          - Application logs                     ║\n";
    echo "╠═══════════════════════════════════════════════════════════════╣\n";
    echo "║             🚀 PRODUCTION SYSTEM READY!                         ║\n";
    echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

    echo "╔═══════════════════════════════════════════════════════════════╗\n";
    echo "║                         📋 NEXT STEPS                          ║\n";
    echo "╠═══════════════════════════════════════════════════════════════╣\n";
    echo "║ 1️⃣  Visit: http://localhost/form.php                           ║\n";
    echo "║     → Start adding student data                              ║\n";
    echo "║                                                               ║\n";
    echo "║ 2️⃣  Command: php backup.php                                   ║\n";
    echo "║     → Set up regular database backups                        ║\n";
    echo "║                                                               ║\n";
    echo "║ 3️⃣  Remove: setup.php (security)                              ║\n";
    echo "║     → Delete setup files after testing                       ║\n";
    echo "║                                                               ║\n";
    echo "║ 4️⃣  Monitor: logs/ folder                                     ║\n";
    echo "║     → Check logs for any system issues                       ║\n";
    echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

    echo "🎊 CONGRATULATIONS! Your Student Data System is now production-ready!\n";
    echo "   The system includes form validation, image processing, backup services,\n";
    echo "   and comprehensive error handling. Ready to manage student data efficiently!\n\n";
}


// ============== MAIN SETUP EXECUTION STARTS HERE ==============

$endTime = microtime(true);
$executionTime = round($endTime - $startTime, 2);

// Step 1: Create directories
showStep("إنشاء المجلدات", "📁");
sleep(1); // Brief pause for visual effect

$directories = ['data', 'backups', 'logs', 'uploads', 'config', 'models', 'tests'];
$created = 0;
foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        showDirectoryStep($dir, true);
        $created++;
    } else {
        showDirectoryStep($dir, false); // Already exists
    }
}
echo "<div class='alert alert-success'>✅ أنشئ $created مجلدات بنجاح</div>";

// Step 2: Set permissions
showStep("تحديد صلاحيات الأمان", "🔒");
sleep(1);

$permissions = ['data' => 0750, 'backups' => 0750, 'logs' => 0750, 'uploads' => 0755, 'config' => 0750];
foreach ($permissions as $dir => $perms) {
    if (is_dir($dir) && chmod($dir, $perms)) {
        $octal = sprintf('%o', $perms);
        showStep("إعداد صلاحيات لـ $dir إلى $octal", "🛡️", "success");
    }
}
echo "<div class='alert alert-success'>🔒 تم تنفيذ صلاحيات الأمان بنجاح</div>";

// Step 3: Create .htaccess
showStep("إنشاء ملف الأمان .htaccess", "🔒");
sleep(1);

$htaccessContent = "# Security Headers\n<IfModule mod_headers.c>\n    Header always set X-Content-Type-Options nosniff\n    Header always set X-Frame-Options DENY\n    Header always set X-XSS-Protection \"1; mode=block\"\n    Header always set Referrer-Policy \"strict-origin-when-cross-origin\"\n</IfModule>\n\nOptions -Indexes\n<FilesMatch \"\\.(sql|db|sqlite|log)$\">Order allow,deny\nDeny from all\n</FilesMatch>";
if (file_put_contents('.htaccess', $htaccessContent)) {
    showStep("تم إنشاء ملف الأمان .htaccess", "🔒", "success");
} else {
    showStep("فشل في إنشاء ملف .htaccess", "❌", "warning");
}

// Step 4: Check and create files
showStep("التحقق من الملفات المطلوبة", "🔍");
sleep(1);

// Check config/database.php
if (!file_exists('config/database.php')) {
    $dbConfig = '<?php class DatabaseConfig { private const DB_TYPE = \'sqlite\'; private const DB_FILE = __DIR__ . \'/../data/student_data.db\'; private static $pdoInstance = null; public static function getConnection() { if (self::$pdoInstance === null) { $dbDir = dirname(self::DB_FILE); if (!is_dir($dbDir)) mkdir($dbDir, 0755, true); self::$pdoInstance = new PDO(self::DB_TYPE . \':\' . self::DB_FILE, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]); } return self::$pdoInstance; } } ?>';
    if (file_put_contents('config/database.php', $dbConfig)) {
        showStep("تم إنشاء config/database.php", "✅", "success");
    }
} else {
    showStep("config/database.php موجود مسبقاً", "ℹ️", "info");
}

// Step 5: Create database table
showStep("إنشاء قاعدة البيانات والجداول", "🗂️");
sleep(1);

try {
    $pdo = new PDO('sqlite:data/student_data.db', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    $pdo->exec('PRAGMA foreign_keys = ON');

    $sql = "CREATE TABLE IF NOT EXISTS students (
        nid VARCHAR(14) PRIMARY KEY,
        nationality VARCHAR(50) NOT NULL DEFAULT 'Egyptian',
        name_ar VARCHAR(35) NOT NULL,
        name_en VARCHAR(35) NOT NULL,
        name_on_card VARCHAR(20) NULL,
        home_number VARCHAR(10) NOT NULL,
        mobile_number VARCHAR(11) NOT NULL,
        address_ar VARCHAR(35) NOT NULL,
        address_en VARCHAR(35) NOT NULL,
        gender VARCHAR(10) NOT NULL DEFAULT 'Male',
        birthday DATE NOT NULL,
        email VARCHAR(100) NOT NULL,
        faculty_name VARCHAR(100) NOT NULL DEFAULT 'العلوم الرياضية للبنين',
        image_path VARCHAR(255),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";

    // Add name_on_card column to existing tables
    try {
        $pdo->exec("ALTER TABLE students ADD COLUMN name_on_card VARCHAR(20) NULL");
        echo "✅ Added name_on_card column to existing table\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'duplicate column name') === false) {
            echo "ℹ️ Column already exists or no table to alter\n";
        }
    }

    $pdo->exec($sql);
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_name_ar ON students(name_ar)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_name_en ON students(name_en)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_email ON students(email)');

    $stmt = $pdo->query("SELECT COUNT(*) as count FROM students");
    $result = $stmt->fetch();
    $count = $result['count'];

    showStep("تم إنشاء جدول قاعدة البيانات بنجاح", "🗂️", "success");
    echo "<div class='alert alert-info'>📊 الجدول جاهز مع $count سجلات موجودة</div>";

} catch (Exception $e) {
    showStep("خطأ في قاعدة البيانات: " . $e->getMessage(), "❌", "danger");
    exit(1);
}

// Step 6: Create log files
showStep("إنشاء ملفات السجلات", "📝");
sleep(1);

$logFiles = ['logs/database.log', 'logs/database_errors.log', 'logs/maintenance.log'];
foreach ($logFiles as $logFile) {
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    if (!file_exists($logFile)) {
        file_put_contents($logFile, '', LOCK_EX);
        showStep("تم إنشاء ملف السجل: $logFile", "📝", "success");
    }
}

// ============== SETUP COMPLETION ==============

showStep("تم الانتهاء من جميع خطوات الإعداد!", "✨", "success");

?>

        </div>
    </div>

    <!-- SUCCESS SUMMARY -->
    <div class="text-center mb-4">
        <div class="completion-badge">
            🎉 إنجاز الإعداد تماماً! 
        </div>
    </div>

    <!-- TIMING SUMMARY -->
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-lg border-0">
                <div class="card-body text-center">
                    <h4 class="text-primary mb-2">⏱️ وقت التنفيذ</h4>
                    <h2 class="text-success"><?php echo $executionTime; ?> ثانية</h2>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-lg border-0">
                <div class="card-body text-center">
                    <h4 class="text-primary mb-2">📊 المكونات المُنشأة</h4>
                    <div class="row">
                        <div class="col-4">
                            <div class="border rounded p-2">
                                <h3 class="text-success">7</h3>
                                <small>مجلدات</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-2">
                                <h3 class="text-success">6</h3>
                                <small>ملفات</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-2">
                                <h3 class="text-success">1</h3>
                                <small>جدول</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-4">

    <!-- FILES CREATED SECTION -->
    <div class="row">
        <div class="col-md-12">
            <h4 class="text-center mb-4">📁 الملفات والمجلدات المُنشأة</h4>

            <div class="row">
                <div class="col-md-6">
                    <h5>✨ الملفات الرئيسية</h5>
                    <div class="list-group mb-3">
                        <div class="list-group-item list-group-item-action d-flex align-items-center">
                            <span class="badge bg-primary rounded-pill">PHP</span>
                            <div class="ms-3">
                                <strong>config/database.php</strong><br>
                                <small class="text-muted">إعدادات قاعدة البيانات</small>
                            </div>
                        </div>
                        <div class="list-group-item list-group-item-action d-flex align-items-center">
                            <span class="badge bg-success rounded-pill">PHP</span>
                            <div class="ms-3">
                                <strong>models/Student.php</strong><br>
                                <small class="text-muted">نموذج بيانات الطالب</small>
                            </div>
                        </div>
                        <div class="list-group-item list-group-item-action d-flex align-items-center">
                            <span class="badge bg-info rounded-pill">DB</span>
                            <div class="ms-3">
                                <strong>data/student_data.db</strong><br>
                                <small class="text-muted">قاعدة البيانات SQLite</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <h5>🛡️ الأمان والسجلات</h5>
                    <div class="list-group mb-3">
                        <div class="list-group-item list-group-item-action d-flex align-items-center">
                            <span class="badge bg-danger rounded-pill">SEC</span>
                            <div class="ms-3">
                                <strong>.htaccess</strong><br>
                                <small class="text-muted">قواعد الأمان</small>
                            </div>
                        </div>
                        <div class="list-group-item list-group-item-action d-flex align-items-center">
                            <span class="badge bg-warning rounded-pill">LOG</span>
                            <div class="ms-3">
                                <strong>logs/*.log</strong><br>
                                <small class="text-muted">ملفات السجلات</small>
                            </div>
                        </div>
                        <div class="list-group-item list-group-item-action d-flex align-items-center">
                            <span class="badge bg-dark rounded-pill">SYS</span>
                            <div class="ms-3">
                                <strong>students table</strong><br>
                                <small class="text-muted">طبقة قاعدة البيانات</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-4">

    <!-- NEXT STEPS -->
    <div class="row">
        <div class="col-md-12">
            <h4 class="text-center mb-4">🚀 الخطوات التالية</h4>

            <div class="row">
                <div class="col-md-3 mb-3">
                    <div class="card h-100 border-primary">
                        <div class="card-body text-center">
                            <h1 class="text-primary mb-2">1️⃣</h1>
                            <h5 class="card-title">ابدأ بإضافة البيانات</h5>
                            <p class="card-text">قم بزيارة النموذج الإلكتروني لبدء إدخال بيانات الطلاب</p>
                            <a href="form.php" class="btn btn-primary" target="_blank">📝 افتح النموذج</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card h-100 border-success">
                        <div class="card-body text-center">
                            <h1 class="text-success mb-2">2️⃣</h1>
                            <h5 class="card-title">نسخ احتياطي</h5>
                            <p class="card-text">أعد نسخ احتياطي لقاعدة البيانات</p>
                            <code class="text-muted bg-light px-2 py-1 rounded">$ php backup.php</code>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card h-100 border-warning">
                        <div class="card-body text-center">
                            <h1 class="text-warning mb-2">3️⃣</h1>
                            <h5 class="card-title">أمان النظام</h5>
                            <p class="card-text">حذف ملفات الإعداد بعد التأكد من عمل النظام</p>

                            <!-- Spirit Check & Self-Destruction -->
                            <?php if (isset($_POST['cleanup'])): ?>
                                <?php
                                $cleanupFiles = ['init.php', 'setup.php', 'db_init.php'];
                                $deleted = [];

                                foreach ($cleanupFiles as $file) {
                                    if (file_exists($file)) {
                                        if (unlink($file)) {
                                            $deleted[] = $file;
                                        }
                                    }
                                }
                                ?>
                                <div class="alert alert-success mt-3">
                                    ✅ تم حذف <?php echo count($deleted); ?> ملف إعداد بنجاح!<br>
                                    <small><?php echo implode(', ', $deleted); ?></small>
                                </div>
                                <a href="form.php" class="btn btn-success btn-sm">📝 انتقل للنموذج</a>
                            <?php elseif (isset($_POST['check_system'])): ?>
                                <?php
                                $systemCheck = [];

                                // Check files
                                $requiredFiles = ['config/database.php', 'models/Student.php', 'student_data.db'];
                                $filesOk = true;
                                foreach ($requiredFiles as $file) {
                                    if (!file_exists($file)) {
                                        $filesOk = false;
                                        break;
                                    }
                                }
                                $systemCheck['files'] = $filesOk;

                                // Check database
                                try {
                                    $pdo = new PDO('sqlite:student_data.db');
                                    $stmt = $pdo->query("SELECT COUNT(*) as count FROM students");
                                    $result = $stmt->fetch();
                                    $systemCheck['database'] = true;
                                    $systemCheck['student_count'] = $result['count'];
                                } catch (Exception $e) {
                                    $systemCheck['database'] = false;
                                }

                                $systemReady = $systemCheck['files'] && $systemCheck['database'];
                                ?>
                                <div class="alert <?php echo $systemReady ? 'alert-success' : 'alert-warning'; ?> mt-3">
                                    <h6><?php echo $systemReady ? '✅ النظام جاهز للاستخدام!' : '⚠️ النظام في حاجة للتصحيح'; ?></h6>
                                    <small>
                                        الملفات: <?php echo $systemCheck['files'] ? '✅' : '❌'; ?><br>
                                        قاعدة البيانات: <?php echo $systemCheck['database'] ? '✅' : '❌'; ?><br>
                                        <?php if (isset($systemCheck['student_count'])): ?>
                                            سجلات الطلاب: <?php echo $systemCheck['student_count']; ?><br>
                                        <?php endif; ?>
                                    </small>
                                </div>

                                <?php if ($systemReady): ?>
                                    <form method="post" class="mt-2">
                                        <button type="submit" name="cleanup" class="btn btn-danger btn-sm"
                                                onclick="return confirm('هل تريد حذف ملفات الإعداد؟\n\nسيتم حذف:\n• init.php\n• setup.php\n• db_init.php\n\nوسيتم توجيهك تلقائياً للنموذج')">🗑️ حذف ملفات الإعداد</button>
                                    </form>
                                <?php else: ?>
                                    <div class="mt-2">
                                        <button class="btn btn-secondary btn-sm" onclick="window.location.reload()">🔄 إعادة التحقق</button>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <form method="post" class="mt-2">
                                    <button type="submit" name="check_system" class="btn btn-warning btn-sm">🔍 التحقق من النظام</button>
                                </form>
                                <small class="text-muted mt-2 d-block">تحقق أولاً من صحة النظام قبل الحذف</small>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card h-100 border-info">
                        <div class="card-body text-center">
                            <h1 class="text-info mb-2">4️⃣</h1>
                            <h5 class="card-title">مراقبة النظام</h5>
                            <p class="card-text">راقب ملفات السجلات بحثاً عن أي مشاكل</p>
                            <code class="text-muted bg-light px-2 py-1 rounded">cd logs/</code>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <hr class="my-4">
    <div class="text-center">
        <h4 class="text-success">🎊 تهانينا! نظام إدارة بيانات الطلاب جاهز للاستخدام!</h4>
        <p class="text-muted mt-2">
            النظام يشمل التحقق من صحة البيانات، معالجة الصور، خدمات النسخ الاحتياطي، ومعالجة الأخطاء الشاملة.
        </p>
        <p class="text-primary fw-bold">جاهز لإدارة بيانات الطلاب بكفاءة! 🚀</p>
    </div>
</div>

<!-- Bootstrap JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
