document.addEventListener('DOMContentLoaded', function () {

    function openModal(el) {
        el.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function closeModal(el) {
        el.classList.remove('open');
        document.body.style.overflow = '';
    }
    window.closeModal = closeModal;

    var taskModal    = document.getElementById('taskModal');
    var openTaskBtn  = document.getElementById('openTaskModal');
    var closeTaskBtn = document.getElementById('closeTaskModal');
    var cancelAddBtn = document.getElementById('cancelAddTask');

    if (openTaskBtn && taskModal) openTaskBtn.addEventListener('click', function () { openModal(taskModal); });
    if (closeTaskBtn && taskModal) closeTaskBtn.addEventListener('click', function () { closeModal(taskModal); });
    if (cancelAddBtn && taskModal) cancelAddBtn.addEventListener('click', function () { closeModal(taskModal); });

    var editModal     = document.getElementById('editModal');
    var closeEditBtn  = document.getElementById('closeEditModal');
    var cancelEditBtn = document.getElementById('cancelEditTask');

    if (closeEditBtn && editModal) closeEditBtn.addEventListener('click', function () { closeModal(editModal); });
    if (cancelEditBtn && editModal) cancelEditBtn.addEventListener('click', function () { closeModal(editModal); });
    if (editModal) editModal.addEventListener('click', function (e) { if (e.target === editModal) closeModal(editModal); });

    function timeAgoJS(datetimeStr) {
        if (!datetimeStr) return '';
        var then = new Date(datetimeStr.replace(' ', 'T'));
        var diff = Math.floor((Date.now() - then.getTime()) / 1000);
        if (diff < 60)     return 'just now';
        if (diff < 3600)   return Math.floor(diff / 60) + ' min ago';
        if (diff < 86400)  return Math.floor(diff / 3600) + ' hr ago';
        if (diff < 604800) return Math.floor(diff / 86400) + ' days ago';
        return then.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    function deadlineLabelJS(dl) {
        if (!dl) return '';
        var dlDate = new Date(dl.replace(' ', 'T'));
        var diffMs = dlDate - Date.now();
        if (diffMs < 0)              return 'Overdue';
        if (diffMs < 3600000)        return 'Due in ' + Math.ceil(diffMs / 60000) + ' min';
        if (diffMs < 86400000)       return 'Due in ' + Math.ceil(diffMs / 3600000) + ' hr';
        if (diffMs < 172800000)      return 'Due tomorrow';
        return 'Due ' + dlDate.toLocaleDateString('en-GB', {day:'2-digit',month:'short',year:'numeric'})
                     + ' ' + dlDate.toLocaleTimeString('en-GB', {hour:'2-digit',minute:'2-digit'});
    }

    var searchInput = document.getElementById('globalSearch');
    var chips       = document.querySelectorAll('.chip[data-filter]');
    var allCards    = document.querySelectorAll('.task-card[data-id]');
    var priSel      = document.getElementById('priorityFilter');

    var activeStatus   = 'all';
    var activePriority = 'all';

    function applyFilters() {
        var anyVisible = false;
        var searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';

        allCards.forEach(function (card) {
            
            var statusOk   = (activeStatus   === 'all' || card.dataset.status   === activeStatus);
            var priorityOk = (activePriority === 'all' || card.dataset.priority === activePriority);
            
            var title   = (card.dataset.title   || '').toLowerCase();
            var desc    = (card.dataset.desc    || '').toLowerCase();
            var catName = (card.dataset.catName || '').toLowerCase();
            var searchOk = !searchQuery || title.includes(searchQuery) || desc.includes(searchQuery) || catName.includes(searchQuery);

            var show = statusOk && priorityOk && searchOk;
            card.style.display = show ? '' : 'none';
            if (show) anyVisible = true;
        });

        var emptyEl = document.getElementById('emptyState');
        if (emptyEl) emptyEl.style.display = anyVisible ? 'none' : '';
    }

    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
    }
    chips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            chips.forEach(function (c) { c.classList.remove('active'); });
            chip.classList.add('active');
            activeStatus = chip.dataset.filter;
            applyFilters();
        });
    });

    if (priSel) {
        priSel.addEventListener('change', function () {
            activePriority = priSel.value;
            applyFilters();
        });
    }

    var previewModal    = document.getElementById('previewModal');
    var closePreviewBtn = document.getElementById('closePreviewModal');
    var _currentCardData = {};

    function fillEditModal(data) {
        document.getElementById('edit_id').value       = data.id;
        document.getElementById('edit_title').value    = data.title;
        document.getElementById('edit_desc').value     = data.desc;
        document.getElementById('edit_priority').value = data.priority;
        document.getElementById('edit_status').value   = data.status;
        var dl = (data.deadline || '').replace(' ', 'T');
        document.getElementById('edit_deadline').value = dl;
    }

    if (closePreviewBtn && previewModal) closePreviewBtn.addEventListener('click', function () { closeModal(previewModal); });
    if (previewModal) previewModal.addEventListener('click', function (e) { if (e.target === previewModal) closeModal(previewModal); });

    function openPreview(card) {
        if (!previewModal) return;

        var data = {
            id:       card.dataset.id       || '',
            title:    card.dataset.title    || '',
            desc:     card.dataset.desc     || '',
            category: card.dataset.category || '',
            priority: card.dataset.priority || 'low',
            status:   card.dataset.status   || 'not_started',
            deadline: card.dataset.deadline || '',
            catName:  card.dataset.catName  || '',
            catColor: card.dataset.catColor || '',
            updated:  card.dataset.updated  || '',
        };
        _currentCardData = data;

        document.getElementById('pv_title').textContent = data.title;
        document.getElementById('pv_status_task_id').value = data.id;
        document.getElementById('pv_delete_id').value = data.id;
        document.getElementById('pv_status_select').value  = data.status;

        var priEl = document.getElementById('pv_priority_badge');
        var priMap = { high: ['High', 'badge-priority-high'], medium: ['Medium', 'badge-priority-medium'], low: ['Low', 'badge-priority-low'] };
        var priInfo = priMap[data.priority] || priMap['low'];
        priEl.textContent = priInfo[0];
        priEl.className   = 'badge ' + priInfo[1];
        document.getElementById('pv_priority_text').textContent = priInfo[0];

        var descEl = document.getElementById('pv_desc');
        if (data.desc) { descEl.textContent = data.desc; descEl.style.display = ''; } else { descEl.style.display = 'none'; }

        var stInfo = { completed: ['Completed', 'badge-completed'], in_progress: ['In Progress', 'badge-progress'], not_started: ['Not Started', 'badge-notstarted'] }[data.status] || ['Not Started', 'badge-notstarted'];
        var stEl = document.getElementById('pv_status');
        stEl.textContent = stInfo[0]; stEl.className = 'badge ' + stInfo[1];

        var catRow = document.getElementById('pv_cat_row'), catEl = document.getElementById('pv_category');
        if (data.catName) { catEl.textContent = data.catName; catEl.style.color = data.catColor; catRow.style.display = ''; } else { catRow.style.display = 'none'; }

        var dlRow = document.getElementById('pv_dl_row'), dlEl = document.getElementById('pv_deadline');
        if (data.deadline) { dlEl.textContent = deadlineLabelJS(data.deadline); dlRow.style.display = ''; } else { dlRow.style.display = 'none'; }

        var dateEl = card.querySelector('.task-date');
        document.getElementById('pv_created').textContent = dateEl ? dateEl.textContent.trim() : '';

        var updRow = document.getElementById('pv_updated_row'), updEl = document.getElementById('pv_updated');
        if (data.updated) { updEl.textContent = 'edited ' + timeAgoJS(data.updated); updRow.style.display = ''; } else { updRow.style.display = 'none'; }

        openModal(previewModal);
    }

    document.querySelectorAll('.task-card[data-id]').forEach(function (card) { card.addEventListener('click', function () { openPreview(card); }); });

    var pvEditBtn = document.getElementById('pv_edit_btn');
    if (pvEditBtn) pvEditBtn.addEventListener('click', function () { closeModal(previewModal); fillEditModal(_currentCardData); if (editModal) openModal(editModal); });

    var pvDeleteBtn = document.getElementById('pv_delete_btn');
    if (pvDeleteBtn) pvDeleteBtn.addEventListener('click', function () { if (confirm('Delete this task?')) document.getElementById('pv_delete_form').submit(); });

    document.querySelectorAll('.status-select').forEach(function (sel) { sel.addEventListener('change', function () { var form = sel.closest('form'); if (form) form.submit(); }); });

});