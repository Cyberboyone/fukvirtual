<?php 
include 'db.php'; 

if (!isset($_GET['room']) || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$room_id = "GSU_Class_" . preg_replace("/[^a-zA-Z0-9]/", "", $_GET['room']);
$user_name = $_SESSION['fullname'];
$user_role = $_SESSION['role'];
$is_staff = ($user_role == 'staff');
$class_title = isset($_GET['title']) ? htmlspecialchars($_GET['title']) : "Live Class Session";
$class_code = isset($_GET['code']) ? htmlspecialchars($_GET['code']) : "CS101";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Classroom - FUK Virtual Learning Environment</title>
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
            background-color: #0f172a;
            color: #e2e8f0;
            overflow: hidden;
            height: 100vh;
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
        }
        
        /* Live Indicator */
        .live-indicator {
            display: inline-flex;
            align-items: center;
            background-color: #dc2626;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-left: 1rem;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }
        
        .live-indicator::before {
            content: '';
            display: inline-block;
            width: 8px;
            height: 8px;
            background-color: white;
            border-radius: 50%;
            margin-right: 6px;
            animation: blink 1.5s infinite;
        }
        
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        /* Classroom Header */
        .classroom-header {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #334155;
            box-shadow: var(--shadow-md);
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
        }
        
        .class-info h4 {
            color: white;
            margin-bottom: 0.25rem;
            font-weight: 700;
        }
        
        .class-subtitle {
            color: #94a3b8;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .user-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background-color: rgba(255, 255, 255, 0.1);
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        
        .staff-badge {
            background: linear-gradient(135deg, #f1c40f 0%, #d4ac0d 100%);
            color: #333;
            font-weight: 600;
        }
        
        .student-badge {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            font-weight: 600;
        }
        
        /* Main Container */
        .classroom-container {
            display: flex;
            height: calc(100vh - 80px);
            overflow: hidden;
        }
        
        /* Video Container */
        .video-container {
            flex: 1;
            background-color: #000;
            position: relative;
            overflow: hidden;
            border-right: 1px solid #334155;
        }
        
        #meet {
            width: 100%;
            height: 100%;
        }
        
        /* Controls Sidebar */
        .controls-sidebar {
            width: 320px;
            background-color: #1e293b;
            display: flex;
            flex-direction: column;
            border-left: 1px solid #334155;
            transition: transform 0.3s ease;
        }
        
        @media (max-width: 1024px) {
            .controls-sidebar {
                position: absolute;
                right: 0;
                top: 80px;
                height: calc(100vh - 80px);
                transform: translateX(100%);
                z-index: 1000;
            }
            
            .controls-sidebar.active {
                transform: translateX(0);
            }
            
            .sidebar-toggle {
                display: block !important;
            }
        }
        
        .sidebar-toggle {
            display: none;
            position: absolute;
            right: 20px;
            top: 20px;
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            z-index: 1001;
            font-size: 1.2rem;
        }
        
        /* Sidebar Tabs */
        .sidebar-tabs {
            display: flex;
            border-bottom: 1px solid #334155;
            background-color: #0f172a;
        }
        
        .sidebar-tab {
            flex: 1;
            padding: 1rem;
            text-align: center;
            background: none;
            border: none;
            color: #94a3b8;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
        }
        
        .sidebar-tab.active {
            color: white;
        }
        
        .sidebar-tab.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: var(--gsu-gradient);
        }
        
        .sidebar-tab:hover {
            color: white;
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        /* Sidebar Content */
        .sidebar-content {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
        }
        
        .tab-pane {
            display: none;
        }
        
        .tab-pane.active {
            display: block;
        }
        
        /* Participants List */
        .participant-item {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            border-radius: 10px;
            margin-bottom: 0.75rem;
            background-color: rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
        }
        
        .participant-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .participant-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            margin-right: 1rem;
        }
        
        .participant-info {
            flex: 1;
        }
        
        .participant-name {
            font-weight: 600;
            color: white;
            margin-bottom: 0.1rem;
        }
        
        .participant-role {
            font-size: 0.8rem;
            color: #94a3b8;
        }
        
        .participant-status {
            font-size: 0.75rem;
            color: #10b981;
            display: flex;
            align-items: center;
        }
        
        .participant-status::before {
            content: '';
            display: inline-block;
            width: 6px;
            height: 6px;
            background-color: #10b981;
            border-radius: 50%;
            margin-right: 4px;
        }
        
        /* Chat Messages */
        .chat-messages {
            height: 400px;
            overflow-y: auto;
            margin-bottom: 1rem;
            padding-right: 0.5rem;
        }
        
        .chat-message {
            padding: 0.75rem;
            border-radius: 10px;
            margin-bottom: 0.75rem;
            background-color: rgba(255, 255, 255, 0.05);
            max-width: 85%;
        }
        
        .chat-message.sent {
            margin-left: auto;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
        }
        
        .chat-message.received {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .message-sender {
            font-weight: 600;
            font-size: 0.85rem;
            margin-bottom: 0.25rem;
        }
        
        .message-time {
            font-size: 0.75rem;
            color: #94a3b8;
            text-align: right;
            margin-top: 0.25rem;
        }
        
        .chat-input {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .chat-input input {
            flex: 1;
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid #475569;
            border-radius: 20px;
            padding: 0.75rem 1rem;
            color: white;
        }
        
        .chat-input input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
        }
        
        /* Resources List */
        .resource-item {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            border-radius: 10px;
            margin-bottom: 0.75rem;
            background-color: rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .resource-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }
        
        .resource-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            margin-right: 1rem;
        }
        
        .resource-info {
            flex: 1;
        }
        
        .resource-name {
            font-weight: 600;
            color: white;
            margin-bottom: 0.1rem;
        }
        
        .resource-size {
            font-size: 0.8rem;
            color: #94a3b8;
        }
        
        /* Control Buttons */
        .control-buttons {
            display: flex;
            gap: 1rem;
            padding: 1rem 1.5rem;
            background-color: #0f172a;
            border-top: 1px solid #334155;
        }
        
        .control-btn {
            flex: 1;
            padding: 0.75rem;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(59, 130, 246, 0.3);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }
        
        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(239, 68, 68, 0.3);
        }
        
        .btn-secondary {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }
        
        /* Staff Controls */
        .staff-controls {
            padding: 1rem;
            background-color: rgba(241, 196, 15, 0.1);
            border-radius: 10px;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--gsu-gold);
        }
        
        .staff-controls h6 {
            color: var(--gsu-gold);
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .staff-buttons {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.5rem;
        }
        
        .staff-btn {
            padding: 0.5rem;
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(241, 196, 15, 0.3);
            border-radius: 5px;
            color: #e2e8f0;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .staff-btn:hover {
            background-color: rgba(241, 196, 15, 0.2);
            border-color: var(--gsu-gold);
        }
        
        /* Recording Indicator */
        .recording-indicator {
            position: absolute;
            top: 20px;
            left: 20px;
            background-color: rgba(220, 38, 38, 0.9);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            z-index: 100;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            animation: pulse 2s infinite;
        }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }
        
        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 3px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        /* Loading Overlay */
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(15, 23, 42, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            z-index: 1000;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 3px solid rgba(255, 255, 255, 0.1);
            border-top-color: var(--gsu-green);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 1rem;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Classroom Header -->
    <div class="classroom-header">
        <div class="class-info">
            <h4>
                <?php echo $class_title; ?>
                <span class="live-indicator">LIVE</span>
            </h4>
            <div class="class-subtitle">
                <span>Course: <?php echo $class_code; ?></span>
                <span>Room: <?php echo substr($room_id, -8); ?></span>
                <span class="user-badge <?php echo $is_staff ? 'staff-badge' : 'student-badge'; ?>">
                    <i class="bi <?php echo $is_staff ? 'bi-person-badge' : 'bi-person-circle'; ?>"></i>
                    <?php echo $user_name; ?> (<?php echo ucfirst($user_role); ?>)
                </span>
            </div>
        </div>
        
        <div class="d-flex gap-2">
            <button class="btn btn-secondary" id="shareScreenBtn">
                <i class="bi bi-laptop"></i> Share Screen
            </button>
            <?php if ($is_staff): ?>
            <button class="btn btn-warning" id="recordBtn" data-recording="false">
                <i class="bi bi-record-circle"></i> Start Recording
            </button>
            <?php endif; ?>
            <button class="btn btn-outline-light" id="fullscreenBtn">
                <i class="bi bi-arrows-fullscreen"></i> Fullscreen
            </button>
        </div>
    </div>
    
    <!-- Sidebar Toggle Button (Mobile) -->
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="bi bi-list"></i>
    </button>
    
    <!-- Classroom Container -->
    <div class="classroom-container">
        <!-- Video Container -->
        <div class="video-container">
            <!-- Recording Indicator (Hidden by default) -->
            <div class="recording-indicator" id="recordingIndicator" style="display: none;">
                <i class="bi bi-record-circle-fill"></i> Recording in progress
            </div>
            
            <!-- Loading Overlay -->
            <div class="loading-overlay" id="loadingOverlay">
                <div class="spinner"></div>
                <h5>Connecting to classroom...</h5>
                <p class="text-muted mt-2">Please wait while we connect you to the live session</p>
            </div>
            
            <!-- Jitsi Meet Container -->
            <div id="meet"></div>
        </div>
        
        <!-- Controls Sidebar -->
        <div class="controls-sidebar" id="controlsSidebar">
            <!-- Sidebar Tabs -->
            <div class="sidebar-tabs">
                <button class="sidebar-tab active" data-tab="participants">
                    <i class="bi bi-people"></i> Participants
                </button>
                <button class="sidebar-tab" data-tab="chat">
                    <i class="bi bi-chat"></i> Chat
                </button>
                <button class="sidebar-tab" data-tab="resources">
                    <i class="bi bi-folder"></i> Resources
                </button>
            </div>
            
            <!-- Sidebar Content -->
            <div class="sidebar-content">
                <!-- Participants Tab -->
                <div class="tab-pane active" id="participantsTab">
                    <?php if ($is_staff): ?>
                    <div class="staff-controls">
                        <h6><i class="bi bi-gear"></i> Instructor Controls</h6>
                        <div class="staff-buttons">
                            <button class="staff-btn" id="muteAllBtn">
                                <i class="bi bi-mic-mute"></i> Mute All
                            </button>
                            <button class="staff-btn" id="muteVideoAllBtn">
                                <i class="bi bi-camera-video-off"></i> Stop Video All
                            </button>
                            <button class="staff-btn" id="lockRoomBtn">
                                <i class="bi bi-lock"></i> Lock Room
                            </button>
                            <button class="staff-btn" id="kickParticipantBtn">
                                <i class="bi bi-person-x"></i> Remove Participant
                            </button>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <h6 class="mb-3">Participants (<span id="participantCount">1</span>)</h6>
                    <div id="participantsList">
                        <!-- Participants will be populated by JavaScript -->
                        <div class="participant-item">
                            <div class="participant-avatar">
                                <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                            </div>
                            <div class="participant-info">
                                <div class="participant-name"><?php echo $user_name; ?></div>
                                <div class="participant-role"><?php echo ucfirst($user_role); ?></div>
                            </div>
                            <div class="participant-status">Connected</div>
                        </div>
                    </div>
                </div>
                
                <!-- Chat Tab -->
                <div class="tab-pane" id="chatTab">
                    <div class="chat-messages" id="chatMessages">
                        <!-- Chat messages will be populated by JavaScript -->
                        <div class="chat-message received">
                            <div class="message-sender">System</div>
                            <div>Welcome to the live classroom! You can use this chat to ask questions.</div>
                            <div class="message-time">Just now</div>
                        </div>
                    </div>
                    <div class="chat-input">
                        <input type="text" id="chatInput" placeholder="Type your message here...">
                        <button class="btn btn-primary" id="sendMessageBtn">
                            <i class="bi bi-send"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Resources Tab -->
                <div class="tab-pane" id="resourcesTab">
                    <h6 class="mb-3">Class Resources</h6>
                    <div id="resourcesList">
                        <div class="resource-item">
                            <div class="resource-icon">
                                <i class="bi bi-file-text"></i>
                            </div>
                            <div class="resource-info">
                                <div class="resource-name">Lecture Slides - Week 5</div>
                                <div class="resource-size">PDF • 2.4 MB</div>
                            </div>
                        </div>
                        <div class="resource-item">
                            <div class="resource-icon">
                                <i class="bi bi-file-pdf"></i>
                            </div>
                            <div class="resource-info">
                                <div class="resource-name">Assignment Guidelines</div>
                                <div class="resource-size">PDF • 1.1 MB</div>
                            </div>
                        </div>
                        <div class="resource-item">
                            <div class="resource-icon">
                                <i class="bi bi-link-45deg"></i>
                            </div>
                            <div class="resource-info">
                                <div class="resource-name">Reference Materials</div>
                                <div class="resource-size">External Link</div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($is_staff): ?>
                    <div class="mt-4">
                        <button class="btn btn-secondary w-100" id="uploadResourceBtn">
                            <i class="bi bi-upload"></i> Upload Resource
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Control Buttons -->
            <div class="control-buttons">
                <button class="control-btn btn-primary" id="muteBtn">
                    <i class="bi bi-mic"></i> Mute
                </button>
                <button class="control-btn btn-danger" id="videoBtn">
                    <i class="bi bi-camera-video"></i> Video
                </button>
                <button class="control-btn btn-danger" id="leaveBtn">
                    <i class="bi bi-telephone-x"></i> Leave
                </button>
            </div>
        </div>
    </div>
    
    <!-- Jitsi Meet External API -->
    <script src='https://meet.jit.si/external_api.js'></script>
    
    <script>
        // Configuration
        const domain = 'meet.jit.si';
        const roomName = <?php echo json_encode($room_id); ?>;
        const userName = <?php echo json_encode($user_name); ?>;
        const isStaff = <?php echo $is_staff ? 'true' : 'false'; ?>;
        const classTitle = <?php echo json_encode($class_title); ?>;
        
        // Jitsi API Options
        const options = {
            roomName: roomName,
            width: '100%',
            height: '100%',
            parentNode: document.querySelector('#meet'),
            userInfo: {
                displayName: userName,
                email: ''
            },
            configOverwrite: {
                startWithAudioMuted: true,
                startWithVideoMuted: false,
                prejoinPageEnabled: false,
                disableDeepLinking: true,
                disableInviteFunctions: !isStaff,
                enableWelcomePage: false,
                enableClosePage: false,
                disableInitialGUM: false,
                enableNoisyMicDetection: true,
                enableLayerSuspension: true,
                startSilent: false,
                constraints: {
                    video: {
                        height: {
                            ideal: 720,
                            max: 720,
                            min: 240
                        }
                    }
                }
            },
            interfaceConfigOverwrite: {
                TOOLBAR_BUTTONS: [
                    'microphone', 'camera', 'closedcaptions', 'desktop', 'fullscreen',
                    'fodeviceselection', 'hangup', 'profile', 'chat', 'recording',
                    'livestreaming', 'etherpad', 'sharedvideo', 'settings', 'raisehand',
                    'videoquality', 'filmstrip', 'invite', 'feedback', 'stats', 'shortcuts',
                    'tileview', 'videobackgroundblur', 'download', 'help', 'mute-everyone',
                    'security'
                ],
                SHOW_JITSI_WATERMARK: false,
                SHOW_WATERMARK_FOR_GUESTS: false,
                SHOW_CHROME_EXTENSION_BANNER: false,
                MOBILE_APP_PROMO: false,
                HIDE_INVITE_MORE_HEADER: false,
                DISABLE_JOIN_LEAVE_NOTIFICATIONS: true,
                DISABLE_FOCUS_INDICATOR: true,
                DISABLE_DOMINANT_SPEAKER_INDICATOR: true,
                DEFAULT_BACKGROUND: '#0f172a',
                DEFAULT_LOCAL_DISPLAY_NAME: 'You',
                DEFAULT_REMOTE_DISPLAY_NAME: 'Participant',
                TOOLBAR_ALWAYS_VISIBLE: true,
                SETTINGS_SECTIONS: ['devices', 'language', 'moderator', 'profile', 'calendar'],
                VIDEO_LAYOUT_FIT: 'both'
            },
            onload: function() {
                console.log('Jitsi Meet API loaded');
            }
        };
        
        // Initialize Jitsi Meet API
        const api = new JitsiMeetExternalAPI(domain, options);
        
        // DOM Elements
        const loadingOverlay = document.getElementById('loadingOverlay');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const controlsSidebar = document.getElementById('controlsSidebar');
        const tabButtons = document.querySelectorAll('.sidebar-tab');
        const tabPanes = document.querySelectorAll('.tab-pane');
        const muteBtn = document.getElementById('muteBtn');
        const videoBtn = document.getElementById('videoBtn');
        const leaveBtn = document.getElementById('leaveBtn');
        const fullscreenBtn = document.getElementById('fullscreenBtn');
        const shareScreenBtn = document.getElementById('shareScreenBtn');
        const recordBtn = document.getElementById('recordBtn');
        const recordingIndicator = document.getElementById('recordingIndicator');
        const chatInput = document.getElementById('chatInput');
        const sendMessageBtn = document.getElementById('sendMessageBtn');
        const chatMessages = document.getElementById('chatMessages');
        
        // State Variables
        let isMuted = true;
        let isVideoOn = true;
        let isRecording = false;
        let participants = [];
        let chatHistory = [];
        
        // Event Listeners for API
        api.on('videoConferenceJoined', () => {
            console.log('Video conference joined');
            loadingOverlay.style.display = 'none';
            
            // Set subject if staff
            if (isStaff) {
                api.executeCommand('subject', classTitle);
                api.executeCommand('toggleLobby', true);
            }
            
            // Load participants
            updateParticipants();
        });
        
        api.on('participantJoined', (participant) => {
            console.log('Participant joined:', participant);
            updateParticipants();
            addChatMessage('System', `${participant.displayName} joined the class`, true);
        });
        
        api.on('participantLeft', (participant) => {
            console.log('Participant left:', participant);
            updateParticipants();
            addChatMessage('System', `${participant.displayName} left the class`, true);
        });
        
        api.on('audioMuteStatusChanged', (participant) => {
            console.log('Audio mute status changed:', participant);
            updateParticipants();
        });
        
        api.on('videoMuteStatusChanged', (participant) => {
            console.log('Video mute status changed:', participant);
            updateParticipants();
        });
        
        api.on('incomingMessage', (message) => {
            console.log('Incoming message:', message);
            const sender = message.displayName || 'Unknown';
            addChatMessage(sender, message.message, false);
        });
        
        // Update participants list
        function updateParticipants() {
            const participantsContainer = document.getElementById('participantsList');
            const participantCount = document.getElementById('participantCount');
            
            // Get participants from API
            const allParticipants = api.getParticipantsInfo();
            participants = [
                {
                    id: 'local',
                    name: userName,
                    role: isStaff ? 'Instructor' : 'Student',
                    isAudioMuted: isMuted,
                    isVideoMuted: !isVideoOn
                },
                ...allParticipants.map(p => ({
                    id: p.participantId,
                    name: p.displayName,
                    role: 'Student',
                    isAudioMuted: p.isAudioMuted,
                    isVideoMuted: p.isVideoMuted
                }))
            ];
            
            // Update count
            participantCount.textContent = participants.length;
            
            // Clear and rebuild list
            participantsContainer.innerHTML = '';
            participants.forEach(participant => {
                const participantItem = document.createElement('div');
                participantItem.className = 'participant-item';
                participantItem.innerHTML = `
                    <div class="participant-avatar">
                        ${participant.name.charAt(0).toUpperCase()}
                    </div>
                    <div class="participant-info">
                        <div class="participant-name">${participant.name}</div>
                        <div class="participant-role">${participant.role}</div>
                    </div>
                    <div class="participant-status">
                        ${participant.isAudioMuted ? 'Mic off' : 'Mic on'} • 
                        ${participant.isVideoMuted ? 'Camera off' : 'Camera on'}
                    </div>
                `;
                participantsContainer.appendChild(participantItem);
            });
        }
        
        // Add chat message
        function addChatMessage(sender, message, isSystem = false) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `chat-message ${isSystem ? 'received' : sender === userName ? 'sent' : 'received'}`;
            
            const now = new Date();
            const timeString = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            
            messageDiv.innerHTML = `
                <div class="message-sender">${isSystem ? 'System' : sender}</div>
                <div>${message}</div>
                <div class="message-time">${timeString}</div>
            `;
            
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
            chatHistory.push({ sender, message, time: timeString, isSystem });
        }
        
        // Tab switching
        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                const tabId = button.getAttribute('data-tab');
                
                // Update active tab button
                tabButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                
                // Show corresponding tab pane
                tabPanes.forEach(pane => pane.classList.remove('active'));
                document.getElementById(`${tabId}Tab`).classList.add('active');
            });
        });
        
        // Mute/Unmute button
        muteBtn.addEventListener('click', () => {
            isMuted = !isMuted;
            api.executeCommand('toggleAudio');
            muteBtn.innerHTML = isMuted ? 
                '<i class="bi bi-mic-mute"></i> Unmute' : 
                '<i class="bi bi-mic"></i> Mute';
            muteBtn.classList.toggle('btn-primary', !isMuted);
            muteBtn.classList.toggle('btn-secondary', isMuted);
            updateParticipants();
        });
        
        // Video on/off button
        videoBtn.addEventListener('click', () => {
            isVideoOn = !isVideoOn;
            api.executeCommand('toggleVideo');
            videoBtn.innerHTML = isVideoOn ? 
                '<i class="bi bi-camera-video-off"></i> Stop Video' : 
                '<i class="bi bi-camera-video"></i> Start Video';
            videoBtn.classList.toggle('btn-danger', isVideoOn);
            videoBtn.classList.toggle('btn-secondary', !isVideoOn);
            updateParticipants();
        });
        
        // Leave button
        leaveBtn.addEventListener('click', () => {
            if (confirm('Are you sure you want to leave the classroom?')) {
                api.executeCommand('hangup');
                setTimeout(() => {
                    window.location.href = '<?php echo $is_staff ? "staff_dashboard.php" : "student_dashboard.php"; ?>';
                }, 1000);
            }
        });
        
        // Fullscreen button
        fullscreenBtn.addEventListener('click', () => {
            const videoContainer = document.querySelector('.video-container');
            if (!document.fullscreenElement) {
                videoContainer.requestFullscreen().catch(err => {
                    console.log(`Error attempting to enable fullscreen: ${err.message}`);
                });
                fullscreenBtn.innerHTML = '<i class="bi bi-fullscreen-exit"></i> Exit Fullscreen';
            } else {
                document.exitFullscreen();
                fullscreenBtn.innerHTML = '<i class="bi bi-arrows-fullscreen"></i> Fullscreen';
            }
        });
        
        // Share screen button
        shareScreenBtn.addEventListener('click', () => {
            api.executeCommand('toggleShareScreen');
        });
        
        // Recording button (staff only)
        if (recordBtn) {
            recordBtn.addEventListener('click', () => {
                isRecording = !isRecording;
                if (isRecording) {
                    api.executeCommand('startRecording', {
                        mode: 'file',
                        dropbox: {
                            appKey: null,
                            redirectURI: null
                        }
                    });
                    recordBtn.innerHTML = '<i class="bi bi-stop-circle"></i> Stop Recording';
                    recordBtn.classList.remove('btn-warning');
                    recordBtn.classList.add('btn-danger');
                    recordingIndicator.style.display = 'flex';
                } else {
                    api.executeCommand('stopRecording', 'file');
                    recordBtn.innerHTML = '<i class="bi bi-record-circle"></i> Start Recording';
                    recordBtn.classList.remove('btn-danger');
                    recordBtn.classList.add('btn-warning');
                    recordingIndicator.style.display = 'none';
                }
            });
        }
        
        // Sidebar toggle for mobile
        sidebarToggle.addEventListener('click', () => {
            controlsSidebar.classList.toggle('active');
            sidebarToggle.innerHTML = controlsSidebar.classList.contains('active') ? 
                '<i class="bi bi-x"></i>' : '<i class="bi bi-list"></i>';
        });
        
        // Send chat message
        function sendChatMessage() {
            const message = chatInput.value.trim();
            if (message) {
                api.executeCommand('sendChatMessage', message);
                addChatMessage(userName, message, false);
                chatInput.value = '';
            }
        }
        
        sendMessageBtn.addEventListener('click', sendChatMessage);
        chatInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                sendChatMessage();
            }
        });
        
        // Staff controls
        if (isStaff) {
            document.getElementById('muteAllBtn').addEventListener('click', () => {
                api.executeCommand('muteEveryone');
                addChatMessage('System', 'Instructor muted all participants', true);
            });
            
            document.getElementById('muteVideoAllBtn').addEventListener('click', () => {
                // Note: This requires moderator privileges
                addChatMessage('System', 'Instructor requested all participants to stop video', true);
            });
            
            document.getElementById('lockRoomBtn').addEventListener('click', function() {
                const isLocked = api.isLocked();
                api.executeCommand('toggleLobby', !isLocked);
                this.innerHTML = isLocked ? 
                    '<i class="bi bi-unlock"></i> Unlock Room' : 
                    '<i class="bi bi-lock"></i> Lock Room';
                addChatMessage('System', `Room ${isLocked ? 'unlocked' : 'locked'} by instructor`, true);
            });
        }
        
        // Handle window close/refresh
        window.addEventListener('beforeunload', (e) => {
            e.preventDefault();
            api.executeCommand('hangup');
            return null;
        });
        
        // Initialize UI
        updateParticipants();
        addChatMessage('System', `Welcome to ${classTitle}! The session has started.`, true);
        
        // Hide loading after 5 seconds (fallback)
        setTimeout(() => {
            loadingOverlay.style.display = 'none';
        }, 5000);
    </script>
</body>
</html>