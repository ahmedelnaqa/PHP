<?php
/**
 * Student Class for Database Operations
 * Follows Repository pattern and best practices
 */

require_once __DIR__ . '/../config/database.php';

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
        'nid' => [
            'pattern' => '/^\d{14}$/',
            'max_length' => 14,
            'message' => 'رقم البطاقة الشخصية يجب أن يكون 14 رقم بالضبط'
        ],
        'name_ar' => [
            'max_length' => 35,
            'message' => 'الاسم بالعربية يجب ألا يزيد عن 35 حرف'
        ],
        'name_en' => [
            'pattern' => '/^[A-Za-z\s]{1,35}$/',
            'max_length' => 35,
            'message' => 'الاسم بالإنجليزية يجب أن يكون أحرف إنجليزية فقط، بحد أقصى 35 حرف'
        ],
        'name_on_card' => [
            'pattern' => '/^[A-Za-z\s]{1,20}$/',
            'max_length' => 20,
            'message' => 'Name on Card must be English letters only, max 20 characters.'
        ],
        'home_number' => [
            'pattern' => '/^0?\d{9,10}$/',
            'max_length' => 10,
            'message' => 'رقم الهاتف المنزلي يجب أن يكون 10 أرقام، يمكن أن يبدأ بالصفر'
        ],
        'mobile_number' => [
            'pattern' => '/^0\d{10}$/',
            'max_length' => 11,
            'message' => 'رقم الهاتف المحمول يجب أن يكون 11 رقم، يبدأ بالصفر'
        ],
        'address_ar' => [
            'max_length' => 35,
            'message' => 'العنوان بالعربية يجب ألا يزيد عن 35 حرف'
        ],
        'address_en' => [
            'pattern' => '/^[A-Za-z\s]{1,35}$/',
            'max_length' => 35,
            'message' => 'العنوان بالإنجليزية يجب أن يكون أحرف إنجليزية فقط، بحد أقصى 35 حرف'
        ],
        'birthday' => [
            'pattern' => '/^\d{4}-\d{2}-\d{2}$/',
            'message' => 'تاريخ الميلاد يجب أن يكون بتنسيق YYYY-MM-DD'
        ],
        'email' => [
            'pattern' => '/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/u',
            'max_length' => 100,
            'message' => 'البريد الإلكتروني غير صحيح'
        ]
    ];

    /**
     * Constructor
     */
    public function __construct(?int $id = null)
    {
        $this->id = $id;
        $this->nationality = 'Egyptian';
        $this->gender = 'Male';
        $this->facultyName = 'العلوم الرياضية للبنين';

        // Initialize typed properties
        $this->nid = '';
        $this->nameAr = '';
        $this->nameEn = '';
        $this->nameOnCard = '';
        $this->homeNumber = '';
        $this->mobileNumber = '';
        $this->addressAr = '';
        $this->addressEn = '';
        $this->birthday = '';
        $this->email = '';
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getNid(): string { return $this->nid; }
    public function getNationality(): string { return $this->nationality; }
    public function getNameAr(): string { return $this->nameAr; }
    public function getNameEn(): string { return $this->nameEn; }
    public function getNameOnCard(): string { return $this->nameOnCard; }
    public function getHomeNumber(): string { return $this->homeNumber; }
    public function getMobileNumber(): string { return $this->mobileNumber; }
    public function getAddressAr(): string { return $this->addressAr; }
    public function getAddressEn(): string { return $this->addressEn; }
    public function getGender(): string { return $this->gender; }
    public function getBirthday(): string { return $this->birthday; }
    public function getEmail(): string { return $this->email; }
    public function getFacultyName(): string { return $this->facultyName; }
    public function getImagePath(): ?string { return $this->imagePath; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function getUpdatedAt(): ?string { return $this->updatedAt; }

    // Setters with validation
    public function setNid(string $nid): void {
        $this->validateField('nid', $nid);
        $this->nid = $nid;
    }

    public function setNationality(string $nationality): void {
        $this->nationality = $nationality;
    }

    public function setNameAr(string $nameAr): void {
        $this->validateField('name_ar', $nameAr);
        $this->nameAr = $nameAr;
    }

    public function setNameEn(string $nameEn): void {
        $this->validateField('name_en', $nameEn);
        $this->nameEn = $nameEn;
    }

    public function setNameOnCard(string $nameOnCard): void {
        $this->validateField('name_on_card', $nameOnCard);
        $this->nameOnCard = $nameOnCard;
    }

    public function setHomeNumber(string $homeNumber): void {
        $this->validateField('home_number', $homeNumber);
        $this->homeNumber = $homeNumber;
    }

    public function setMobileNumber(string $mobileNumber): void {
        $this->validateField('mobile_number', $mobileNumber);
        $this->mobileNumber = $mobileNumber;
    }

    public function setAddressAr(string $addressAr): void {
        $this->validateField('address_ar', $addressAr);
        $this->addressAr = $addressAr;
    }

    public function setAddressEn(string $addressEn): void {
        $this->validateField('address_en', $addressEn);
        $this->addressEn = $addressEn;
    }

    public function setGender(string $gender): void {
        $this->gender = $gender;
    }

    public function setBirthday(string $birthday): void {
        $this->validateField('birthday', $birthday);
        $this->birthday = $birthday;
    }

    public function setEmail(string $email): void {
        $this->validateField('email', $email);
        $this->email = $email;
    }

    public function setFacultyName(string $facultyName): void {
        $this->facultyName = $facultyName;
    }

    public function setImagePath(?string $imagePath): void {
        $this->imagePath = $imagePath;
    }

    /**
     * Validate a field against validation rules
     *
     * @param string $field
     * @param string $value
     * @throws InvalidArgumentException
     */
    private function validateField(string $field, string $value): void
    {
        $rules = isset(self::VALIDATION_RULES[$field]) ? self::VALIDATION_RULES[$field] : [];

        // Check max length, using mb_strlen for multi-byte character safety
        if (isset($rules['max_length']) && mb_strlen($value, 'UTF-8') > $rules['max_length']) {
            throw new InvalidArgumentException($rules['message']);
        }

        // Check pattern
        if (isset($rules['pattern']) && !preg_match($rules['pattern'], $value)) {
            throw new InvalidArgumentException($rules['message']);
        }

        // Check empty (for required fields)
        if (isset($rules['required']) && $rules['required'] && empty(trim($value))) {
            throw new InvalidArgumentException($field . ' مطلوب ولا يمكن أن يكون فارغ');
        }
    }

    /**
     * Save student to database
     *
     * @return void
     * @throws PDOException
     */
    public function save(): void
    {
        if (!$this->nid) {
            throw new RuntimeException('NID is required for saving student');
        }

        if ($this->exists()) {
            $this->update();
        } else {
            $this->insert();
        }
    }

    /**
     * Insert new student
     */
    private function insert(): void
    {
        $sql = "INSERT INTO students (
            nid, nationality, name_ar, name_en, name_on_card, home_number, mobile_number,
            address_ar, address_en, gender, birthday, email, faculty_name, image_path
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $params = [
            $this->nid,
            $this->nationality,
            $this->nameAr,
            $this->nameEn,
            $this->nameOnCard,
            $this->homeNumber,
            $this->mobileNumber,
            $this->addressAr,
            $this->addressEn,
            $this->gender,
            $this->birthday,
            $this->email,
            $this->facultyName,
            $this->imagePath
        ];

        executeQuery($sql, $params);
        $this->id = DatabaseConfig::getConnection()->lastInsertId();
    }

    /**
     * Update existing student
     */
    private function update(): void
    {
        $sql = "UPDATE students SET
            nationality = ?, name_ar = ?, name_en = ?, name_on_card = ?,
            home_number = ?, mobile_number = ?, address_ar = ?, address_en = ?, gender = ?,
            birthday = ?, email = ?, faculty_name = ?, image_path = ?,
            updated_at = CURRENT_TIMESTAMP
            WHERE nid = ?";

        $params = [
            $this->nationality,
            $this->nameAr,
            $this->nameEn,
            $this->nameOnCard,
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
        ];

        executeQuery($sql, $params);
    }

    /**
     * Delete student from database
     *
     * @return bool
     */
    public function delete(): bool
    {
        if (!$this->nid) {
            return false;
        }

        try {
            $sql = "DELETE FROM students WHERE nid = ?";
            executeQuery($sql, [$this->nid]);

            // Remove associated image file if it exists
            if ($this->imagePath && file_exists($this->imagePath)) {
                unlink($this->imagePath);
            }

            return true;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    /**
     * Check if student exists in database
     *
     * @return bool
     */
    public function exists(): bool
    {
        $sql = "SELECT COUNT(*) as count FROM students WHERE nid = ?";
        $stmt = executeQuery($sql, [$this->nid]);
        $result = $stmt->fetch();

        return $result['count'] > 0;
    }

    /**
     * Find student by NID
     *
     * @param string $nid
     * @return Student|null
     */
    public static function findByNid(string $nid): ?Student
    {
        $sql = "SELECT * FROM students WHERE nid = ?";
        $stmt = executeQuery($sql, [$nid]);
        $result = $stmt->fetch();

        if (!$result) {
            return null;
        }

        return self::createFromArray($result);
    }

    /**
     * Get all students
     *
     * @param int $limit
     * @param int $offset
     * @return array<Student>
     */
    public static function all(int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT * FROM students ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $stmt = executeQuery($sql, [$limit, $offset]);

        $students = [];
        while ($row = $stmt->fetch()) {
            $students[] = self::createFromArray($row);
        }

        return $students;
    }

    /**
     * Search students
     *
     * @param string $query
     * @param int $limit
     * @return array<Student>
     */
    public static function search(string $query, int $limit = 20): array
    {
        $sql = "SELECT * FROM students WHERE
            nid LIKE ? OR name_ar LIKE ? OR name_en LIKE ? OR email LIKE ?
            ORDER BY name_ar LIMIT ?";

        $searchPattern = '%' . $query . '%';
        $params = [$searchPattern, $searchPattern, $searchPattern, $searchPattern, $limit];

        $stmt = executeQuery($sql, $params);

        $students = [];
        while ($row = $stmt->fetch()) {
            $students[] = self::createFromArray($row);
        }

        return $students;
    }

    /**
     * Get total count of students
     *
     * @return int
     */
    public static function count(): int
    {
        $sql = "SELECT COUNT(*) as count FROM students";
        $stmt = executeQuery($sql);
        $result = $stmt->fetch();

        return (int)$result['count'];
    }

    /**
     * Create Student object from database row
     *
     * @param array $data
     * @return Student
     */
    private static function createFromArray(array $data): Student
    {
        $student = new self();

        foreach ($data as $key => $value) {
            $method = 'set' . str_replace('_', '', ucwords($key, '_'));
            $property = lcfirst(str_replace('_', '', ucwords($key, '_')));

            if (property_exists($student, $property)) {
                if ($value !== null) {
                    $student->$property = $value;
                }
            }
        }

        return $student;
    }

    /**
     * Convert object to array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'nid' => $this->nid,
            'nationality' => $this->nationality,
            'name_ar' => $this->nameAr,
            'name_en' => $this->nameEn,
            'name_on_card' => $this->nameOnCard,
            'home_number' => $this->homeNumber,
            'mobile_number' => $this->mobileNumber,
            'address_ar' => $this->addressAr,
            'address_en' => $this->addressEn,
            'gender' => $this->gender,
            'birthday' => $this->birthday,
            'email' => $this->email,
            'faculty_name' => $this->facultyName,
            'image_path' => $this->imagePath,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
    }

    /**
     * Validate all fields
     *
     * @throws InvalidArgumentException
     */
    public function validateAll(): void
    {
        foreach (self::VALIDATION_RULES as $field => $rules) {
            $getter = 'get' . str_replace('_', '', ucwords($field, '_'));
            if (method_exists($this, $getter)) {
                $value = $this->$getter();
                $this->validateField($field, $value);
            }
        }
    }
}

// Prevent direct access
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    header('HTTP/1.1 403 Forbidden', true, 403);
    echo 'Direct access not allowed';
    exit;
}
?>
