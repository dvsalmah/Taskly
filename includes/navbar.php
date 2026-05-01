<?php
$_nav_user      = $_SESSION['user'] ?? [];
$_nav_photo_raw = $_nav_user['photo'] ?? '';

if (empty($_nav_photo_raw)) {
    $_nav_foto = 'https://i.pravatar.cc/150?img=8';
} elseif (strpos($_nav_photo_raw, 'http') === 0) {
    $_nav_foto = $_nav_photo_raw;
} else {
    $_nav_photo_path = __DIR__ . '/../' . $_nav_photo_raw;
    $_nav_foto = file_exists($_nav_photo_path) ? '../' . $_nav_photo_raw : 'https://i.pravatar.cc/150?img=8';
}

$_nav_date_full = date('l, d F Y');
?>
<header class="topbar">

    <a href="homepage.php" class="topbar-logo-link">
        <img src="../assets/taskly-HD.png" alt="Taskly" class="topbar-logo-img">
    </a>

    <div class="topbar-search-center">
        <div class="search-wrap">
            <img src="../assets/seacrh.svg" alt="" class="search-icon">
            <input type="text" id="globalSearch" placeholder="Search your task here"
                   class="search-input" autocomplete="off">
        </div>
    </div>

    <div class="topbar-right">

        <button type="button" class="topbar-icon-btn" title="Notifications">
            <img src="../assets/notif.svg" alt="Notifications" class="topbar-icon">
        </button>

        <!-- Calendar with popup -->
        <div class="topbar-cal-wrap" id="calWrap">
            <button type="button" class="topbar-icon-btn" id="calToggleBtn" title="<?= htmlspecialchars($_nav_date_full) ?>">
                <img src="../assets/calendar.svg" alt="Calendar" class="topbar-icon">
            </button>

            <div class="cal-popup" id="calPopup" style="display:none;">
                <div class="cal-header">
                    <button class="cal-nav" id="calPrev">&#8249;</button>
                    <span class="cal-month-label" id="calMonthLabel"></span>
                    <button class="cal-nav" id="calNext">&#8250;</button>
                </div>
                <div class="cal-dow">
                    <span>Su</span><span>Mo</span><span>Tu</span>
                    <span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
                </div>
                <div class="cal-grid" id="calGrid"></div>
                <div class="cal-today-label"><?= htmlspecialchars($_nav_date_full) ?></div>
            </div>
        </div>

        <a href="profile.php" class="topbar-avatar-link" title="My Profile">
            <img src="<?= htmlspecialchars($_nav_foto) ?>"
                 alt="Profile"
                 class="topbar-avatar"
                 onerror="this.src='https://i.pravatar.cc/150?img=8'">
        </a>

    </div>
</header>

<script>
(function () {
    var calWrap  = document.getElementById('calWrap');
    var calPopup = document.getElementById('calPopup');
    var calToggle = document.getElementById('calToggleBtn');
    var calGrid  = document.getElementById('calGrid');
    var calLabel = document.getElementById('calMonthLabel');
    var calPrev  = document.getElementById('calPrev');
    var calNext  = document.getElementById('calNext');

    if (!calToggle || !calPopup) return;

    var now     = new Date();
    var viewYear  = now.getFullYear();
    var viewMonth = now.getMonth(); // 0-indexed

    var MONTHS = ['January','February','March','April','May','June',
                  'July','August','September','October','November','December'];

    function buildCalendar() {
        var firstDay = new Date(viewYear, viewMonth, 1).getDay(); // 0=Sun
        var daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();
        calLabel.textContent = MONTHS[viewMonth] + ' ' + viewYear;

        var html = '';
        // blank cells before 1st
        for (var i = 0; i < firstDay; i++) html += '<span class="cal-day empty"></span>';
        for (var d = 1; d <= daysInMonth; d++) {
            var isToday = (d === now.getDate() && viewMonth === now.getMonth() && viewYear === now.getFullYear());
            html += '<span class="cal-day' + (isToday ? ' today' : '') + '">' + d + '</span>';
        }
        calGrid.innerHTML = html;
    }

    function togglePopup(show) {
        calPopup.style.display = show ? 'block' : 'none';
        if (show) buildCalendar();
    }

    calToggle.addEventListener('click', function (e) {
        e.stopPropagation();
        var visible = calPopup.style.display !== 'none';
        togglePopup(!visible);
    });

    calPrev.addEventListener('click', function (e) {
        e.stopPropagation();
        viewMonth--;
        if (viewMonth < 0) { viewMonth = 11; viewYear--; }
        buildCalendar();
    });

    calNext.addEventListener('click', function (e) {
        e.stopPropagation();
        viewMonth++;
        if (viewMonth > 11) { viewMonth = 0; viewYear++; }
        buildCalendar();
    });

    document.addEventListener('click', function (e) {
        if (!calWrap.contains(e.target)) togglePopup(false);
    });
})();
</script>