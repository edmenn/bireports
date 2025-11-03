@php
    $subscriptionSnippetOptions = isset($subscriptionSnippetOptions) && is_array($subscriptionSnippetOptions)
        ? $subscriptionSnippetOptions
        : [
            ['value' => '{{report_name}}', 'label' => __('subscriptions.snippets.report_name')],
            ['value' => '{{date}}', 'label' => __('subscriptions.snippets.date')],
            ['value' => '{{datetime}}', 'label' => __('subscriptions.snippets.datetime')],
            ['value' => '{{subscription_name}}', 'label' => __('subscriptions.snippets.subscription_name')],
        ];

    if (auth()->user()?->can('reports.view') && ! collect($subscriptionSnippetOptions)->contains(fn ($option) => $option['value'] === '{{report_link}}')) {
        $subscriptionSnippetOptions[] = ['value' => '{{report_link}}', 'label' => __('subscriptions.snippets.report_link')];
    }
@endphp

@push('css')
<style>
    .subscription-checklist {
        max-height: 240px;
        overflow-y: auto;
        border: 1px solid #ced4da;
        border-radius: .25rem;
        padding: .75rem;
        background-color: #fff;
    }

    .subscription-checklist .custom-control {
        padding-left: 1.75rem;
    }

    .subscription-checklist .custom-control-label {
        cursor: pointer;
    }

    .subscription-checklist::-webkit-scrollbar {
        width: 6px;
    }

    .subscription-checklist::-webkit-scrollbar-thumb {
        background-color: rgba(0, 0, 0, 0.2);
        border-radius: 3px;
    }

.subscription-email-toolbar {
    border: 1px solid #ced4da;
    border-bottom: none;
    border-top-left-radius: .25rem;
    border-top-right-radius: .25rem;
        padding: .5rem;
        background-color: #f8f9fa;
    }

    .subscription-email-toolbar .btn {
        padding: .25rem .5rem;
    }

.subscription-email-design {
    border: 1px solid #ced4da;
    border-top: none;
    border-bottom-left-radius: .25rem;
    border-bottom-right-radius: .25rem;
    min-height: 260px;
    padding: .75rem;
    background-color: #fff;
    overflow-y: auto;
}

#report-subscription-modal .modal-content {
    border: 0;
    border-radius: .75rem;
    overflow: hidden;
    box-shadow: 0 .85rem 2.2rem rgba(15, 34, 58, .18);
}

#report-subscription-modal .subscription-modal-header {
    background: linear-gradient(180deg, #f6f9ff 0%, #eef3fb 100%);
    border-bottom: 1px solid #dee6f5;
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

#report-subscription-modal .subscription-modal-title {
    display: inline-flex;
    align-items: center;
    gap: .75rem;
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
    color: #1f2d3d;
}

#report-subscription-modal .subscription-modal-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.5rem;
    height: 2.5rem;
    border-radius: .75rem;
    background-color: rgba(13, 110, 253, .12);
    color: #0d6efd;
    font-size: 1.1rem;
}

#report-subscription-modal .subscription-modal-close {
    border: none;
    background: #ffffff;
    border-radius: 999px;
    width: 2.1rem;
    height: 2.1rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #6c7a91;
    box-shadow: 0 .2rem .55rem rgba(15, 34, 58, .18);
    transition: all .2s ease-in-out;
}

#report-subscription-modal .subscription-modal-close:hover,
#report-subscription-modal .subscription-modal-close:focus {
    color: #0d6efd;
    outline: none;
}
</style>
@endpush

