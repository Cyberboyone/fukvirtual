<?php 
include 'db.php'; 
include 'header.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$student_name = $_SESSION['fullname'];

// Fetch enrolled courses with error handling
$courses_query = $conn->query("
    SELECT c.*, u.fullname as instructor 
    FROM courses c 
    JOIN enrollments e ON c.id = e.course_id 
    JOIN users u ON c.staff_id = u.id 
    WHERE e.student_id = $student_id 
    ORDER BY c.course_code
");

if ($courses_query === false) {
    // Handle query error
    $course_count = 0;
    $courses_data = [];
    echo "<div class='alert alert-danger'>Error loading courses: " . $conn->error . "</div>";
} else {
    $course_count = $courses_query->num_rows;
    $courses_data = $courses_query->fetch_all(MYSQLI_ASSOC);
}

// Fetch upcoming sessions with error handling
$sessions_query = $conn->query("
    SELECT s.*, c.course_code, c.course_title, u.fullname as instructor
    FROM sessions s 
    JOIN courses c ON s.course_id = c.id 
    JOIN users u ON c.staff_id = u.id 
    JOIN enrollments e ON c.id = e.course_id 
    WHERE e.student_id = $student_id 
    ORDER BY s.created_at DESC
    LIMIT 6
");

if ($sessions_query === false) {
    // Handle query error
    $session_count = 0;
    $sessions_data = [];
    echo "<div class='alert alert-danger'>Error loading sessions: " . $conn->error . "</div>";
} else {
    $session_count = $sessions_query->num_rows;
    $sessions_data = $sessions_query->fetch_all(MYSQLI_ASSOC);
}

// Fetch recent activity with error handling - FIXED
// Removed the UNION with courses because 'courses' table has no 'created_at' column in your DB
$recent_activity_query = $conn->query("
    SELECT 'session_joined' as type, s.title, s.created_at, c.course_code
    FROM sessions s 
    JOIN courses c ON s.course_id = c.id 
    JOIN enrollments e ON c.id = e.course_id 
    WHERE e.student_id = $student_id 
    ORDER BY s.created_at DESC 
    LIMIT 5
");

if ($recent_activity_query === false) {
    // Handle query error
    $recent_activity_data = [];
    echo "<div class='alert alert-danger'>Error loading recent activity: " . $conn->error . "</div>";
} else {
    $recent_activity_data = $recent_activity_query->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - GSU Virtual Learning</title>
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
        
        /* Student Dashboard Header */
        .student-header {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: white;
            padding: 2.5rem 0;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-md);
            position: relative;
            overflow: hidden;
        }
        
        .student-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 30px 30px;
            opacity: 0.2;
            animation: floatPattern 20s linear infinite;
        }
        
        @keyframes floatPattern {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .welcome-student {
            position: relative;
            z-index: 1;
        }
        
        .welcome-student h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .welcome-student p {
            opacity: 0.9;
            font-size: 1.1rem;
            max-width: 600px;
        }
        
        /* Stats Cards */
        .student-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 1rem;
            border-left: 4px solid #3b82f6;
        }
        
        .stat-card.courses {
            border-left-color: #10b981;
        }
        
        .stat-card.sessions {
            border-left-color: #f59e0b;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
        }
        
        .stat-card.courses .stat-icon {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }
        
        .stat-card.sessions .stat-icon {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }
        
        .stat-content h3 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
            color: #1e293b;
        }
        
        .stat-content p {
            color: #64748b;
            font-weight: 500;
            font-size: 0.95rem;
        }
        
        /* Dashboard Layout */
        .dashboard-container {
            padding: 0 1rem;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        @media (max-width: 992px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }
        
        /* Live Sessions Grid */
        .sessions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        @media (max-width: 768px) {
            .sessions-grid {
                grid-template-columns: 1fr;
            }
        }
        
        /* Session Card */
        .session-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--shadow-md);
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
            height: 100%;
        }
        
        .session-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            border-color: #cbd5e1;
        }
        
        .session-header {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: white;
            padding: 1.25rem;
            position: relative;
        }
        
        .live-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #dc2626;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            animation: pulse 2s infinite;
        }
        
        .live-badge::before {
            content: '';
            width: 8px;
            height: 8px;
            background: white;
            border-radius: 50%;
            animation: blink 1.5s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }
        
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .course-code {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .session-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
            line-height: 1.3;
        }
        
        .session-body {
            padding: 1.5rem;
        }
        
        .session-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
            color: #64748b;
            font-size: 0.9rem;
        }
        
        .session-meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .session-meta-item i {
            color: #3b82f6;
        }
        
        .session-description {
            color: #475569;
            margin-bottom: 1.5rem;
            line-height: 1.6;
            font-size: 0.95rem;
        }
        
        .session-description p {
            margin-bottom: 0.5rem;
        }
        
        .btn-join {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s ease;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            text-decoration: none;
        }
        
        .btn-join:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.3);
            color: white;
        }
        
        /* Sidebar */
        .sidebar-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            margin-bottom: 1.5rem;
            border: 1px solid #e2e8f0;
        }
        
        .sidebar-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.25rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .sidebar-title i {
            color: #3b82f6;
            font-size: 1.25rem;
        }
        
        .sidebar-title h5 {
            margin: 0;
            color: #1e293b;
        }
        
        /* Courses List */
        .courses-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .course-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 0.75rem;
            background: #f8fafc;
            transition: all 0.3s ease;
            border-left: 3px solid #10b981;
        }
        
        .course-item:hover {
            background: #f1f5f9;
            transform: translateX(5px);
        }
        
        .course-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 1rem;
            font-size: 1.1rem;
        }
        
        .course-info h6 {
            margin: 0;
            color: #1e293b;
            font-weight: 600;
        }
        
        .course-info small {
            color: #64748b;
            font-size: 0.85rem;
        }
        
        /* Activity Feed */
        .activity-feed {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .activity-item {
            display: flex;
            gap: 1rem;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 0.75rem;
            background: #f8fafc;
            transition: all 0.3s ease;
        }
        
        .activity-item:hover {
            background: #f1f5f9;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.1rem;
            flex-shrink: 0;
        }
        
        .activity-icon.session {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }
        
        .activity-icon.course {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        
        .activity-content {
            flex: 1;
        }
        
        .activity-text {
            color: #475569;
            margin-bottom: 0.25rem;
            font-size: 0.95rem;
        }
        
        .activity-text strong {
            color: #1e293b;
        }
        
        .activity-time {
            color: #94a3b8;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        /* Empty States */
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            background: white;
            border-radius: 15px;
            box-shadow: var(--shadow-md);
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 1.5rem;
        }
        
        .empty-state h4 {
            color: #64748b;
            margin-bottom: 1rem;
        }
        
        .empty-state p {
            color: #94a3b8;
            max-width: 400px;
            margin: 0 auto 1.5rem;
        }
        
        /* Upcoming Classes */
        .upcoming-sessions {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 15px;
            padding: 1.5rem;
            margin-top: 2rem;
        }
        
        .upcoming-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .upcoming-header h5 {
            margin: 0;
            color: #1e293b;
        }
        
        .upcoming-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .upcoming-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: white;
            border-radius: 10px;
            box-shadow: var(--shadow-sm);
            border-left: 4px solid #f59e0b;
        }
        
        .upcoming-time {
            background: #fef3c7;
            color: #92400e;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            min-width: 100px;
            text-align: center;
        }
        
        .upcoming-info h6 {
            margin: 0;
            color: #1e293b;
        }
        
        .upcoming-info small {
            color: #64748b;
        }
        
        /* Responsive */
        @media (max-width: 576px) {
            .student-header {
                padding: 2rem 0;
            }
            
            .welcome-student h1 {
                font-size: 2rem;
            }
            
            .student-stats {
                grid-template-columns: 1fr;
            }
            
            .session-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="student-header">
        <div class="container">
            <div class="welcome-student">
                <h1>Welcome back, <?php echo htmlspecialchars($student_name); ?>!</h1>
                <p>Access your live classes, course materials, and track your learning progress all in one place.</p>
                <div class="d-flex gap-2 mt-3">
                    <span class="badge bg-light text-dark">
                        <i class="bi bi-mortarboard me-1"></i> Student
                    </span>
                    <span class="badge bg-light text-dark">
                        <i class="bi bi-book me-1"></i> <?php echo $course_count; ?> Courses
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container dashboard-container">
        <div class="student-stats">
            <div class="stat-card courses">
                <div class="stat-icon">
                    <i class="bi bi-journal-bookmark"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $course_count; ?></h3>
                    <p>Enrolled Courses</p>
                </div>
            </div>
            
            <div class="stat-card sessions">
                <div class="stat-icon">
                    <i class="bi bi-camera-video"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $session_count; ?></h3>
                    <p>Active Sessions</p>
                </div>
            </div>
        </div>
        
        <div class="dashboard-grid">
            <div>
                <h4 class="mb-3">Live Class Sessions</h4>
                
                <?php if ($session_count > 0): ?>
                    <div class="sessions-grid">
                        <?php foreach ($sessions_data as $sess): ?>
                        <div class="session-card">
                            <div class="session-header">
                                <span class="course-code"><?php echo htmlspecialchars($sess['course_code']); ?></span>
                                <div class="live-badge">LIVE NOW</div>
                                <h3 class="session-title"><?php echo htmlspecialchars($sess['title']); ?></h3>
                            </div>
                            
                            <div class="session-body">
                                <div class="session-meta">
                                    <div class="session-meta-item">
                                        <i class="bi bi-person"></i>
                                        <span><?php echo htmlspecialchars($sess['instructor']); ?></span>
                                    </div>
                                    <div class="session-meta-item">
                                        <i class="bi bi-clock"></i>
                                        <span><?php echo date('M d, Y h:i A', strtotime($sess['created_at'])); ?></span>
                                    </div>
                                </div>
                                
                                <?php if (!empty($sess['description'])): ?>
                                <div class="session-description">
                                    <p><?php echo nl2br(htmlspecialchars(substr($sess['description'], 0, 200))); ?><?php echo strlen($sess['description']) > 200 ? '...' : ''; ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <a href="live_classroom.php?room=<?php echo $sess['meeting_id']; ?>&title=<?php echo urlencode($sess['title']); ?>&code=<?php echo urlencode($sess['course_code']); ?>" 
                                   class="btn-join" target="_blank">
                                   <i class="bi bi-camera-video"></i>
                                   Join Live Classroom
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="text-center mt-4">
                        <a href="#" class="btn btn-outline-primary">
                            <i class="bi bi-arrow-clockwise me-2"></i> View All Sessions
                        </a>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="bi bi-camera-video-off"></i>
                        <h4>No Active Sessions</h4>
                        <p>There are currently no live sessions available. Check back later or contact your instructors.</p>
                        <a href="#" class="btn btn-primary">
                            <i class="bi bi-bell me-2"></i> Notify Me
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <div>
                <div class="sidebar-card">
                    <div class="sidebar-title">
                        <i class="bi bi-book"></i>
                        <h5>My Courses</h5>
                    </div>
                    
                    <?php if ($course_count > 0): ?>
                        <ul class="courses-list">
                            <?php 
                            $counter = 0;
                            foreach ($courses_data as $course): 
                                if ($counter >= 4) break;
                                $counter++;
                            ?>
                            <li class="course-item">
                                <div class="course-icon">
                                    <i class="bi bi-journal-text"></i>
                                </div>
                                <div class="course-info">
                                    <h6><?php echo htmlspecialchars($course['course_code']); ?></h6>
                                    <small><?php echo htmlspecialchars(substr($course['course_title'], 0, 40)); ?><?php echo strlen($course['course_title']) > 40 ? '...' : ''; ?></small>
                                    <div class="small text-muted mt-1"><?php echo htmlspecialchars($course['instructor']); ?></div>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        
                        <?php if ($course_count > 4): ?>
                        <div class="text-center mt-3">
                            <a href="#" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-arrow-right me-1"></i> View All (<?php echo $course_count; ?>)
                            </a>
                        </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="bi bi-journal-x text-muted" style="font-size: 2rem;"></i>
                            <p class="text-muted mt-2 mb-0">No enrolled courses</p>
                            <small class="text-muted">Contact your instructor to get enrolled</small>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="sidebar-card">
                    <div class="sidebar-title">
                        <i class="bi bi-activity"></i>
                        <h5>Recent Activity</h5>
                    </div>
                    
                    <?php if (!empty($recent_activity_data)): ?>
                        <ul class="activity-feed">
                            <?php foreach ($recent_activity_data as $activity): ?>
                            <li class="activity-item">
                                <div class="activity-icon <?php echo $activity['type']; ?>">
                                    <i class="bi bi-<?php echo $activity['type'] == 'session_joined' ? 'camera-video' : 'person-plus'; ?>"></i>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-text">
                                        <?php if ($activity['type'] == 'session_joined'): ?>
                                            Session: <strong><?php echo htmlspecialchars($activity['title']); ?></strong>
                                        <?php else: ?>
                                            Enrolled in: <strong><?php echo htmlspecialchars($activity['title']); ?></strong>
                                        <?php endif; ?>
                                    </div>
                                    <div class="activity-time">
                                        <i class="bi bi-clock"></i>
                                        <?php 
                                        $time_ago = time() - strtotime($activity['created_at']);
                                        if ($time_ago < 3600) {
                                            echo floor($time_ago / 60) . ' minutes ago';
                                        } elseif ($time_ago < 86400) {
                                            echo floor($time_ago / 3600) . ' hours ago';
                                        } else {
                                            echo date('M d, Y', strtotime($activity['created_at']));
                                        }
                                        ?>
                                    </div>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="bi bi-activity text-muted" style="font-size: 2rem;"></i>
                            <p class="text-muted mt-2 mb-0">No recent activity</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="sidebar-card">
                    <div class="sidebar-title">
                        <i class="bi bi-lightning"></i>
                        <h5>Quick Actions</h5>
                    </div>
                    <div class="d-grid gap-2">
                        <a href="#" class="btn btn-outline-primary">
                            <i class="bi bi-download me-2"></i> Download Materials
                        </a>
                        <a href="#" class="btn btn-outline-success">
                            <i class="bi bi-calendar-check me-2"></i> View Schedule
                        </a>
                        <a href="#" class="btn btn-outline-info">
                            <i class="bi bi-question-circle me-2"></i> Get Help
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="upcoming-sessions">
            <div class="upcoming-header">
                <h5><i class="bi bi-calendar-event me-2"></i> Upcoming Classes</h5>
                <span class="badge bg-primary">This Week</span>
            </div>
            
            <div class="upcoming-list">
                <div class="upcoming-item">
                    <div class="upcoming-time">
                        Tomorrow<br>10:00 AM
                    </div>
                    <div class="upcoming-info">
                        <h6>Advanced Database Systems</h6>
                        <small>Prof. Ahmed Musa • Room: CS-101</small>
                    </div>
                </div>
                
                <div class="upcoming-item">
                    <div class="upcoming-time">
                        Wed, 2:00 PM
                    </div>
                    <div class="upcoming-info">
                        <h6>Software Engineering</h6>
                        <small>Dr. Fatima Bello • Virtual Class</small>
                    </div>
                </div>
                
                <div class="upcoming-item">
                    <div class="upcoming-time">
                        Fri, 9:30 AM
                    </div>
                    <div class="upcoming-info">
                        <h6>Data Structures & Algorithms</h6>
                        <small>Prof. John Doe • Room: CS-203</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update time ago for activity feed
        function updateTimeAgo() {
            document.querySelectorAll('.activity-time').forEach(el => {
                const timeText = el.textContent;
                if (timeText.includes('minutes') || timeText.includes('hours')) {
                    // This would normally be updated with real-time logic
                    console.log('Time ago would update here');
                }
            });
        }
        
        // Refresh sessions every 30 seconds
        setInterval(() => {
            const liveBadges = document.querySelectorAll('.live-badge');
            liveBadges.forEach(badge => {
                badge.style.animation = 'none';
                setTimeout(() => {
                    badge.style.animation = 'pulse 2s infinite';
                }, 10);
            });
        }, 30000);
        
        // Smooth scroll for anchors
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // Auto-refresh when returning from live session (Removed infinite loop)
        if (window.performance && performance.navigation.type === 1) {
            // Page was reloaded
            setTimeout(() => {
                // location.reload();
            }, 1000);
        }
        
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    </script>
</body>
</html>