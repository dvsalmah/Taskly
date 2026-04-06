<header class="topbar">
    <div class="search-wrap">
        <input type="text" placeholder="Search your task here" class="search-input">
            <button class="icon-btn">
            <img src="../assets/seacrh.svg">    
            </button>
    </div>
    <div class="topbar-right">
        <button type="button" class="icon-btn">
            <img src="../assets/notif.svg">
        </button>
        <button type="button" class="icon-btn">
            <img src="../assets/calendar.svg">
        </button>
        <div class="topbar-date">
            <span class="day"><?= date('l') ?></span>
            <span class="date"><?= date('d/m/Y') ?></span>
        </div>
    </div>
</header>