@push('js')
<script>
@can('report_subscriptions.manage')
@php
    $subscriptionConfig = [
        'store_url' => route('subscriptions.store'),
        'metadata_url_template' => route('reports.subscriptions.metadata', ['report' => '__REPORT__']),
        'update_url_template' => route('subscriptions.update', ['reportSubscription' => '__SUB__']),
        'edit_url_template' => route('subscriptions.edit', ['reportSubscription' => '__SUB__']),
        'show_url_template' => route('subscriptions.show', ['reportSubscription' => '__SUB__']),
        'default_subject' => __('subscriptions.mail.subject.default'),
        'default_body' => __('subscriptions.mail.body_default'),
        'messages' => [
            'metadata_error' => __('subscriptions.feedback.metadata_error'),
            'save_error' => __('subscriptions.feedback.save_error'),
            'save_success' => __('subscriptions.feedback.save_success'),
            'loading' => __('subscriptions.feedback.loading'),
        ],
        'placeholders' => [
            'groups_empty' => __('subscriptions.placeholders.groups_empty'),
            'users_empty' => __('subscriptions.placeholders.users_empty'),
            'notification_users_empty' => __('subscriptions.placeholders.notification_users_empty'),
            'notification_groups_empty' => __('subscriptions.placeholders.notification_groups_empty'),
        ],
        'labels' => [
            'page' => __('subscriptions.labels.page'),
        ],
        'directory' => [
            'groups' => \App\Models\Group::query()
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn ($group) => [
                    'id' => $group->id,
                    'name' => $group->name,
                ])
                ->values(),
            'users' => \App\Models\User::query()
                ->orderBy('name')
                ->get(['id', 'name', 'email'])
                ->map(fn ($user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ])
                ->values(),
        ],
        'snippets' => $subscriptionSnippetOptions ?? [],
        'email_preview_empty' => __('subscriptions.email_editor.preview_empty'),
    ];
@endphp
window.reportSubscriptionConfig = @json($subscriptionConfig);

