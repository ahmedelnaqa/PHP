<?php
/**
 * Database Configuration for Production Use
 * SQLite Database with PDO and best practices
 */

/**
 * Database Configuration Class
 */
class DatabaseConfig
{
    // Database connection settings
    private const DB_TYPE = 'sqlite';
    private const DB_FILE = __DIR__ . '/../data/student_data.db';
    private const DB_BACKUP_DIR = __DIR__ . '/../data/backups/';

    // PDO Options for better security and performance (SQLite specific)
    private const PDO_OPTIONS = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false, // Use native prepared statements
        PDO::ATTR_STRINGIFY_FETCHES => false, // Return strings for all values
    ];

    private static $pdoInstance = null;
    private static $connectionCount = 0;

    /**
     * Get PDO instance (singleton pattern)
     *
     * @return PDO
     * @throws PDOException
     */
    public static function getConnection(): PDO
    {
        if (self::$pdoInstance === null) {
            try {
                self::ensureDatabaseExists();
                self::ensureBackupDirectoryExists();

                self::$pdoInstance = new PDO(
                    self::DB_TYPE . ':' . self::DB_FILE,
                    null, // SQLite doesn't use username
                    null, // SQLite doesn't use password
                    self::PDO_OPTIONS
                );

                // Configure SQLite specific settings
                self::$pdoInstance->exec('PRAGMA foreign_keys = ON'); // Enable foreign key constraints
                self::$pdoInstance->exec('PRAGMA journal_mode = WAL'); // Better concurrency
                self::$pdoInstance->exec('PRAGMA synchronous = NORMAL'); // Balance between speed and safety
                self::$pdoInstance->exec('PRAGMA cache_size = 1000000'); // 1MB cache
                self::$pdoInstance->exec('PRAGMA temp_store = MEMORY'); // Store temp tables in memory

                // Set busy timeout to handle database contention
                self::$pdoInstance->exec('PRAGMA busy_timeout = 30000'); // 30 seconds

                self::logConnection('Database connection established successfully');

        } catch (PDOException $e) {
            self::logError('Database connection failed: ' . $e->getMessage());
            throw new PDOException('Database connection failed: ' . $e->getMessage(), intval($e->getCode()));
        }
        }

        self::$connectionCount++;
        return self::$pdoInstance;
    }

    /**
     * Ensure database file exists and create if needed
     */
    private static function ensureDatabaseExists(): void
    {
        $dbDir = dirname(self::DB_FILE);

        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0755, true);
        }

        if (!file_exists(self::DB_FILE)) {
            self::initializeDatabase();
        }
    }

    /**
     * Ensure backup directory exists
     */
    private static function ensureBackupDirectoryExists(): void
    {
        if (!is_dir(self::DB_BACKUP_DIR)) {
            mkdir(self::DB_BACKUP_DIR, 0755, true);
        }
    }

    /**
     * Initialize database with schema
     */
    private static function initializeDatabase(): void
    {
        try {
            $pdo = new PDO(self::DB_TYPE . ':' . self::DB_FILE, null, null, self::PDO_OPTIONS);

            // Enable foreign keys
            $pdo->exec('PRAGMA foreign_keys = ON');

            // Create students table
            $createTableSQL = "
                CREATE TABLE IF NOT EXISTS students (
                    nid VARCHAR(14) PRIMARY KEY,
                    nationality VARCHAR(50) NOT NULL DEFAULT 'Egyptian',
                    name_ar VARCHAR(35) NOT NULL,
                    name_en VARCHAR(35) NOT NULL,
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
                );
            ";

            $pdo->exec($createTableSQL);

            // Create indexes for better performance
            $pdo->exec('CREATE INDEX IF NOT EXISTS idx_name_ar ON students(name_ar)');
            $pdo->exec('CREATE INDEX IF NOT EXISTS idx_name_en ON students(name_en)');
            $pdo->exec('CREATE INDEX IF NOT EXISTS idx_email ON students(email)');
            $pdo->exec('CREATE INDEX IF NOT EXISTS idx_created_at ON students(created_at)');

            self::logConnection('Database initialized successfully');

        } catch (PDOException $e) {
            self::logError('Database initialization failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create database backup
     *
     * @return string Backup file path
     * @throws Exception
     */
    public static function createBackup(): string
    {
        $backupFile = self::DB_BACKUP_DIR . 'backup_' . date('Y-m-d_H-i-s') . '.db';

        try {
            if (!copy(self::DB_FILE, $backupFile)) {
                throw new Exception('Failed to create backup');
            }

            self::logConnection('Database backup created: ' . $backupFile);
            return $backupFile;

        } catch (Exception $e) {
            self::logError('Backup creation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get database statistics
     *
     * @return array
     */
    public static function getStats(): array
    {
        if (self::$pdoInstance === null) {
            self::getConnection();
        }

        $stats = [
            'connection_count' => self::$connectionCount,
            'database_size' => filesize(self::DB_FILE),
            'readable_size' => self::formatSize(filesize(self::DB_FILE))
        ];

        try {
            $stmt = self::$pdoInstance->query("SELECT COUNT(*) as total_students FROM students");
            $result = $stmt->fetch();
            $stats['total_students'] = $result['total_students'];
        } catch (PDOException $e) {
            $stats['total_students'] = 'Error: ' . $e->getMessage();
        }

        return $stats;
    }

    /**
     * Format file size
     *
     * @param int $bytes
     * @return string
     */
    private static function formatSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < 3) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Log connection events
     *
     * @param string $message
     */
    public static function logConnection(string $message): void
    {
        $logFile = __DIR__ . '/../logs/database.log';

        if (!is_dir(dirname($logFile))) {
            mkdir(dirname($logFile), 0755, true);
        }

        $logMessage = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }

    /**
     * Log errors
     *
     * @param string $message
     */
    public static function logError(string $message): void
    {
        $errorFile = __DIR__ . '/../logs/database_errors.log';

        if (!is_dir(dirname($errorFile))) {
            mkdir(dirname($errorFile), 0755, true);
        }

        $errorMessage = '[' . date('Y-m-d H:i:s') . '] ERROR: ' . $message . PHP_EOL;
        file_put_contents($errorFile, $errorMessage, FILE_APPEND | LOCK_EX);
    }

    /**
     * Test database connection
     *
     * @return bool
     */
    public static function testConnection(): bool
    {
        try {
            $pdo = self::getConnection();
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Close database connection
     */
    public static function closeConnection(): void
    {
        if (self::$pdoInstance !== null) {
            self::$pdoInstance = null;
            self::logConnection('Database connection closed');
        }
        self::$connectionCount = 0;
    }
}

/**
 * Database Helper Functions
 */

/**
 * Execute query with error handling
 *
 * @param string $sql
 * @param array $params
 * @return PDOStatement
 * @throws PDOException
 */
function executeQuery(string $sql, array $params = []): PDOStatement
{
    try {
        $pdo = DatabaseConfig::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        DatabaseConfig::logConnection('Query executed: ' . substr($sql, 0, 100) . '...');
        return $stmt;

    } catch (PDOException $e) {
        DatabaseConfig::logError('Query execution failed: ' . $e->getMessage() . ' | SQL: ' . $sql);
        throw $e;
    }
}

/**
 * Execute transaction
 *
 * @param callable $callback
 * @return mixed
 * @throws Exception
 */
function executeTransaction(callable $callback)
{
    $pdo = DatabaseConfig::getConnection();
    $pdo->beginTransaction();

    try {
        $result = $callback($pdo);
        $pdo->commit();
        DatabaseConfig::logConnection('Transaction committed successfully');
        return $result;

    } catch (Exception $e) {
        $pdo->rollBack();
        DatabaseConfig::logError('Transaction rolled back: ' . $e->getMessage());
        throw $e;
    }
}

// Prevent direct access
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    header('HTTP/1.1 403 Forbidden', true, 403);
    echo 'Direct access not allowed';
    exit;
}
?>
