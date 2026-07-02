<?php

$host = '127.0.0.1';
$user = 'root';
$pass = '';

$pdo_simple = new PDO("mysql:host=$host;dbname=simple_accounting;charset=utf8mb4", $user, $pass);
$pdo_arab = new PDO("mysql:host=$host;dbname=arab_db;charset=utf8mb4", $user, $pass);

// Disable foreign key checks temporarily during migration
$pdo_simple->exec("SET FOREIGN_KEY_CHECKS = 0;");

// 1. Sync Employees
echo "Syncing Employees...\n";
$stmt = $pdo_arab->query("SELECT * FROM employees");
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

$inserted_emp = 0;
foreach ($employees as $emp) {
    // Check/clean status to match enum('active','suspended','terminated')
    $status = strtolower($emp['status'] ?? 'active');
    if (!in_array($status, ['active', 'suspended', 'terminated'])) {
        $status = 'active';
    }
    
    // Check job_title and hire_date (NOT NULL in simple_accounting)
    $job_title = $emp['job_title'] ?? 'N/A';
    $hire_date = $emp['hire_date'] ?? date('Y-m-d');
    
    $insertStmt = $pdo_simple->prepare("
        INSERT INTO employees (
            id, employee_no, name, name_en, nationality, birth_date, iqama_no, operation_card_no,
            driver_card_no, transport_license_no, iqama_expiry, license_expiry, authorization_expiry,
            work_card_expiry, driver_card_expiry, transport_license_expiry, national_id, passport_no,
            passport_expiry, job_title, is_driver, department, hire_date, end_date, basic_salary,
            commission, housing_allowance, transport_allowance, other_allowances, bank_name,
            account_no, iban, phone, address, email, status, notes, created_at, updated_at,
            license_copy, iqama_copy, document_file, authorization_copy, operation_card_copy,
            driver_card_copy, combined_documents_pdf, vehicle_license_copy, work_card_copy, account_id
        ) VALUES (
            :id, :employee_no, :name, :name_en, :nationality, :birth_date, :iqama_no, :operation_card_no,
            :driver_card_no, :transport_license_no, :iqama_expiry, :license_expiry, :authorization_expiry,
            :work_card_expiry, :driver_card_expiry, :transport_license_expiry, :national_id, :passport_no,
            :passport_expiry, :job_title, :is_driver, :department, :hire_date, :end_date, :basic_salary,
            :commission, :housing_allowance, :transport_allowance, :other_allowances, :bank_name,
            :account_no, :iban, :phone, :address, :email, :status, :notes, :created_at, :updated_at,
            :license_copy, :iqama_copy, :document_file, :authorization_copy, :operation_card_copy,
            :driver_card_copy, :combined_documents_pdf, :vehicle_license_copy, :work_card_copy, :account_id
        ) ON DUPLICATE KEY UPDATE name = VALUES(name)
    ");
    
    $insertStmt->execute([
        ':id' => $emp['id'],
        ':employee_no' => $emp['employee_no'],
        ':name' => $emp['name'],
        ':name_en' => $emp['name_en'],
        ':nationality' => $emp['nationality'] ?? 'non_saudi',
        ':birth_date' => $emp['birth_date'],
        ':iqama_no' => $emp['iqama_no'],
        ':operation_card_no' => $emp['operation_card_no'],
        ':driver_card_no' => $emp['driver_card_no'],
        ':transport_license_no' => $emp['transport_license_no'],
        ':iqama_expiry' => $emp['iqama_expiry'],
        ':license_expiry' => $emp['license_expiry'],
        ':authorization_expiry' => $emp['authorization_expiry'],
        ':work_card_expiry' => $emp['work_card_expiry'],
        ':driver_card_expiry' => $emp['driver_card_expiry'],
        ':transport_license_expiry' => $emp['transport_license_expiry'],
        ':national_id' => $emp['national_id'],
        ':passport_no' => $emp['passport_no'],
        ':passport_expiry' => $emp['passport_expiry'],
        ':job_title' => $job_title,
        ':is_driver' => $emp['is_driver'],
        ':department' => $emp['department'],
        ':hire_date' => $hire_date,
        ':end_date' => $emp['end_date'],
        ':basic_salary' => $emp['basic_salary'] ?? 0.00,
        ':commission' => $emp['commission'] ?? 0.00,
        ':housing_allowance' => $emp['housing_allowance'] ?? 0.00,
        ':transport_allowance' => $emp['transport_allowance'] ?? 0.00,
        ':other_allowances' => $emp['other_allowances'] ?? 0.00,
        ':bank_name' => $emp['bank_name'],
        ':account_no' => $emp['account_no'],
        ':iban' => $emp['iban'],
        ':phone' => $emp['phone'],
        ':address' => $emp['address'],
        ':email' => $emp['email'],
        ':status' => $status,
        ':notes' => $emp['notes'],
        ':created_at' => $emp['created_at'],
        ':updated_at' => $emp['updated_at'],
        ':license_copy' => $emp['license_copy'],
        ':iqama_copy' => $emp['iqama_copy'],
        ':document_file' => $emp['document_file'],
        ':authorization_copy' => $emp['authorization_copy'],
        ':operation_card_copy' => $emp['operation_card_copy'],
        ':driver_card_copy' => $emp['driver_card_copy'],
        ':combined_documents_pdf' => $emp['combined_documents_pdf'],
        ':vehicle_license_copy' => $emp['vehicle_license_copy'],
        ':work_card_copy' => $emp['work_card_copy'],
        ':account_id' => $emp['account_id']
    ]);
    $inserted_emp++;
}
echo "Synced $inserted_emp employees.\n";

// 2. Sync Contacts
echo "Syncing Contacts...\n";
$stmt = $pdo_arab->query("SELECT * FROM contacts");
$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$inserted_contacts = 0;
foreach ($contacts as $contact) {
    // Check/clean type to match enum('customer','supplier','employee')
    $type = strtolower($contact['type']);
    if (!in_array($type, ['customer', 'supplier', 'employee'])) {
        if ($contact['is_customer']) $type = 'customer';
        elseif ($contact['is_supplier']) $type = 'supplier';
        else $type = 'customer'; // fallback
    }

    $insertStmt = $pdo_simple->prepare("
        INSERT INTO contacts (
            id, type, is_customer, is_supplier, is_main_company, is_sub_client,
            main_company_id, is_related_party, name, email, phone, tax_number,
            account_id, receivable_account_id, payable_account_id, notes, is_active,
            created_at, updated_at
        ) VALUES (
            :id, :type, :is_customer, :is_supplier, :is_main_company, :is_sub_client,
            :main_company_id, :is_related_party, :name, :email, :phone, :tax_number,
            :account_id, :receivable_account_id, :payable_account_id, :notes, :is_active,
            :created_at, :updated_at
        ) ON DUPLICATE KEY UPDATE name = VALUES(name)
    ");

    $insertStmt->execute([
        ':id' => $contact['id'],
        ':type' => $type,
        ':is_customer' => $contact['is_customer'],
        ':is_supplier' => $contact['is_supplier'],
        ':is_main_company' => $contact['is_main_company'],
        ':is_sub_client' => $contact['is_sub_client'],
        ':main_company_id' => $contact['main_company_id'],
        ':is_related_party' => $contact['is_related_party'],
        ':name' => $contact['name'],
        ':email' => $contact['email'],
        ':phone' => $contact['phone'],
        ':tax_number' => $contact['tax_number'],
        ':account_id' => $contact['account_id'],
        ':receivable_account_id' => $contact['receivable_account_id'],
        ':payable_account_id' => $contact['payable_account_id'],
        ':notes' => $contact['notes'],
        ':is_active' => $contact['is_active'],
        ':created_at' => $contact['created_at'],
        ':updated_at' => $contact['updated_at']
    ]);
    $inserted_contacts++;
}
echo "Synced $inserted_contacts contacts.\n";

$pdo_simple->exec("SET FOREIGN_KEY_CHECKS = 1;");