(function () {
    if (!window.jQuery) return;

    const config = window.reportSubscriptionConfig || {};
    const directory = {
        groups: Array.isArray(config.directory?.groups) ? config.directory.groups : [],
        users: Array.isArray(config.directory?.users) ? config.directory.users : [],
    };
    const state = {
        mode: 'create',
        submitUrl: config.store_url,
        submitMethod: 'POST',
        editingId: null,
    };

    const form = document.getElementById('report-subscription-form');
    const modalEl = document.getElementById('report-subscription-modal');
    if (!form || !modalEl) return;

    const $modal = window.$('#report-subscription-modal');
    const createButtons = Array.from(document.querySelectorAll('[data-open-subscription]'));

    const elements = {
        errorsBox: document.getElementById('report-subscription-errors'),
        errorsList: document.querySelector('#report-subscription-errors ul'),
        reportSelect: document.getElementById('subscription-report'),
        pagesCard: document.getElementById('subscription-pages-card'),
        pagesList: document.getElementById('subscription-pages-list'),
        groupSelect: document.getElementById('subscription-groups'),
        userSelect: document.getElementById('subscription-users'),
        notifUsersSelect: document.getElementById('subscription-notification-users'),
        notifGroupsSelect: document.getElementById('subscription-notification-groups'),
        recurrenceSelect: document.getElementById('subscription-recurrence'),
        cronWrapper: document.getElementById('cron-expression-wrapper'),
        cronInput: document.getElementById('subscription-cron'),
        timeInput: document.getElementById('subscription-time'),
        startInput: document.getElementById('subscription-start-date'),
        endInput: document.getElementById('subscription-end-date'),
        nameInput: document.getElementById('subscription-name'),
        formatSelect: document.getElementById('subscription-format'),
        maxSizeInput: document.getElementById('subscription-max-size'),
        timezoneInput: document.getElementById('subscription-timezone'),
        enabledSwitch: document.getElementById('subscription-enabled'),
        subjectInput: document.getElementById('subscription-mail-subject'),
        bodyInput: document.getElementById('subscription-mail-body'),
        notifyAdminsCheckbox: document.getElementById('subscription-notify-admins'),
        emailNav: document.getElementById('subscription-email-editor-nav'),
        emailPreviewPane: document.getElementById('subscription-email-editor-preview'),
        emailPreviewFrame: document.getElementById('subscription-mail-preview-frame'),
        snippetsMenu: document.getElementById('subscription-snippets-menu'),
        designPane: document.getElementById('subscription-email-editor-design'),
        designEditor: document.getElementById('subscription-design-editor'),
        designToolbar: document.getElementById('subscription-email-toolbar'),
        designFontSize: document.getElementById('subscription-design-fontsize'),
    };

    const recurrenceFields = Array.from(document.querySelectorAll('.recurrence-dependent'));
    const metadataCache = new Map();
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    let activeInput = elements.bodyInput || elements.subjectInput || null;

    if (elements.subjectInput) {
        elements.subjectInput.addEventListener('focus', () => {
            activeInput = elements.subjectInput;
        });
    }

    if (elements.bodyInput) {
        elements.bodyInput.addEventListener('focus', () => {
            activeInput = elements.bodyInput;
        });
        elements.bodyInput.addEventListener('input', () => {
            if (isPreviewActive()) {
                updatePreview();
            }
            syncTextareaToDesign();
        });
    }

    if (elements.designEditor) {
        elements.designEditor.innerHTML = elements.bodyInput?.value || '';
        elements.designEditor.addEventListener('focus', () => {
            activeInput = 'design';
        });
        elements.designEditor.addEventListener('input', () => {
            syncDesignToTextarea();
            if (isPreviewActive()) {
                updatePreview();
            }
        });
    }

    if (elements.designToolbar) {
        elements.designToolbar.addEventListener('mousedown', (event) => {
            if (event.target.closest('button')) {
                event.preventDefault();
            }
        });
        elements.designToolbar.addEventListener('click', (event) => {
            const trigger = event.target.closest('[data-design-command]');
            if (!trigger) return;
            const command = trigger.getAttribute('data-design-command');
            if (!command) return;
            applyDesignCommand(command);
        });
    }

    if (elements.designFontSize) {
        elements.designFontSize.addEventListener('change', () => {
            const value = elements.designFontSize.value;
            if (!value) return;
            applyDesignCommand('fontSize', value);
            elements.designFontSize.selectedIndex = 0;
        });
    }

    if (elements.snippetsMenu) {
        elements.snippetsMenu.addEventListener('click', (event) => {
            const trigger = event.target.closest('[data-snippet-value]');
            if (!trigger) {
                return;
            }
            insertSnippet(trigger.getAttribute('data-snippet-value') || '');
        });
    }

    if (elements.emailNav && window.$) {
        window.$(elements.emailNav).on('shown.bs.tab', 'a[data-toggle="pill"]', function () {
            const target = this.getAttribute('href');
            if (target === '#subscription-email-editor-design') {
                syncTextareaToDesign();
                focusDesignEditor();
            } else if (target === '#subscription-email-editor-preview') {
                updatePreview();
            } else if (target === '#subscription-email-editor-html') {
                activeInput = elements.bodyInput;
            }
        });
    }

    if (window.location.hash === '#create-subscription' && createButtons.length) {
        resetForm();
        setMode('create');
        showModal();
        history.replaceState(null, '', window.location.pathname);
    }

    document.addEventListener('click', (event) => {
        const createTrigger = event.target.closest('[data-open-subscription]');
        if (createTrigger) {
            event.preventDefault();
            resetForm();
            setMode('create');
            showModal();
            return;
        }

        const editTrigger = event.target.closest('[data-subscription-edit]');
        if (!editTrigger) return;
        const id = editTrigger.getAttribute('data-subscription-edit');
        if (!id) return;
        event.preventDefault();
        loadSubscription(id);
    });

    if (elements.recurrenceSelect) {
        elements.recurrenceSelect.addEventListener('change', updateRecurrenceView);
        updateRecurrenceView();
    }

    if (elements.reportSelect) {
        elements.reportSelect.addEventListener('change', () => {
            const reportId = elements.reportSelect.value || '';
            loadMetadata(reportId).catch(() => {});
        });
    }

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        showErrors([]);
        disableSubmit(true);

        fetch(state.submitUrl, {
            method: state.submitMethod,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify(collectPayload()),
        })
            .then(async (response) => {
                disableSubmit(false);
                if (response.ok) {
                    const data = await response.json();
                    if (data.redirect) {
                        window.location.href = data.redirect;
                        return;
                    }
                    if (config.messages?.save_success) {
                        console.info(config.messages.save_success);
                    }
                    $modal.modal('hide');
                    return;
                }
                if (response.status === 422) {
                    const data = await response.json();
                    const errs = data.errors ? Object.values(data.errors).flat() : [];
                    showErrors(errs);
                    return;
                }
                showErrors([config.messages?.save_error || 'Error']);
            })
            .catch(() => {
                disableSubmit(false);
                showErrors([config.messages?.save_error || 'Error']);
            });
    });

    function showModal() {
        $modal.modal('show');
    }

    function setMode(mode, id = null) {
        state.mode = mode;
        state.editingId = id;
        if (mode === 'edit' && id) {
            state.submitUrl = buildUpdateUrl(id);
            state.submitMethod = 'PUT';
        } else {
            state.submitUrl = config.store_url;
            state.submitMethod = 'POST';
        }
    }

    function buildUpdateUrl(id) {
        return (config.update_url_template || '').replace('__SUB__', encodeURIComponent(id));
    }

    function buildEditUrl(id) {
        return (config.edit_url_template || '').replace('__SUB__', encodeURIComponent(id));
    }

    function buildMetadataUrl(reportId) {
        return (config.metadata_url_template || '').replace('__REPORT__', encodeURIComponent(reportId));
    }

    function todayString() {
        const d = new Date();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${d.getFullYear()}-${month}-${day}`;
    }

    function normalizeDateValue(value) {
        if (!value) return '';
        if (typeof value === 'string') {
            const isoMatch = value.match(/^(\d{4}-\d{2}-\d{2})/);
            if (isoMatch) {
                return isoMatch[1];
            }
        }
        try {
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) return '';
            const month = String(date.getUTCMonth() + 1).padStart(2, '0');
            const day = String(date.getUTCDate()).padStart(2, '0');
            return `${date.getUTCFullYear()}-${month}-${day}`;
        } catch (_) {
            return '';
        }
    }

    function parseTimeInput(value) {
        if (typeof value !== 'string') return { hour: null, minute: null };
        const match = value.trim().match(/^(\d{1,2}):(\d{2})(?::\d{2})?$/);
        if (!match) return { hour: null, minute: null };

        const hour = Number(match[1]);
        const minute = Number(match[2]);

        if (!Number.isFinite(hour) || !Number.isFinite(minute)) {
            return { hour: null, minute: null };
        }

        if (hour < 0 || hour > 23 || minute < 0 || minute > 59) {
            return { hour: null, minute: null };
        }

        return {
            hour: Math.floor(hour),
            minute: Math.floor(minute),
        };
    }

    function formatTimeInput(hour, minute) {
        const h = Number(hour);
        const m = Number(minute);

        if (!Number.isFinite(h) || !Number.isFinite(m)) {
            return '';
        }

        if (h < 0 || h > 23 || m < 0 || m > 59) {
            return '';
        }

        return `${String(Math.floor(h)).padStart(2, '0')}:${String(Math.floor(m)).padStart(2, '0')}`;
    }

    function showErrors(messages) {
        if (!elements.errorsBox || !elements.errorsList) return;
        if (!messages || messages.length === 0) {
            elements.errorsBox.classList.add('d-none');
            elements.errorsList.innerHTML = '';
            return;
        }
        elements.errorsBox.classList.remove('d-none');
        elements.errorsList.innerHTML = '';
        messages.forEach((msg) => {
            const li = document.createElement('li');
            li.textContent = msg;
            elements.errorsList.appendChild(li);
        });
    }

    function renderRecipientList(container, items, selectedIds = [], placeholder = '') {
        if (!container) return;
        const selected = new Set((selectedIds || []).map((value) => String(value)));
        container.innerHTML = '';

        if (!items || !items.length) {
            if (placeholder) {
                const empty = document.createElement('p');
                empty.className = 'text-muted small mb-0';
                empty.textContent = placeholder;
                container.appendChild(empty);
            }
            return;
        }

        const fragment = document.createDocumentFragment();
        items.forEach((item) => {
            const wrapper = document.createElement('div');
            wrapper.className = 'custom-control custom-checkbox mb-1';
            const baseId = container.id || 'recipient';
            const checkboxId = `${baseId}-${item.id}`;

            const input = document.createElement('input');
            input.type = 'checkbox';
            input.className = 'custom-control-input';
            input.id = checkboxId;
            input.value = item.id;
            input.dataset.id = item.id;
            input.checked = selected.has(String(item.id));

            const label = document.createElement('label');
            label.className = 'custom-control-label d-flex flex-column';
            label.setAttribute('for', checkboxId);

            const displayName = document.createElement('span');
            displayName.textContent = item.name || '';
            label.appendChild(displayName);

            if (item.email) {
                const email = document.createElement('small');
                email.className = 'text-muted';
                email.textContent = item.email;
                label.appendChild(email);
            }

            wrapper.appendChild(input);
            wrapper.appendChild(label);
            fragment.appendChild(wrapper);
        });

        container.appendChild(fragment);
    }

    function gatherSelectedIds(container) {
        if (!container) return [];
        return Array.from(container.querySelectorAll('input[type="checkbox"][data-id]:checked'))
            .map((input) => Number(input.dataset.id))
            .filter((value) => Number.isFinite(value));
    }

    function captureCurrentSelections() {
        return {
            groupIds: gatherSelectedIds(elements.groupSelect),
            userIds: gatherSelectedIds(elements.userSelect),
            notificationUserIds: gatherSelectedIds(elements.notifUsersSelect),
            notificationGroupIds: gatherSelectedIds(elements.notifGroupsSelect),
        };
    }

    function populateRecipientsFromDirectory(selections = {}) {
        const groupItems = directory.groups || [];
        const userItems = directory.users || [];
        renderRecipientList(elements.groupSelect, groupItems, selections.groupIds, config.placeholders?.groups_empty);
        renderRecipientList(elements.userSelect, userItems, selections.userIds, config.placeholders?.users_empty);
        renderRecipientList(elements.notifUsersSelect, userItems, selections.notificationUserIds, config.placeholders?.notification_users_empty);
        renderRecipientList(elements.notifGroupsSelect, groupItems, selections.notificationGroupIds, config.placeholders?.notification_groups_empty);
    }

    function isPreviewActive() {
        return Boolean(elements.emailPreviewPane?.classList.contains('active'));
    }

    function updatePreview() {
        if (!elements.emailPreviewFrame) return;
        const html = (elements.bodyInput?.value || '').trim();
        const fallback = config.email_preview_empty || '';
        const bodyContent = html !== '' ? html : `<div style="font-family: Arial, sans-serif; color: #6c757d;">${fallback}</div>`;
        const fullDocument = /^<!DOCTYPE/i.test(bodyContent) || /^<html/i.test(bodyContent)
            ? bodyContent
            : `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Email preview</title>
    <style>body{margin:0;padding:24px;font-family:Arial,sans-serif;background-color:#f8f9fa;color:#212529;} a{color:#007bff;}</style>
</head>
<body>${bodyContent}</body>
</html>`;
        elements.emailPreviewFrame.srcdoc = fullDocument;
    }

    function focusDesignEditor() {
        if (!elements.designEditor) return;
        elements.designEditor.focus();
        activeInput = 'design';
    }

    function syncTextareaToDesign() {
        if (!elements.designEditor || !elements.bodyInput) return;
        elements.designEditor.innerHTML = elements.bodyInput.value || '';
    }

    function syncDesignToTextarea() {
        if (!elements.designEditor || !elements.bodyInput) return;
        elements.bodyInput.value = elements.designEditor.innerHTML;
    }

    function applyDesignCommand(command, value = null) {
        if (!elements.designEditor) return;
        focusDesignEditor();
        document.execCommand(command, false, value);
        syncDesignToTextarea();
        if (isPreviewActive()) {
            updatePreview();
        }
    }

    function insertSnippet(snippet) {
        if (!snippet) return;
        if (activeInput === 'design') {
            if (!elements.designEditor) return;
            focusDesignEditor();
            document.execCommand('insertHTML', false, snippet);
            syncDesignToTextarea();
            if (isPreviewActive()) {
                updatePreview();
            }
            return;
        }
        const target = activeInput || elements.bodyInput || elements.subjectInput;
        if (!target) return;
        const { selectionStart, selectionEnd, value = '' } = target;
        const start = typeof selectionStart === 'number' ? selectionStart : value.length;
        const end = typeof selectionEnd === 'number' ? selectionEnd : start;
        target.value = `${value.slice(0, start)}${snippet}${value.slice(end)}`;
        if (typeof target.setSelectionRange === 'function') {
            const position = start + snippet.length;
            target.setSelectionRange(position, position);
        }
        target.dispatchEvent(new Event('input', { bubbles: true }));
        if (typeof target.focus === 'function') {
            target.focus();
        }
        if (target === elements.bodyInput && isPreviewActive()) {
            updatePreview();
        }
    }

    function populateRecipientsFromDirectory(selections = {}) {
        const groupItems = directory.groups || [];
        const userItems = directory.users || [];
        renderRecipientList(elements.groupSelect, groupItems, selections.groupIds, config.placeholders?.groups_empty);
        renderRecipientList(elements.userSelect, userItems, selections.userIds, config.placeholders?.users_empty);
        renderRecipientList(elements.notifUsersSelect, userItems, selections.notificationUserIds, config.placeholders?.notification_users_empty);
        renderRecipientList(elements.notifGroupsSelect, groupItems, selections.notificationGroupIds, config.placeholders?.notification_groups_empty);
    }

    function populatePages(pages, selectedNames = new Set()) {
        if (!elements.pagesList || !elements.pagesCard) return;
        elements.pagesList.innerHTML = '';
        if (!pages || !pages.length) {
            elements.pagesCard.classList.add('d-none');
            return;
        }
        elements.pagesCard.classList.remove('d-none');
        const fallbackLabel = config.labels?.page || 'Page';
        pages.forEach((page, index) => {
            const col = document.createElement('div');
            col.className = 'col-md-4 mb-2';
            const checkboxId = `subscription-page-${index}`;
            const name = page.name || page.page_name || '';
            const display = page.displayName || page.page_name || page.name || `${fallbackLabel} ${index + 1}`;
            const isChecked = selectedNames.has(name);
            col.innerHTML = `
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input subscription-page-checkbox" id="${checkboxId}"
                        data-page-name="${name}"
                        data-page-display="${page.displayName || page.page_name || ''}" ${isChecked ? 'checked' : ''}>
                    <label class="custom-control-label" for="${checkboxId}">${display}</label>
                </div>
            `;
            elements.pagesList.appendChild(col);
        });
    }

    function updateRecurrenceView() {
        const value = elements.recurrenceSelect?.value || 'daily';
        const isCron = value === 'cron';
        recurrenceFields.forEach((field) => {
            if (!field) return;
            field.classList.toggle('d-none', isCron);
        });
        if (elements.cronWrapper) {
            elements.cronWrapper.classList.toggle('d-none', !isCron);
        }
    }

    function gatherPages() {
        return Array.from(document.querySelectorAll('.subscription-page-checkbox:checked')).map((cb) => ({
            name: cb.getAttribute('data-page-name') || '',
            display_name: cb.getAttribute('data-page-display') || null,
        }));
    }

    function collectPayload() {
        const recurrence = elements.recurrenceSelect?.value || 'daily';
        const isCron = recurrence === 'cron';
        const timeValue = elements.timeInput?.value ?? '';
        const timeParts = parseTimeInput(timeValue);
        const maxSizeValue = elements.maxSizeInput?.value ?? '';

        return {
            name: elements.nameInput?.value?.trim() || '',
            is_enabled: elements.enabledSwitch ? !!elements.enabledSwitch.checked : true,
            format: elements.formatSelect?.value || 'pdf',
            recurrence,
            cron_expression: isCron ? (elements.cronInput?.value?.trim() || '') : null,
            start_date: elements.startInput?.value || '',
            end_date: elements.endInput?.value || null,
            hour: !isCron ? timeParts.hour : null,
            minute: !isCron ? timeParts.minute : null,
            timezone: elements.timezoneInput?.value?.trim() || '',
            report_id: elements.reportSelect?.value || '',
            pages: gatherPages(),
            groups: gatherSelectedIds(elements.groupSelect),
            users: gatherSelectedIds(elements.userSelect),
            mail_subject: elements.subjectInput?.value || '',
            mail_body: elements.bodyInput?.value || '',
            max_attachment_size_mb: maxSizeValue !== '' ? Number(maxSizeValue) : null,
            notify_workspace_admins: elements.notifyAdminsCheckbox ? !!elements.notifyAdminsCheckbox.checked : false,
            notification_users: gatherSelectedIds(elements.notifUsersSelect),
            notification_groups: gatherSelectedIds(elements.notifGroupsSelect),
        };
    }

    function disableSubmit(disabled) {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (!submitBtn) return;
        submitBtn.disabled = disabled;
        submitBtn.classList.toggle('disabled', disabled);
    }

    function showLoadingState() {
        if (elements.pagesCard) elements.pagesCard.classList.remove('d-none');
        if (elements.pagesList) {
            elements.pagesList.innerHTML = `<div class="col-12 text-muted small">${config.messages?.loading || ''}</div>`;
        }
        populateRecipientsFromDirectory(captureCurrentSelections());
    }

    function loadMetadata(reportId, selections = {}) {
        if (!reportId) {
            if (elements.pagesCard) elements.pagesCard.classList.add('d-none');
            if (elements.pagesList) elements.pagesList.innerHTML = '';
            populateRecipientsFromDirectory(selections);
            return Promise.resolve(null);
        }
        if (metadataCache.has(reportId)) {
            const data = metadataCache.get(reportId);
            applyMetadata(data, selections);
            return Promise.resolve(data);
        }

        showLoadingState();

        return fetch(buildMetadataUrl(reportId), {
            headers: { 'Accept': 'application/json' },
        })
            .then((response) => {
                if (!response.ok) throw new Error('metadata');
                return response.json();
            })
            .then((data) => {
                metadataCache.set(reportId, data);
                applyMetadata(data, selections);
                return data;
            })
            .catch((error) => {
                showErrors([config.messages?.metadata_error || 'Metadata error']);
                throw error;
            });
    }

    function applyMetadata(data, selections = {}) {
        if (!data) return;
        const pages = Array.isArray(data.pages) ? data.pages.slice() : [];
        const groups = Array.isArray(data.groups) && data.groups.length
            ? data.groups
            : (directory.groups || []);
        const users = Array.isArray(data.users) && data.users.length
            ? data.users
            : (directory.users || []);
        pages.sort((a, b) => {
            const ao = typeof a.order === 'number' ? a.order : Number.MAX_SAFE_INTEGER;
            const bo = typeof b.order === 'number' ? b.order : Number.MAX_SAFE_INTEGER;
            if (ao !== bo) return ao - bo;
            return (a.displayName || a.page_name || a.name || '').localeCompare(b.displayName || b.page_name || b.name || '');
        });
        populatePages(pages, selections.pageNames || new Set());
        renderRecipientList(elements.groupSelect, groups, selections.groupIds, config.placeholders?.groups_empty);
        renderRecipientList(elements.userSelect, users, selections.userIds, config.placeholders?.users_empty);
        renderRecipientList(elements.notifUsersSelect, users, selections.notificationUserIds, config.placeholders?.notification_users_empty);
        renderRecipientList(elements.notifGroupsSelect, groups, selections.notificationGroupIds, config.placeholders?.notification_groups_empty);
    }

    function resetForm(options = {}) {
        form.reset();
        showErrors([]);
        if (!options.skipMode) {
            setMode('create');
        }
        activeInput = elements.bodyInput || elements.subjectInput || null;
        if (elements.enabledSwitch) elements.enabledSwitch.checked = true;
        if (elements.timezoneInput && elements.timezoneInput.defaultValue) {
            elements.timezoneInput.value = elements.timezoneInput.defaultValue;
        }
        if (elements.subjectInput) elements.subjectInput.value = config.default_subject || '';
        if (elements.bodyInput) elements.bodyInput.value = config.default_body || '';
        if (elements.notifyAdminsCheckbox) elements.notifyAdminsCheckbox.checked = false;
        if (elements.startInput) elements.startInput.value = todayString();
        if (elements.endInput) elements.endInput.value = '';
        if (elements.timeInput) {
            const defaultValue = elements.timeInput.defaultValue || '';
            elements.timeInput.value = defaultValue;
        }
        populateRecipientsFromDirectory();
        syncTextareaToDesign();
        updatePreview();
        if (elements.pagesList) elements.pagesList.innerHTML = '';
        if (elements.pagesCard) elements.pagesCard.classList.add('d-none');
        if (elements.reportSelect) elements.reportSelect.selectedIndex = 0;
        window.$('#report-subscription-tabs a:first').tab('show');
        updateRecurrenceView();
    }

    function loadSubscription(id) {
        resetForm({ skipMode: true });
        setMode('edit', id);

        fetch(buildEditUrl(id), {
            headers: { 'Accept': 'application/json' },
        })
            .then((response) => {
                if (!response.ok) throw new Error('edit');
                return response.json();
            })
            .then((data) => {
                fillFormWithSubscription(data.subscription || {});
                showModal();
            })
            .catch(() => {
                showErrors([config.messages?.save_error || 'Error']);
            });
    }

    function fillFormWithSubscription(subscription) {
        if (elements.nameInput) elements.nameInput.value = subscription.name || '';
        if (elements.enabledSwitch) elements.enabledSwitch.checked = !!subscription.is_enabled;
        if (elements.formatSelect && subscription.format) elements.formatSelect.value = subscription.format;
        if (elements.recurrenceSelect && subscription.recurrence) elements.recurrenceSelect.value = subscription.recurrence;
        updateRecurrenceView();

        if (subscription.recurrence === 'cron') {
            if (elements.cronInput) elements.cronInput.value = subscription.cron_expression || '';
            if (elements.timeInput) elements.timeInput.value = '';
        } else {
            if (elements.cronInput) elements.cronInput.value = '';
            if (elements.timeInput) elements.timeInput.value = formatTimeInput(subscription.hour, subscription.minute);
        }

        if (elements.startInput && subscription.start_date) {
            elements.startInput.value = normalizeDateValue(subscription.start_date);
        }
        if (elements.endInput) {
            elements.endInput.value = normalizeDateValue(subscription.end_date) || '';
        }
        if (elements.timezoneInput) elements.timezoneInput.value = subscription.timezone || elements.timezoneInput.defaultValue || '';
        if (elements.subjectInput) elements.subjectInput.value = subscription.mail_subject || '';
        if (elements.bodyInput) elements.bodyInput.value = subscription.mail_body || '';
        syncTextareaToDesign();
        updatePreview();
        if (elements.maxSizeInput) elements.maxSizeInput.value = subscription.max_attachment_size_mb ?? '';
        if (elements.notifyAdminsCheckbox) elements.notifyAdminsCheckbox.checked = !!subscription.notify_workspace_admins;
        if (elements.reportSelect && subscription.report_id) elements.reportSelect.value = subscription.report_id;

        const selectedPageNames = new Set((subscription.pages || []).map((page) => page.page_name || page.name));
        const selectedGroupIds = (subscription.recipient_groups || []).map((group) => group.id);
        const selectedUserIds = (subscription.recipient_users || []).map((user) => user.id);
        const notificationUserIds = (subscription.notification_links || [])
            .filter((link) => link.notifiable_type === 'user')
            .map((link) => link.notifiable_id);
        const notificationGroupIds = (subscription.notification_links || [])
            .filter((link) => link.notifiable_type === 'group')
            .map((link) => link.notifiable_id);

        loadMetadata(subscription.report_id, {
            pageNames: selectedPageNames,
            groupIds: selectedGroupIds,
            userIds: selectedUserIds,
            notificationUserIds,
            notificationGroupIds,
        });
    }
})();
@endcan
</script>
@endpush
