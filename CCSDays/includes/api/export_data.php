<?php
session_start();
require_once '../config.php';

// Initialize PDO connection
try {
    $pdo = getDbConnection();
} catch (Exception $e) {
    http_response_code(500);
    exit('Database connection failed: ' . $e->getMessage());
}

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    exit('Unauthorized');
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

// Get export parameters
$format = $_POST['format'] ?? 'excel';
$dataType = $_POST['dataType'] ?? 'all';
$dateRange = $_POST['dateRange'] ?? 'all';
$startDate = $_POST['startDate'] ?? '';
$endDate = $_POST['endDate'] ?? '';

// Validate format
if (!in_array($format, ['pdf', 'excel'])) {
    http_response_code(400);
    exit('Invalid format');
}

try {
    // Get data based on type
    $data = getData($dataType, $dateRange, $startDate, $endDate);
    
    if ($format === 'excel') {
        generateExcel($data, $dataType, $dateRange);
    } else {
        generatePDF($data, $dataType, $dateRange);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    exit('Export failed: ' . $e->getMessage());
}

function getData($dataType, $dateRange, $startDate, $endDate) {
    global $pdo;
    
    $data = [];
    
    switch ($dataType) {
        case 'students':
            // Get all students data grouped by year level with attendance count
            $data = [];
            
            // Get students for each year level
            for ($year = 1; $year <= 4; $year++) {
                $sql = "SELECT s.Student_ID, s.Name, s.Gender, s.Year, s.College,
                               COUNT(a.Attendance_ID) as attendance_count
                        FROM students s 
                        LEFT JOIN attendance a ON s.Student_ID = a.Student_ID 
                        WHERE s.Year = ? 
                        GROUP BY s.Student_ID 
                        ORDER BY s.Name ASC";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$year]);
                $yearData = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (!empty($yearData)) {
                    $yearLabel = match($year) {
                        1 => 'First Year',
                        2 => 'Second Year', 
                        3 => 'Third Year',
                        4 => 'Fourth Year'
                    };
                    $data[$yearLabel] = $yearData;
                }
            }
            
            return $data;
            
        case 'events':
            $sql = "SELECT id, name, event_date, venue, description, reminder_enabled, 
                           reminder_time, status, created_at, updated_at 
                    FROM events WHERE 1=1";
            $params = [];
            
            // Add date filtering for events
            if ($dateRange !== 'all') {
                switch ($dateRange) {
                    case 'today':
                        $sql .= " AND DATE(created_at) = CURDATE()";
                        break;
                    case 'week':
                        $sql .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
                        break;
                    case 'month':
                        $sql .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
                        break;
                    case 'custom':
                        if ($startDate && $endDate) {
                            $sql .= " AND DATE(created_at) BETWEEN ? AND ?";
                            $params[] = $startDate;
                            $params[] = $endDate;
                        }
                        break;
                }
            }
            
            $sql .= " ORDER BY event_date DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        case 'attendance':
            $sql = "SELECT a.Attendance_ID, a.Student_ID, s.Name as Student_Name, 
                           a.QR_Code, a.Sign_In_Time, a.Sign_Out_Time
                    FROM attendance a 
                    LEFT JOIN students s ON a.Student_ID = s.Student_ID 
                    WHERE 1=1";
            $params = [];
            
            // Add date filtering for attendance
            if ($dateRange !== 'all') {
                switch ($dateRange) {
                    case 'today':
                        $sql .= " AND DATE(a.Sign_In_Time) = CURDATE()";
                        break;
                    case 'week':
                        $sql .= " AND a.Sign_In_Time >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
                        break;
                    case 'month':
                        $sql .= " AND a.Sign_In_Time >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
                        break;
                    case 'custom':
                        if ($startDate && $endDate) {
                            $sql .= " AND DATE(a.Sign_In_Time) BETWEEN ? AND ?";
                            $params[] = $startDate;
                            $params[] = $endDate;
                        }
                        break;
                }
            }
            
            $sql .= " ORDER BY a.Sign_In_Time DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        default: // 'all'
            // Get all data types
            $data['students'] = getData('students', $dateRange, $startDate, $endDate);
            $data['events'] = getData('events', $dateRange, $startDate, $endDate);
            $data['attendance'] = getData('attendance', $dateRange, $startDate, $endDate);
            return $data;
    }
}

function generateExcel($data, $dataType, $dateRange) {
    // Set headers for Excel download
    $filename = "ccs_days_export_" . $dataType . "_" . date('Y-m-d_H-i-s') . ".csv";
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Expires: 0');
    
    // Create file pointer connected to the output stream
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    if ($dataType === 'all') {
        // Export all data types
        foreach ($data as $type => $records) {
            if (!empty($records)) {
                // Handle students data with year level separation
                if ($type === 'students') {
                    fputcsv($output, [strtoupper($type) . ' DATA']);
                    fputcsv($output, []);
                    
                    foreach ($records as $yearLevel => $yearStudents) {
                        if (!empty($yearStudents)) {
                            // Add year level header
                            fputcsv($output, [$yearLevel . ' (' . count($yearStudents) . ' students)']);
                            fputcsv($output, []);
                            
                            // Add column headers
                            $headers = [];
                            foreach (array_keys($yearStudents[0]) as $header) {
                                $displayHeader = str_replace('_', ' ', $header);
                                if ($header === 'Student_ID') $displayHeader = 'Student ID';
                                if ($header === 'attendance_count') $displayHeader = 'Attendance Count';
                                if ($header === 'College') $displayHeader = 'Course';
                                $headers[] = ucwords($displayHeader);
                            }
                            fputcsv($output, $headers);
                            
                            // Add student data
                            foreach ($yearStudents as $student) {
                                fputcsv($output, array_values($student));
                            }
                            
                            fputcsv($output, []); // Empty row between year levels
                        }
                    }
                } else {
                    // Handle other data types normally
                    fputcsv($output, [strtoupper($type) . ' DATA (' . count($records) . ' records)']);
                    fputcsv($output, []); // Empty row
                    
                    // Add column headers with proper formatting
                    $headers = [];
                    foreach (array_keys($records[0]) as $header) {
                        $displayHeader = str_replace('_', ' ', $header);
                        if ($header === 'Student_ID') $displayHeader = 'Student ID';
                        if ($header === 'Sign_In_Time') $displayHeader = 'Sign In Time';
                        if ($header === 'Sign_Out_Time') $displayHeader = 'Sign Out Time';
                        if ($header === 'QR_Code') $displayHeader = 'QR Code';
                        if ($header === 'Student_Name') $displayHeader = 'Student Name';
                        if ($header === 'Attendance_ID') $displayHeader = 'Attendance ID';
                        $headers[] = ucwords($displayHeader);
                    }
                    fputcsv($output, $headers);
                    
                    // Add data rows with formatted values
                    foreach ($records as $record) {
                        $row = [];
                        foreach ($record as $key => $value) {
                            if ($key === 'Sign_In_Time' || $key === 'Sign_Out_Time') {
                                $value = $value ? date('M j, Y g:i A', strtotime($value)) : 'Not recorded';
                            }
                            if ($key === 'event_date') {
                                $value = $value ? date('M j, Y g:i A', strtotime($value)) : '';
                            }
                            if ($key === 'created_at' || $key === 'updated_at') {
                                $value = $value ? date('M j, Y g:i A', strtotime($value)) : '';
                            }
                            $row[] = $value ?? '';
                        }
                        fputcsv($output, $row);
                    }
                }
                
                // Add empty rows between sections
                fputcsv($output, []);
                fputcsv($output, []);
            }
        }
    } else {
        // Export single data type
        if (!empty($data)) {
            // Handle students data with year level separation
            if ($dataType === 'students') {
                foreach ($data as $yearLevel => $yearStudents) {
                    if (!empty($yearStudents)) {
                        // Add year level header
                        fputcsv($output, [$yearLevel . ' (' . count($yearStudents) . ' students)']);
                        fputcsv($output, []);
                        
                        // Add column headers
                        $headers = [];
                        foreach (array_keys($yearStudents[0]) as $header) {
                            $displayHeader = str_replace('_', ' ', $header);
                            if ($header === 'Student_ID') $displayHeader = 'Student ID';
                            if ($header === 'attendance_count') $displayHeader = 'Attendance Count';
                            if ($header === 'College') $displayHeader = 'Course';
                            $headers[] = ucwords($displayHeader);
                        }
                        fputcsv($output, $headers);
                        
                        // Add student data
                        foreach ($yearStudents as $student) {
                            fputcsv($output, array_values($student));
                        }
                        
                        fputcsv($output, []); // Empty row between year levels
                    }
                }
            } else {
                // Handle other data types normally
                $headers = [];
                foreach (array_keys($data[0]) as $header) {
                    $displayHeader = str_replace('_', ' ', $header);
                    if ($header === 'Student_ID') $displayHeader = 'Student ID';
                    if ($header === 'Sign_In_Time') $displayHeader = 'Sign In Time';
                    if ($header === 'Sign_Out_Time') $displayHeader = 'Sign Out Time';
                    if ($header === 'QR_Code') $displayHeader = 'QR Code';
                    if ($header === 'Student_Name') $displayHeader = 'Student Name';
                    if ($header === 'Attendance_ID') $displayHeader = 'Attendance ID';
                    $headers[] = ucwords($displayHeader);
                }
                fputcsv($output, $headers);
                
                // Add data rows with formatted values
                foreach ($data as $record) {
                    $row = [];
                    foreach ($record as $key => $value) {
                        if ($key === 'Sign_In_Time' || $key === 'Sign_Out_Time') {
                            $value = $value ? date('M j, Y g:i A', strtotime($value)) : 'Not recorded';
                        }
                        if ($key === 'event_date') {
                            $value = $value ? date('M j, Y g:i A', strtotime($value)) : '';
                        }
                        if ($key === 'created_at' || $key === 'updated_at') {
                            $value = $value ? date('M j, Y g:i A', strtotime($value)) : '';
                        }
                        $row[] = $value ?? '';
                    }
                    fputcsv($output, $row);
                }
            }
        }
    }
    
    fclose($output);
    exit;
}

// Helper function to create PDF tables
function createPDFTable($pdf, $data) {
    if (empty($data) || !is_array($data)) return;
    
    // Ensure we have valid data structure
    if (!isset($data[0]) || !is_array($data[0])) return;
    
    // Get headers
    $headers = array_keys($data[0]);
    if (empty($headers)) return;
    
    // Calculate total available width (page width minus margins)
    $totalWidth = 180; // A4 width minus margins
    
    // Define column priorities and minimum widths
    $colConfig = [];
    foreach ($headers as $header) {
        switch ($header) {
            case 'Student_ID':
                $colConfig[] = ['min' => 30, 'priority' => 3];
                break;
            case 'Student_Name':
            case 'Name':
                $colConfig[] = ['min' => 60, 'priority' => 1]; // Larger for full names
                break;
            case 'College':
            case 'Course':
                $colConfig[] = ['min' => 25, 'priority' => 2];
                break;
            case 'Year_Level':
            case 'Year':
                $colConfig[] = ['min' => 15, 'priority' => 4];
                break;
            case 'Gender':
                $colConfig[] = ['min' => 15, 'priority' => 4];
                break;
            case 'attendance_count':
                $colConfig[] = ['min' => 30, 'priority' => 3]; // Wider for attendance count
                break;
            case 'time_in':
            case 'time_out':
            case 'Sign_In_Time':
            case 'Sign_Out_Time':
            case 'event_date':
            case 'created_at':
            case 'updated_at':
                $colConfig[] = ['min' => 30, 'priority' => 2];
                break;
            default:
                $colConfig[] = ['min' => 25, 'priority' => 3];
                break;
        }
    }
    
    // Calculate balanced column widths with scaling if needed
    $colWidths = [];
    $mins = array_column($colConfig, 'min');
    $totalMinWidth = array_sum($mins);
    
    if ($totalMinWidth <= $totalWidth) {
        // Distribute extra space proportionally across all columns
        $extraSpace = $totalWidth - $totalMinWidth;
        foreach ($mins as $min) {
            $proportion = $min / max(1, $totalMinWidth);
            $colWidths[] = $min + ($extraSpace * $proportion);
        }
    } else {
        // Lock critical columns and scale the rest to fit
        $lockedIndices = [];
        foreach ($headers as $i => $h) {
            if (in_array($h, ['Student_ID', 'Student_Name', 'Name'])) {
                $lockedIndices[] = $i;
            }
        }
        $lockedWidth = 0;
        foreach ($lockedIndices as $i) { $lockedWidth += $mins[$i]; }
        $remainingWidth = max(40, $totalWidth - $lockedWidth); // leave room for others
        
        // Sum of mins for non-locked columns
        $othersMin = 0;
        foreach ($mins as $i => $min) { if (!in_array($i, $lockedIndices)) $othersMin += $min; }
        $scale = $othersMin > 0 ? min(1.0, $remainingWidth / $othersMin) : 1.0;
        
        foreach ($headers as $i => $h) {
            if (in_array($i, $lockedIndices)) {
                $colWidths[$i] = $mins[$i];
            } else {
                $colWidths[$i] = max(10, $mins[$i] * $scale);
            }
        }
        
        // If we still have rounding differences, adjust Name column to absorb
        $sum = array_sum($colWidths);
        $delta = $totalWidth - $sum;
        if (abs($delta) >= 0.5) {
            // Prefer to adjust Student_Name/Name if present, else first column
            $nameIdx = array_search('Student_Name', $headers);
            if ($nameIdx === false) $nameIdx = array_search('Name', $headers);
            if ($nameIdx === false) $nameIdx = 0;
            $colWidths[$nameIdx] = max(10, $colWidths[$nameIdx] + $delta);
        }
    }
    
    // Table headers
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetFillColor(76, 114, 115); // #4c7273
    $pdf->SetTextColor(255, 255, 255); // white
    
    foreach ($headers as $index => $header) {
        $displayHeader = str_replace('_', ' ', $header);
        if ($header === 'Student_ID') $displayHeader = 'Student ID';
        if ($header === 'attendance_count') $displayHeader = 'Attend Count'; // Shortened to prevent overlap
        if ($header === 'College') $displayHeader = 'Course';
        if ($header === 'Sign_In_Time') $displayHeader = 'Sign In Time';
        if ($header === 'Sign_Out_Time') $displayHeader = 'Sign Out Time';
        if ($header === 'Student_Name') $displayHeader = 'Student Name';
        if ($header === 'Year_Level' || $header === 'Year') $displayHeader = 'Year Level';
        
        // Use smaller font for headers if text is long
        if (strlen($displayHeader) > 12) {
            $pdf->SetFont('Arial', 'B', 7);
        }
        
        $pdf->Cell($colWidths[$index], 8, ucwords($displayHeader), 1, 0, 'C', true);
        
        // Reset font size
        if (strlen($displayHeader) > 12) {
            $pdf->SetFont('Arial', 'B', 8);
        }
    }
    $pdf->Ln();
    
    // Table data
    $pdf->SetFont('Arial', '', 7);
    $pdf->SetTextColor(51, 51, 51); // #333
    $fill = false;
    
    foreach ($data as $row) {
        if (!is_array($row)) continue;
        
        $pdf->SetFillColor(249, 249, 249); // #f9f9f9 for alternating rows
        
        // Check if we need a new page
        if ($pdf->GetY() > 250) {
            $pdf->AddPage();
        }
        
        $rowValues = array_values($row);
        foreach ($rowValues as $index => $value) {
            if (!isset($headers[$index]) || !isset($colWidths[$index])) continue;
            
            $key = $headers[$index];
            
            // Format dates safely
            if (in_array($key, ['time_in', 'time_out', 'event_date', 'created_at', 'updated_at', 'Sign_In_Time', 'Sign_Out_Time'])) {
                if (!empty($value) && $value !== '0000-00-00 00:00:00') {
                    $timestamp = strtotime($value);
                    $value = $timestamp ? date('M j, Y g:i A', $timestamp) : ($key === 'time_out' || $key === 'Sign_Out_Time' ? 'Not recorded' : '');
                } else {
                    $value = ($key === 'time_out' || $key === 'Sign_Out_Time' ? 'Not recorded' : '');
                }
            }
            
            // Handle text based on column type
            $cellText = (string)($value ?? '');
            
            // Never truncate student names or IDs - show full content
            if (!in_array($key, ['Student_Name', 'Name', 'Student_ID'])) {
                $maxChars = floor($colWidths[$index] / 2.5); // Approximate characters per width unit
                if (strlen($cellText) > $maxChars) {
                    $cellText = substr($cellText, 0, $maxChars - 3) . '...';
                }
            }
            
            // Center align numeric columns (Student_ID, attendance_count)
            $alignment = 'L';
            if (in_array($key, ['Student_ID', 'attendance_count', 'Year_Level'])) {
                $alignment = 'C';
            }
            
            // Use smaller font for long names or long IDs to fit better
            $reducedFontApplied = false;
            if (in_array($key, ['Student_Name', 'Name']) && strlen($cellText) > 25) {
                $pdf->SetFont('Arial', '', 6);
                $reducedFontApplied = true;
            }
            if ($key === 'Student_ID' && strlen($cellText) > 14) {
                $pdf->SetFont('Arial', '', 6);
                $reducedFontApplied = true;
            }
            
            $pdf->Cell($colWidths[$index], 6, $cellText, 1, 0, $alignment, $fill);
            
            // Reset font size after cell
            if ($reducedFontApplied) {
                $pdf->SetFont('Arial', '', 7);
            }
        }
        $pdf->Ln();
        $fill = !$fill; // Alternate row colors
    }
}

function generatePDF($data, $dataType, $dateRange) {
    // Set timezone to Philippine time
    date_default_timezone_set('Asia/Manila');
    try {
        require_once('../fpdf.php');
        
        $filename = "ccs_days_export_" . $dataType . "_" . date('Y-m-d_H-i-s') . ".pdf";
        
        // Validate data
        if (empty($data)) {
            throw new Exception("No data available for export");
        }
        
        // Create PDF instance
        $pdf = new FPDF();
        $pdf->SetAutoPageBreak(true, 20);
        $pdf->SetMargins(15, 15, 15);
        
        // Add first page
        $pdf->AddPage();
    } catch (Exception $e) {
        // Handle errors gracefully
        header('Content-Type: text/html; charset=UTF-8');
        echo "<script>alert('PDF Export Error: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
        exit;
    }
    
    // Header with logos
    $leftLogo = dirname(__DIR__) . '/images/spc-ccs-logo.png';
    $rightLogo = dirname(__DIR__) . '/images/spc-logo.png';
    if (file_exists($leftLogo)) {
        $pdf->Image($leftLogo, 15, 10, 25, 25); // Left top corner
    }
    if (file_exists($rightLogo)) {
        $pdf->Image($rightLogo, 170, 10, 25, 25); // Right top corner (A4 width ~210mm, margin 15mm)
    }
    $pdf->SetFont('Arial', 'B', 20);
    $pdf->SetTextColor(76, 114, 115); // #4c7273
    $pdf->Cell(0, 15, 'CCS Days Export Report', 0, 1, 'C');
    
    $pdf->SetFont('Arial', '', 10);
    $pdf->SetTextColor(102, 102, 102); // #666
    $pdf->Cell(0, 6, 'Generated on: ' . date('F j, Y \a\t g:i:s A'), 0, 1, 'C');
    $pdf->Cell(0, 6, 'Data Type: ' . ucfirst($dataType) . ' | Date Range: ' . ucfirst($dateRange), 0, 1, 'C');
    $pdf->Ln(10);
    
    // Generate PDF content based on data type
    if ($dataType === 'all') {
        foreach ($data as $type => $records) {
            if (!empty($records)) {
                // Add section header
                $pdf->SetFont('Arial', 'B', 14);
                $pdf->SetTextColor(76, 114, 115);
                $pdf->Cell(0, 10, strtoupper($type) . ' DATA (' . (is_array($records) ? count($records) : 0) . ' records)', 0, 1, 'L');
                $pdf->Ln(5);
                
                // Handle students data with year level separation
                if ($type === 'students') {
                    $yearCount = 0;
                    foreach ($records as $yearLevel => $yearStudents) {
                        if (!empty($yearStudents)) {
                            $yearCount++;
                            
                            // Add new page for each year level (except the first one)
                            if ($yearCount > 1) {
                                $pdf->AddPage();
                                
                                // Add header on new page
                                $pdf->SetFont('Arial', 'B', 20);
                                $pdf->SetTextColor(76, 114, 115);
                                $pdf->Cell(0, 15, 'CCS Days Export Report', 0, 1, 'C');
                                
                                $pdf->SetFont('Arial', '', 10);
                                $pdf->SetTextColor(102, 102, 102);
                                $pdf->Cell(0, 6, 'Generated on: ' . date('F j, Y \a\t g:i A'), 0, 1, 'C');
                                $pdf->Cell(0, 6, 'Data Type: ' . ucfirst($dataType) . ' | Date Range: ' . ucfirst($dateRange), 0, 1, 'C');
                                $pdf->Ln(10);
                            }
                            
                            // Year level header
                            $pdf->SetFont('Arial', 'B', 14);
                            $pdf->SetTextColor(76, 114, 115);
                            $pdf->Cell(0, 10, $yearLevel . ' STUDENTS (' . count($yearStudents) . ' students)', 0, 1, 'L');
                            $pdf->Ln(5);
                            
                            // Create table
                            createPDFTable($pdf, $yearStudents);
                            $pdf->Ln(10);
                        }
                    }
                } else {
                    // Handle other data types
                    if (!empty($records)) {
                        createPDFTable($pdf, $records);
                        $pdf->Ln(10);
                    }
                }
            }
        }
    } else if ($dataType === 'students' && is_array($data)) {
        // Grouped by year level: output each year level's students in a table
        $totalRecords = 0;
        foreach ($data as $yearLevel => $yearStudents) {
            $totalRecords += count($yearStudents);
        }
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->SetTextColor(76, 114, 115);
        $pdf->Cell(0, 10, 'STUDENTS DATA (' . $totalRecords . ' students)', 0, 1, 'L');
        $pdf->Ln(5);
        $yearCount = 0;
        foreach ($data as $yearLevel => $yearStudents) {
            if (!empty($yearStudents)) {
                $yearCount++;
                // Add new page for each year level (except the first one)
                if ($yearCount > 1) {
                    $pdf->AddPage();
                    $pdf->SetFont('Arial', 'B', 20);
                    $pdf->SetTextColor(76, 114, 115);
                    $pdf->Cell(0, 15, 'CCS Days Export Report', 0, 1, 'C');
                    $pdf->SetFont('Arial', '', 10);
                    $pdf->SetTextColor(102, 102, 102);
                    $pdf->Cell(0, 6, 'Generated on: ' . date('F j, Y \a\t g:i A'), 0, 1, 'C');
                    $pdf->Cell(0, 6, 'Data Type: Students | Date Range: ' . ucfirst($dateRange), 0, 1, 'C');
                    $pdf->Ln(10);
                }
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->SetTextColor(40, 80, 120);
                $pdf->Cell(0, 8, $yearLevel . ' (' . count($yearStudents) . ' students)', 0, 1, 'L');
                $pdf->Ln(2);
                createPDFTable($pdf, $yearStudents);
                $pdf->Ln(5);
            }
        }
    } else {
        // Single data type (not students)
        $totalRecords = is_array($data) ? count($data) : 0;
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->SetTextColor(76, 114, 115);
        $pdf->Cell(0, 10, strtoupper($dataType) . ' DATA (' . $totalRecords . ' records)', 0, 1, 'L');
        $pdf->Ln(5);
        if (!empty($data)) {
            createPDFTable($pdf, $data);
        }
    }
    
    // Footer
    $totalRecords = 0;
    if ($dataType === 'all') {
        foreach ($data as $type => $records) {
            if (is_array($records)) {
                if ($type === 'students') {
                    foreach ($records as $yearStudents) {
                        $totalRecords += count($yearStudents);
                    }
                } else {
                    $totalRecords += count($records);
                }
            }
        }
    } else {
        $totalRecords = is_array($data) ? count($data) : 0;
    }
    
    // Add footer at bottom of last page
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetTextColor(102, 102, 102);
    $pdf->Cell(0, 6, 'CCS Days Portal - Admin Export System', 0, 1, 'C');
    $pdf->Cell(0, 6, 'Total Records: ' . $totalRecords, 0, 1, 'C');
    $pdf->Cell(0, 6, 'Export Date: ' . date('F j, Y \a\t g:i:s A'), 0, 1, 'C');
    
    // Output PDF with error handling
    try {
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');
        
        $pdf->Output('D', $filename);
        exit;
    } catch (Exception $e) {
        header('Content-Type: text/html; charset=UTF-8');
        echo "<script>alert('PDF Output Error: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
        exit;
    }
}
?>