<div class="modal fade" id="report-subscription-modal" tabindex="-1" role="dialog" aria-labelledby="report-subscription-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <form id="report-subscription-form" method="post" action="{{ route('subscriptions.store') }}">
                <div class="modal-header subscription-modal-header">
                    <div class="subscription-modal-title">
                        <span class="subscription-modal-icon"><i class="fas fa-envelope-open-text"></i></span>
                        <span class="subscription-modal-title-text" id="report-subscription-modal-title">{{ __('subscriptions.create.title') }}</span>
                    </div>
                    <button type="button" class="subscription-modal-close" data-dismiss="modal" aria-label="{{ __('subscriptions.actions.close') }}">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger d-none" id="report-subscription-errors">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <strong>{{ __('subscriptions.validation.errors_title') }}</strong>
                        </div>
                        <ul class="mt-2 mb-0 small"></ul>
                    </div>

                    <ul class="nav nav-tabs" id="report-subscription-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="subscription-tab-config" data-toggle="tab" href="#subscription-panel-config" role="tab" aria-controls="subscription-panel-config" aria-selected="true">
                                {{ __('subscriptions.tabs.configuration') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="subscription-tab-report" data-toggle="tab" href="#subscription-panel-report" role="tab" aria-controls="subscription-panel-report" aria-selected="false">
                                {{ __('subscriptions.tabs.report') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="subscription-tab-groups" data-toggle="tab" href="#subscription-panel-groups" role="tab" aria-controls="subscription-panel-groups" aria-selected="false">
                                {{ __('subscriptions.tabs.groups') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="subscription-tab-users" data-toggle="tab" href="#subscription-panel-users" role="tab" aria-controls="subscription-panel-users" aria-selected="false">
                                {{ __('subscriptions.tabs.users') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="subscription-tab-email" data-toggle="tab" href="#subscription-panel-email" role="tab" aria-controls="subscription-panel-email" aria-selected="false">
                                {{ __('subscriptions.tabs.email') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="subscription-tab-notifications" data-toggle="tab" href="#subscription-panel-notifications" role="tab" aria-controls="subscription-panel-notifications" aria-selected="false">
                                {{ __('subscriptions.tabs.notifications') }}
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content pt-3" id="report-subscription-tab-content">
                        <div class="tab-pane fade show active" id="subscription-panel-config" role="tabpanel" aria-labelledby="subscription-tab-config">
                            <div class="form-row">
                                <div class="form-group col-md-8">
                                    <label for="subscription-name">{{ __('subscriptions.fields.name') }}</label>
                                    <input type="text" class="form-control" id="subscription-name" name="name" maxlength="150" required>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="subscription-enabled">{{ __('subscriptions.fields.is_enabled') }}</label>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="subscription-enabled" name="is_enabled" checked>
                                        <label class="custom-control-label" for="subscription-enabled">{{ __('subscriptions.fields.enabled_label') }}</label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label for="subscription-format">{{ __('subscriptions.fields.format') }}</label>
                                    <select class="custom-select" id="subscription-format" name="format">
                                        <option value="pdf">PDF</option>
                                        <option value="ppt">PPT</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="subscription-recurrence">{{ __('subscriptions.fields.recurrence') }}</label>
                                    <select class="custom-select" id="subscription-recurrence" name="recurrence">
                                        <option value="once">{{ __('subscriptions.recurrence.once') }}</option>
                                        <option value="daily">{{ __('subscriptions.recurrence.daily') }}</option>
                                        <option value="weekly">{{ __('subscriptions.recurrence.weekly') }}</option>
                                        <option value="monthly">{{ __('subscriptions.recurrence.monthly') }}</option>
                                    </select>
                                </div>
                            </div>
                            <input type="hidden" id="subscription-timezone" name="timezone" value="{{ config('app.timezone') }}">

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="subscription-start-date">{{ __('subscriptions.fields.start_date') }}</label>
                                    <input type="date" class="form-control" id="subscription-start-date" name="start_date" required>
                                    <small class="form-text text-muted">{{ __('subscriptions.hints.start_date') }}</small>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="subscription-end-date">{{ __('subscriptions.fields.end_date') }}</label>
                                    <input type="date" class="form-control" id="subscription-end-date" name="end_date">
                                    <small class="form-text text-muted">{{ __('subscriptions.hints.end_date') }}</small>
                                </div>
                            </div>

                            <div class="form-row recurrence-dependent-row">
                                <div class="form-group col-md-4 col-lg-3 recurrence-dependent">
                                    <label for="subscription-time">{{ __('subscriptions.fields.hour') }}</label>
                                    <input type="time" class="form-control" id="subscription-time" name="time" value="08:00" min="00:00" max="23:59">
                                </div>
                            </div>

                            <div id="subscription-weekly-options" class="schedule-options d-none">
                                <label class="d-block font-weight-semibold">{{ __('subscriptions.schedule.weekly.label') }}</label>
                                <div class="form-row">
                                    <div class="form-group col-md-8">
                                        <span class="d-block mb-2">{{ __('subscriptions.schedule.weekly.days') }}</span>
                                        <div class="d-flex flex-wrap">
                                            @foreach(['mon','tue','wed','thu','fri','sat','sun'] as $weekday)
                                                <div class="custom-control custom-checkbox mr-3 mb-2">
                                                    <input type="checkbox" class="custom-control-input subscription-weekday" id="subscription-weekday-{{ $weekday }}" data-day="{{ $weekday }}">
                                                    <label class="custom-control-label" for="subscription-weekday-{{ $weekday }}">{{ __('subscriptions.schedule.weekly.day_labels.' . $weekday) }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="subscription-weekly-interval">{{ __('subscriptions.schedule.weekly.interval') }}</label>
                                        <input type="number" class="form-control" id="subscription-weekly-interval" min="1" max="6" value="1">
                                        <small class="form-text text-muted">{{ __('subscriptions.schedule.weekly.interval_hint') }}</small>
                                    </div>
                                </div>
                            </div>

                            <div id="subscription-monthly-options" class="schedule-options d-none">
                                <label class="d-block font-weight-semibold">{{ __('subscriptions.schedule.monthly.label') }}</label>
                                <div class="custom-control custom-radio mb-2">
                                    <input type="radio" class="custom-control-input" name="subscription-monthly-mode" id="subscription-monthly-mode-day" value="day" checked>
                                    <label class="custom-control-label" for="subscription-monthly-mode-day">
                                        {{ __('subscriptions.schedule.monthly.on_day') }}
                                    </label>
                                    <div class="mt-2 ml-4">
                                        <input type="number" class="form-control w-auto d-inline-block" id="subscription-monthly-day" min="1" max="31" value="1">
                                        <small class="form-text text-muted">{{ __('subscriptions.schedule.monthly.day_hint') }}</small>
                                    </div>
                                </div>
                                <div class="custom-control custom-radio">
                                    <input type="radio" class="custom-control-input" name="subscription-monthly-mode" id="subscription-monthly-mode-last" value="last">
                                    <label class="custom-control-label" for="subscription-monthly-mode-last">
                                        {{ __('subscriptions.schedule.monthly.on_last_day') }}
                                    </label>
                                </div>
                            </div>

                            <div class="form-group col-md-4 px-0">
                                <label for="subscription-max-size">{{ __('subscriptions.fields.max_attachment_size_mb') }}</label>
                                <input type="number" class="form-control" id="subscription-max-size" name="max_attachment_size_mb" min="1" max="100" step="1" placeholder="10">
                                <small class="form-text text-muted">{{ __('subscriptions.hints.max_attachment_size_mb') }}</small>
                            </div>

                            <div class="form-group mt-3">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="subscription-capacity-warmup" name="ensure_capacity_active" value="1">
                                    <label class="custom-control-label" for="subscription-capacity-warmup">
                                        {{ __('subscriptions.fields.ensure_capacity_active') }}
                                    </label>
                                </div>
                                <small class="form-text text-muted">{{ __('subscriptions.hints.ensure_capacity_active') }}</small>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="subscription-panel-report" role="tabpanel" aria-labelledby="subscription-tab-report">
                            <div class="form-group">
                                <label for="subscription-report">{{ __('subscriptions.fields.report_id') }}</label>
                                <select class="custom-select" id="subscription-report" name="report_id" required>
                                    <option value="">{{ __('subscriptions.placeholders.report') }}</option>
                                    @isset($reports)
                                        @foreach($reports as $report)
                                            <option value="{{ $report->id }}" data-workspace="{{ $report->workspace_id }}">{{ $report->name }}</option>
                                        @endforeach
                                    @endisset
                                </select>
                                <small class="form-text text-muted">{{ __('subscriptions.hints.report_selection') }}</small>
                            </div>

                            <div class="card border shadow-sm d-none" id="subscription-pages-card">
                                <div class="card-header">
                                    <strong>{{ __('subscriptions.sections.pages') }}</strong>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small mb-2">{{ __('subscriptions.hints.pages') }}</p>
                                    <div id="subscription-pages-list" class="row"></div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="subscription-panel-groups" role="tabpanel" aria-labelledby="subscription-tab-groups">
                            <div class="form-group">
                                <label for="subscription-groups">{{ __('subscriptions.fields.groups') }}</label>
                                <div class="subscription-checklist" id="subscription-groups" data-recipient-type="groups"></div>
                                <small class="form-text text-muted">{{ __('subscriptions.hints.groups') }}</small>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="subscription-panel-users" role="tabpanel" aria-labelledby="subscription-tab-users">
                            <div class="form-group">
                                <label for="subscription-users">{{ __('subscriptions.fields.users') }}</label>
                                <div class="subscription-checklist" id="subscription-users" data-recipient-type="users"></div>
                                <small class="form-text text-muted">{{ __('subscriptions.hints.users') }}</small>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="subscription-panel-email" role="tabpanel" aria-labelledby="subscription-tab-email">
                            <div class="form-group">
                                <label for="subscription-mail-subject">{{ __('subscriptions.fields.mail_subject') }}</label>
                                <input type="text" class="form-control" id="subscription-mail-subject" name="mail_subject" placeholder="{{ __('subscriptions.placeholders.subject') }}">
                                <small class="form-text text-muted">
                                    {{ __('subscriptions.hints.mail_subject') }}
                                </small>
                            </div>
                            <div class="form-group">
                                <label>{{ __('subscriptions.fields.mail_body') }}</label>
                                @if(!empty($subscriptionSnippetOptions))
                                    <div class="mb-2">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fas fa-code mr-1"></i> {{ __('subscriptions.actions.insert_snippet') }}
                                            </button>
                                            <div class="dropdown-menu" id="subscription-snippets-menu">
                                                @foreach($subscriptionSnippetOptions as $snippet)
                                                    <button class="dropdown-item" type="button" data-snippet-value="{{ $snippet['value'] }}">
                                                        {{ $snippet['label'] }}
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <ul class="nav nav-pills mb-3" id="subscription-email-editor-nav" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="subscription-email-tab-html" data-toggle="pill" href="#subscription-email-editor-html" role="tab" aria-controls="subscription-email-editor-html" aria-selected="true">
                                            {{ __('subscriptions.email_editor.html') }}
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="subscription-email-tab-design" data-toggle="pill" href="#subscription-email-editor-design" role="tab" aria-controls="subscription-email-editor-design" aria-selected="false">
                                            {{ __('subscriptions.email_editor.design') }}
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="subscription-email-tab-preview" data-toggle="pill" href="#subscription-email-editor-preview" role="tab" aria-controls="subscription-email-editor-preview" aria-selected="false">
                                            {{ __('subscriptions.email_editor.preview') }}
                                        </a>
                                    </li>
                                </ul>
                                <div class="tab-content" id="subscription-email-editor-content">
                                    <div class="tab-pane fade show active" id="subscription-email-editor-html" role="tabpanel" aria-labelledby="subscription-email-tab-html">
                                        <textarea class="form-control" id="subscription-mail-body" name="mail_body" rows="8" placeholder="{{ __('subscriptions.placeholders.body') }}"></textarea>
                                        <small class="form-text text-muted">
                                            {{ __('subscriptions.hints.mail_body') }}
                                        </small>
                                    </div>
                                    <div class="tab-pane fade" id="subscription-email-editor-design" role="tabpanel" aria-labelledby="subscription-email-tab-design">
                                        <div class="subscription-email-toolbar btn-toolbar mb-0" role="toolbar" aria-label="{{ __('subscriptions.email_editor.design_toolbar') }}" id="subscription-email-toolbar">
                                            <div class="btn-group mr-2 mb-2" role="group">
                                                <button type="button" class="btn btn-outline-secondary" data-design-command="bold"><i class="fas fa-bold"></i></button>
                                                <button type="button" class="btn btn-outline-secondary" data-design-command="italic"><i class="fas fa-italic"></i></button>
                                                <button type="button" class="btn btn-outline-secondary" data-design-command="underline"><i class="fas fa-underline"></i></button>
                                                <button type="button" class="btn btn-outline-secondary" data-design-command="strikeThrough"><i class="fas fa-strikethrough"></i></button>
                                            </div>
                                            <div class="btn-group mr-2 mb-2" role="group">
                                                <button type="button" class="btn btn-outline-secondary" data-design-command="justifyLeft"><i class="fas fa-align-left"></i></button>
                                                <button type="button" class="btn btn-outline-secondary" data-design-command="justifyCenter"><i class="fas fa-align-center"></i></button>
                                                <button type="button" class="btn btn-outline-secondary" data-design-command="justifyRight"><i class="fas fa-align-right"></i></button>
                                                <button type="button" class="btn btn-outline-secondary" data-design-command="justifyFull"><i class="fas fa-align-justify"></i></button>
                                            </div>
                                            <div class="btn-group mr-2 mb-2" role="group">
                                                <button type="button" class="btn btn-outline-secondary" data-design-command="insertUnorderedList"><i class="fas fa-list-ul"></i></button>
                                                <button type="button" class="btn btn-outline-secondary" data-design-command="insertOrderedList"><i class="fas fa-list-ol"></i></button>
                                            </div>
                                            <div class="btn-group mb-2" role="group">
                                                <select class="custom-select custom-select-sm" id="subscription-design-fontsize" aria-label="{{ __('subscriptions.email_editor.font_size') }}">
                                                    <option value="">{{ __('subscriptions.email_editor.font_size') }}</option>
                                                    <option value="1">10px</option>
                                                    <option value="2">13px</option>
                                                    <option value="3">16px</option>
                                                    <option value="4">18px</option>
                                                    <option value="5">24px</option>
                                                    <option value="6">32px</option>
                                                    <option value="7">48px</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="subscription-email-design" id="subscription-design-editor" contenteditable="true" spellcheck="false"></div>
                                    </div>
                                    <div class="tab-pane fade" id="subscription-email-editor-preview" role="tabpanel" aria-labelledby="subscription-email-tab-preview">
                                        <div class="border rounded position-relative">
                                            <iframe id="subscription-mail-preview-frame" title="{{ __('subscriptions.email_editor.preview') }}" class="w-100 border-0" style="min-height: 280px; border-top-left-radius: .25rem; border-top-right-radius: .25rem;"></iframe>
                                        </div>
                                        <small class="form-text text-muted mt-2">
                                            {{ __('subscriptions.email_editor.preview_hint') }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="subscription-panel-notifications" role="tabpanel" aria-labelledby="subscription-tab-notifications">
                            <div class="custom-control custom-checkbox mb-3">
                                <input type="checkbox" class="custom-control-input" id="subscription-notify-admins" name="notify_workspace_admins" value="1">
                                <label class="custom-control-label" for="subscription-notify-admins">
                                    {{ __('subscriptions.fields.notify_workspace_admins') }}
                                </label>
                                <small class="form-text text-muted">{{ __('subscriptions.hints.notify_workspace_admins') }}</small>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="subscription-notification-users">{{ __('subscriptions.fields.notification_users') }}</label>
                                    <div class="subscription-checklist" id="subscription-notification-users" data-recipient-type="notification-users"></div>
                                    <small class="form-text text-muted">{{ __('subscriptions.hints.notification_users') }}</small>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="subscription-notification-groups">{{ __('subscriptions.fields.notification_groups') }}</label>
                                    <div class="subscription-checklist" id="subscription-notification-groups" data-recipient-type="notification-groups"></div>
                                    <small class="form-text text-muted">{{ __('subscriptions.hints.notification_groups') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> {{ __('subscriptions.actions.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> {{ __('subscriptions.actions.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
