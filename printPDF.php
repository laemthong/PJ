<?php
require_once('tcpdf/tcpdf.php');
include 'config.php';

// รับค่าประเภทการรายงานจาก URL (GET method)
$reportType = isset($_GET['report-type']) ? $_GET['report-type'] : '';

class MYPDF extends TCPDF {
    public function Header() {
        $this->SetFont('thsarabunnew', '', 16);
        $this->Cell(0, 10, 'รายงานข้อมูล', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('thsarabunnew', '', 12);
        $this->Cell(0, 10, 'หน้าที่ ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, 0, 'C');
    }
}

// สร้าง PDF ใหม่
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// ตั้งค่าข้อมูลเอกสาร
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your Name');
$pdf->SetTitle('รายงานข้อมูล');
$pdf->SetSubject('รายงานข้อมูล');
$pdf->SetKeywords('รายงาน, TCPDF, PDF, ฟอนต์ไทย');

// ตั้งค่าฟอนต์ THSarabunNew
$pdf->SetFont('thsarabunnew', '', 14);

// เพิ่มหน้ากระดาษ
$pdf->AddPage();

// เช็คประเภทการรายงานและดึงข้อมูลจากฐานข้อมูลตามประเภทนั้น
$html = '';

if ($reportType == 'location') {
    // ดึงข้อมูลสถานที่เล่นกีฬา
    $html .= '<h1 style="text-align:center;">รายงานข้อมูลสถานที่</h1>';
    $html .= '<table border="1" cellpadding="4">
                <tr>
                    <th>ลำดับ</th>
                    <th>ชื่อสถานที่</th>
                    <th>เวลาเปิด</th>
                    <th>ละติจูด</th>
                    <th>ลองจิจูด</th>
                </tr>';

    $query = "SELECT location_name, location_time, latitude, longitude FROM location";
    $result = mysqli_query($conn, $query);
    $counter = 1;
    
    while ($row = mysqli_fetch_assoc($result)) {
        $html .= '<tr>
                    <td>' . $counter . '</td>
                    <td>' . htmlspecialchars($row['location_name']) . '</td>
                    <td>' . htmlspecialchars($row['location_time']) . '</td>
                    <td>' . htmlspecialchars($row['latitude']) . '</td>
                    <td>' . htmlspecialchars($row['longitude']) . '</td>
                </tr>';
        $counter++;
    }

    $html .= '</table>';
} elseif ($reportType == 'activity') {
    // ดึงข้อมูลกิจกรรม
    $html .= '<h1 style="text-align:center;">รายงานข้อมูลกิจกรรม</h1>';
    $html .= '<table border="1" cellpadding="4">
                <tr>
                    <th>ลำดับ</th>
                    <th>ชื่อกิจกรรม</th>
                    <th>วันที่</th>
                    <th>รายละเอียด</th>
                    <th>ชื่อสถานที่</th>
                    <th>ชื่อกีฬา</th>
                    <th>ชื่อผู้ใช้</th>
                    <th>สถานะ</th>
                </tr>';

    $query = "
        SELECT 
            activity.activity_name, 
            activity.activity_date, 
            activity.activity_details, 
            location.location_name, 
            sport.sport_name, 
            user_information.user_name, 
            activity.status 
        FROM activity
        JOIN location ON activity.location_id = location.location_id
        JOIN sport ON activity.sport_id = sport.sport_id
        JOIN user_information ON activity.user_id = user_information.user_id
    ";
    $result = mysqli_query($conn, $query);
    $counter = 1;

    while ($row = mysqli_fetch_assoc($result)) {
        $html .= '<tr>
                    <td>' . $counter . '</td>
                    <td>' . htmlspecialchars($row['activity_name']) . '</td>
                    <td>' . htmlspecialchars($row['activity_date']) . '</td>
                    <td>' . htmlspecialchars($row['activity_details']) . '</td>
                    <td>' . htmlspecialchars($row['location_name']) . '</td>
                    <td>' . htmlspecialchars($row['sport_name']) . '</td>
                    <td>' . htmlspecialchars($row['user_name']) . '</td>
                    <td>' . htmlspecialchars($row['status']) . '</td>
                </tr>';
        $counter++;
    }

    $html .= '</table>';
} elseif ($reportType == 'user') {
    // ดึงข้อมูลสมาชิก
    $html .= '<h1 style="text-align:center;">รายงานข้อมูลสมาชิก</h1>';
    $html .= '<table border="1" cellpadding="4">
                <tr>
                    <th>ลำดับ</th>
                    <th>รหัสสมาชิก</th>
                    <th>อีเมล</th>
                    <th>ชื่อสมาชิก</th>
                    <th>อายุ</th>
                    <th>สถานะ</th>
                </tr>';

    $query = "
        SELECT 
            user_id, 
            user_email, 
            user_name, 
            user_age, 
            status 
        FROM user_information";
    $result = mysqli_query($conn, $query);
    $counter = 1;

    while ($row = mysqli_fetch_assoc($result)) {
        $html .= '<tr>
                    <td>' . $counter . '</td>
                    <td>' . htmlspecialchars($row['user_id']) . '</td>
                    <td>' . htmlspecialchars($row['user_email']) . '</td>
                    <td>' . htmlspecialchars($row['user_name']) . '</td>
                    <td>' . htmlspecialchars($row['user_age']) . '</td>
                    <td>' . htmlspecialchars($row['status']) . '</td>
                </tr>';
        $counter++;
    }

    $html .= '</table>';
}

// แสดง HTML ใน PDF
$pdf->writeHTML($html, true, false, true, false, '');

// ส่งไฟล์ PDF ไปยัง browser
$pdf->Output('report.pdf', 'I');
?>
