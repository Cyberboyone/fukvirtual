<?php 
include 'db.php'; 
include 'header.php'; 

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'staff') {
    header("Location: login.php");
    exit();
}

$staff_id = $_SESSION['user_id'];
$staff_name = $_SESSION['fullname'];

// Handle session creation
if (isset($_POST['create_session'])) {
    $course_id = $_POST['session_course_id'];
    $title = $_POST['session_title'];
    $desc = $_POST['session_desc'];
    $meeting_id = uniqid('GSU_LIVE_');
    
    $stmt = $conn->prepare("INSERT INTO sessions (course_id, title, description, meeting_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $course_id, $title, $desc, $meeting_id);
    if ($stmt->execute()) {
        $success_message = "Live session created successfully!";
    }
}

// Handle session deletion
if (isset($_GET['delete_session'])) {
    $sess_id = intval($_GET['delete_session']);
    $stmt = $conn->prepare("DELETE FROM sessions WHERE id=?");
    $stmt->bind_param("i", $sess_id);
    $stmt->execute();
    header("Location: staff_dashboard.php?deleted=1");
    exit();
}

// Handle course creation
if (isset($_POST['create_course'])) {
    $code = $_POST['code'];
    $title = $_POST['title'];
    $stmt = $conn->prepare("INSERT INTO courses (course_code, course_title, staff_id) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $code, $title, $staff_id);
    $stmt->execute();
    $course_success = "Course created successfully!";
}

// Handle student enrollment
if (isset($_POST['add_student'])) {
    $course_id = intval($_POST['course_id']);
    $student_email = $_POST['student_email'];
    $stmt = $conn->prepare("SELECT id FROM users WHERE email=? AND role='student'");
    $stmt->bind_param("s", $student_email);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $sid = $res->fetch_assoc()['id'];
        // Check if already enrolled
        $check = $conn->prepare("SELECT id FROM enrollments WHERE course_id=? AND student_id=?");
        $check->bind_param("ii", $course_id, $sid);
        $check->execute();
        $checkRes = $check->get_result();
        if ($checkRes->num_rows == 0) {
            $insert = $conn->prepare("INSERT INTO enrollments (course_id, student_id) VALUES (?, ?)");
            $insert->bind_param("ii", $course_id, $sid);
            $insert->execute();
            $enrollment_success = "Student enrolled successfully!";
        } else {
            $enrollment_error = "Student is already enrolled in this course.";
        }
    } else {
        $enrollment_error = "Student email not found.";
    }
}

// Fetch statistics
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM courses WHERE staff_id=?");
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$course_count = $stmt->get_result()->fetch_assoc()['count'];

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM sessions s JOIN courses c ON s.course_id = c.id WHERE c.staff_id=?");
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$session_count = $stmt->get_result()->fetch_assoc()['count'];

$stmt = $conn->prepare("SELECT COUNT(DISTINCT e.student_id) as count FROM enrollments e JOIN courses c ON e.course_id = c.id WHERE c.staff_id=?");
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$student_count = $stmt->get_result()->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - FUK Virtual Learning</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --gsu-green: #006837;
            --gsu-dark: #004d29;
            --gsu-gold: #f1c40f;
            --gsu-light-green: #e8f5e9;
            --gsu-gradient: linear-gradient(135deg, #006837 0%, #004d29 100%);
            --gsu-gradient-gold: linear-gradient(135deg, #f1c40f 0%, #d4ac0d 100%);
            --shadow-sm: 0 4px 6px rgba(0, 0, 0, 0.07);
            --shadow-md: 0 10px 25px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8fafc;
            color: #333;
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
        }
        
        /* Dashboard Header */
        .dashboard-header {
            background: var(--gsu-gradient);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-md);
        }
        
        .welcome-section h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .welcome-section p {
            opacity: 0.9;
            font-size: 1.1rem;
        }
        
        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            transition: all 0.3s ease;
            border-left: 5px solid var(--gsu-green);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        
        .stat-card.courses {
            border-left-color: #3b82f6;
        }
        
        .stat-card.sessions {
            border-left-color: #f59e0b;
        }
        
        .stat-card.students {
            border-left-color: #10b981;
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            font-size: 1.8rem;
        }
        
        .stat-card.courses .stat-icon {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
        }
        
        .stat-card.sessions .stat-icon {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }
        
        .stat-card.students .stat-icon {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
            color: #1e293b;
        }
        
        .stat-label {
            color: #64748b;
            font-weight: 500;
        }
        
        /* Dashboard Layout */
        .dashboard-container {
            padding: 0 1rem;
        }
        
        .dashboard-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        @media (max-width: 992px) {
            .dashboard-content {
                grid-template-columns: 1fr;
            }
        }
        
        /* Action Cards */
        .action-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            margin-bottom: 1.5rem;
            border: 1px solid #e2e8f0;
        }
        
        .action-card-header {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--gsu-light-green);
        }
        
        .action-card-header i {
            font-size: 1.5rem;
            margin-right: 0.75rem;
            color: var(--gsu-green);
        }
        
        .action-card-header h5 {
            margin: 0;
            color: #1e293b;
        }
        
        /* Form Styling */
        .form-group {
            margin-bottom: 1.25rem;
        }
        
        .form-label {
            font-weight: 600;
            color: #475569;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }
        
        .form-control-custom {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: white;
        }
        
        .form-control-custom:focus {
            border-color: var(--gsu-green);
            box-shadow: 0 0 0 3px rgba(0, 104, 55, 0.1);
        }
        
        .btn-staff-primary {
            background: var(--gsu-gradient);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 0.5rem;
        }
        
        .btn-staff-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 104, 55, 0.25);
        }
        
        .btn-staff-secondary {
            background: #64748b;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .btn-staff-secondary:hover {
            background: #475569;
            color: white;
        }
        
        /* Sessions Table */
        .sessions-table-container {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            margin-bottom: 3rem;
            overflow: hidden;
        }
        
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--gsu-light-green);
        }
        
        .table-header h5 {
            margin: 0;
            color: #1e293b;
        }
        
        .table-custom {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .table-custom thead th {
            background-color: #f1f5f9;
            color: #475569;
            font-weight: 600;
            padding: 1rem;
            border-bottom: 2px solid #e2e8f0;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }
        
        .table-custom tbody tr {
            transition: all 0.2s ease;
        }
        
        .table-custom tbody tr:hover {
            background-color: #f8fafc;
        }
        
        .table-custom td {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        
        .course-badge {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .session-topic {
            font-weight: 600;
            color: #1e293b;
        }
        
        .session-desc {
            font-size: 0.9rem;
            color: #64748b;
            margin-top: 0.25rem;
        }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-live {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .btn-live:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(239, 68, 68, 0.3);
            color: white;
        }
        
        .btn-delete {
            background: transparent;
            color: #ef4444;
            border: 1px solid #ef4444;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .btn-delete:hover {
            background: #ef4444;
            color: white;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #64748b;
        }
        
        .empty-state i {
            font-size: 3rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }
        
        /* Alerts */
        .alert-custom {
            border-radius: 10px;
            border: none;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            animation: slideIn 0.3s ease-out;
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .quick-action-btn {
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 1.25rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
        }
        
        .quick-action-btn:hover {
            border-color: var(--gsu-green);
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
            color: inherit;
        }
        
        .quick-action-btn i {
            font-size: 2rem;
            margin-bottom: 0.75rem;
            color: var(--gsu-green);
        }
        
        .quick-action-btn h6 {
            margin: 0;
            color: #1e293b;
            font-weight: 600;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .welcome-section h1 {
                font-size: 2rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn-live, .btn-delete {
                width: 100%;
                justify-content: center;
            }
            
            .quick-actions {
                grid-template-columns: 1fr;
            }
        }
        
        /* Modal Styles */
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: var(--shadow-lg);
        }
        
        .modal-header {
            background: var(--gsu-gradient);
            color: white;
            border-radius: 15px 15px 0 0;
            border: none;
        }
    </style>
</head>
<body>
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="container">
            <div class="welcome-section">
                <h1>Welcome, <?php echo htmlspecialchars($staff_name); ?></h1>
                <p>Manage your courses, create live sessions, and track student progress</p>
            </div>
        </div>
    </div>
    
    <!-- Main Dashboard -->
    <div class="container dashboard-container">
        <!-- Success Messages -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-custom d-flex align-items-center" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <div><?php echo $success_message; ?></div>
            </div>
        <?php endif; ?>
        
        <?php if (isset($course_success)): ?>
            <div class="alert alert-success alert-custom d-flex align-items-center" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <div><?php echo $course_success; ?></div>
            </div>
        <?php endif; ?>
        
        <?php if (isset($enrollment_success)): ?>
            <div class="alert alert-success alert-custom d-flex align-items-center" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <div><?php echo $enrollment_success; ?></div>
            </div>
        <?php endif; ?>
        
        <?php if (isset($enrollment_error)): ?>
            <div class="alert alert-danger alert-custom d-flex align-items-center" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <div><?php echo $enrollment_error; ?></div>
            </div>
        <?php endif; ?>
        
        <!-- Statistics Cards -->
        <div class="stats-container">
            <div class="stat-card courses">
                <div class="stat-icon">
                    <i class="bi bi-journal-bookmark"></i>
                </div>
                <div class="stat-number"><?php echo $course_count; ?></div>
                <div class="stat-label">Active Courses</div>
            </div>
            
            <div class="stat-card sessions">
                <div class="stat-icon">
                    <i class="bi bi-camera-video"></i>
                </div>
                <div class="stat-number"><?php echo $session_count; ?></div>
                <div class="stat-label">Live Sessions</div>
            </div>
            
            <div class="stat-card students">
                <div class="stat-icon">
                    <i class="bi bi-people"></i>
                </div>
                <div class="stat-number"><?php echo $student_count; ?></div>
                <div class="stat-label">Enrolled Students</div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="mb-4">
            <h5 class="mb-3">Quick Actions</h5>
            <div class="quick-actions">
                <a href="#createCourseModal" data-bs-toggle="modal" class="quick-action-btn">
                    <i class="bi bi-plus-circle"></i>
                    <h6>Create Course</h6>
                    <small class="text-muted">Add a new course</small>
                </a>
                <a href="#createSessionModal" data-bs-toggle="modal" class="quick-action-btn">
                    <i class="bi bi-camera-video"></i>
                    <h6>Start Live Session</h6>
                    <small class="text-muted">Create a live class</small>
                </a>
                <a href="#enrollStudentModal" data-bs-toggle="modal" class="quick-action-btn">
                    <i class="bi bi-person-plus"></i>
                    <h6>Enroll Student</h6>
                    <small class="text-muted">Add student to course</small>
                </a>
                <a href="#" class="quick-action-btn">
                    <i class="bi bi-graph-up"></i>
                    <h6>View Analytics</h6>
                    <small class="text-muted">Track student progress</small>
                </a>
            </div>
        </div>
        
        <!-- Dashboard Content -->
        <div class="dashboard-content">
            <!-- Left Column - Course Management -->
            <div>
                <!-- Create Course Card (Hidden in Modal) -->
                <div class="action-card d-none d-lg-block">
                    <div class="action-card-header">
                        <i class="bi bi-plus-circle"></i>
                        <h5>Create New Course</h5>
                    </div>
                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label">Course Code</label>
                            <input type="text" name="code" class="form-control-custom" placeholder="e.g. CSC101" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Course Title</label>
                            <input type="text" name="title" class="form-control-custom" placeholder="Introduction to Computer Science" required>
                        </div>
                        <button type="submit" name="create_course" class="btn-staff-primary">
                            <i class="bi bi-check-circle me-2"></i> Create Course
                        </button>
                    </form>
                </div>
                
                <!-- Enroll Student Card (Hidden in Modal) -->
                <div class="action-card d-none d-lg-block">
                    <div class="action-card-header">
                        <i class="bi bi-person-plus"></i>
                        <h5>Enroll Student</h5>
                    </div>
                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label">Select Course</label>
                            <select name="course_id" class="form-control-custom">
                                <?php
                                $courses = $conn->query("SELECT * FROM courses WHERE staff_id=$staff_id ORDER BY course_code");
                                while($c = $courses->fetch_assoc()) {
                                    echo "<option value='{$c['id']}'>{$c['course_code']} - {$c['course_title']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Student Email</label>
                            <input type="email" name="student_email" class="form-control-custom" placeholder="student@gsu.edu.ng" required>
                        </div>
                        <button type="submit" name="add_student" class="btn-staff-primary">
                            <i class="bi bi-person-plus me-2"></i> Enroll Student
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Right Column - Session Creation -->
            <div>
                <!-- Create Session Card (Hidden in Modal) -->
                <div class="action-card d-none d-lg-block">
                    <div class="action-card-header">
                        <i class="bi bi-camera-video"></i>
                        <h5>Create Live Session</h5>
                    </div>
                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label">Select Course</label>
                            <select name="session_course_id" class="form-control-custom">
                                <?php
                                $courses->data_seek(0);
                                while($c = $courses->fetch_assoc()) {
                                    echo "<option value='{$c['id']}'>{$c['course_code']} - {$c['course_title']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Session Topic</label>
                            <input type="text" name="session_title" class="form-control-custom" placeholder="Introduction to Programming Concepts" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Description (Optional)</label>
                            <textarea name="session_desc" class="form-control-custom" placeholder="Brief description of what will be covered..." rows="3"></textarea>
                        </div>
                        <button type="submit" name="create_session" class="btn-staff-primary">
                            <i class="bi bi-rocket-takeoff me-2"></i> Launch Session
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Sessions Table -->
        <div class="sessions-table-container">
            <div class="table-header">
                <h5><i class="bi bi-list-task me-2"></i> Recent Live Sessions</h5>
                <span class="badge bg-primary"><?php echo $session_count; ?> Total</span>
            </div>
            
            <?php
            $stmt = $conn->prepare("
                SELECT s.*, c.course_code, c.course_title 
                FROM sessions s 
                JOIN courses c ON s.course_id = c.id 
                WHERE c.staff_id = ? 
                ORDER BY s.created_at DESC
                LIMIT 10
            ");
            $stmt->bind_param("i", $staff_id);
            $stmt->execute();
            $my_sessions = $stmt->get_result();
            
            if($my_sessions->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Course</th>
                                <th>Topic</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $my_sessions->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div class="text-muted small"><?php echo date('M d, Y', strtotime($row['created_at'])); ?></div>
                                    <div class="text-muted smaller"><?php echo date('h:i A', strtotime($row['created_at'])); ?></div>
                                </td>
                                <td>
                                    <div class="course-badge"><?php echo $row['course_code']; ?></div>
                                    <div class="small text-muted mt-1"><?php echo $row['course_title']; ?></div>
                                </td>
                                <td>
                                    <div class="session-topic"><?php echo $row['title']; ?></div>
                                    <?php if (!empty($row['description'])): ?>
                                    <div class="session-desc"><?php echo substr($row['description'], 0, 80) . '...'; ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-success">Ready</span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="live_classroom.php?room=<?php echo $row['meeting_id']; ?>&title=<?php echo urlencode($row['title']); ?>" 
                                           class="btn-live" target="_blank">
                                           <i class="bi bi-broadcast"></i> Go Live
                                        </a>
                                        <a href="staff_dashboard.php?delete_session=<?php echo $row['id']; ?>" 
                                           class="btn-delete"
                                           onclick="return confirm('Are you sure you want to delete this session?');">
                                           <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <a href="#" class="btn btn-outline-primary">View All Sessions</a>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-camera-video-off"></i>
                    <h5>No Live Sessions Yet</h5>
                    <p class="text-muted">Create your first live session to start teaching online</p>
                    <a href="#createSessionModal" data-bs-toggle="modal" class="btn-staff-primary mt-3" style="max-width: 200px;">
                        <i class="bi bi-plus-circle me-2"></i> Create Session
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Modals for Mobile/Tablet -->
    
    <!-- Create Course Modal -->
    <div class="modal fade" id="createCourseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Course</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label">Course Code</label>
                            <input type="text" name="code" class="form-control-custom" placeholder="e.g. CSC101" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Course Title</label>
                            <input type="text" name="title" class="form-control-custom" placeholder="Introduction to Computer Science" required>
                        </div>
                        <button type="submit" name="create_course" class="btn-staff-primary">
                            <i class="bi bi-check-circle me-2"></i> Create Course
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Create Session Modal -->
    <div class="modal fade" id="createSessionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Live Session</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label">Select Course</label>
                            <select name="session_course_id" class="form-control-custom">
                                <?php
                                $courses->data_seek(0);
                                while($c = $courses->fetch_assoc()) {
                                    echo "<option value='{$c['id']}'>{$c['course_code']} - {$c['course_title']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Session Topic</label>
                            <input type="text" name="session_title" class="form-control-custom" placeholder="Introduction to Programming Concepts" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Description (Optional)</label>
                            <textarea name="session_desc" class="form-control-custom" placeholder="Brief description of what will be covered..." rows="3"></textarea>
                        </div>
                        <button type="submit" name="create_session" class="btn-staff-primary">
                            <i class="bi bi-rocket-takeoff me-2"></i> Launch Session
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Enroll Student Modal -->
    <div class="modal fade" id="enrollStudentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Enroll Student</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label">Select Course</label>
                            <select name="course_id" class="form-control-custom">
                                <?php
                                $courses->data_seek(0);
                                while($c = $courses->fetch_assoc()) {
                                    echo "<option value='{$c['id']}'>{$c['course_code']} - {$c['course_title']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Student Email</label>
                            <input type="email" name="student_email" class="form-control-custom" placeholder="student@gsu.edu.ng" required>
                        </div>
                        <button type="submit" name="add_student" class="btn-staff-primary">
                            <i class="bi bi-person-plus me-2"></i> Enroll Student
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-dismiss alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // Handle form submissions in modals
        document.querySelectorAll('.modal form').forEach(form => {
            form.addEventListener('submit', function(e) {
                // Add loading state to button
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Processing...';
                submitBtn.disabled = true;
                
                // Simulate processing time
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 1500);
            });
        });
        
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Auto-refresh page when returning from a live session (Removed infinite loop)
        if (window.performance) {
            if (performance.navigation.type === 1) {
                // Page was reloaded
                // window.location.reload();
            }
        }
    </script>
</body>
</html>