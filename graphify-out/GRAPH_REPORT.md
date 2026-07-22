# Graph Report - bireports  (2026-07-22)

## Corpus Check
- 539 files · ~559,404 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 2578 nodes · 5233 edges · 438 communities (403 shown, 35 thin omitted)
- Extraction: 90% EXTRACTED · 10% INFERRED · 0% AMBIGUOUS · INFERRED: 502 edges (avg confidence: 0.67)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `62afe3a7`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Community 0
- Community 1
- Community 2
- Community 3
- Community 4
- Community 5
- Community 6
- Community 7
- Community 8
- Community 9
- Community 10
- Community 11
- Community 12
- Community 13
- Community 14
- Community 15
- Community 16
- Community 17
- Community 18
- Community 19
- Community 20
- Community 21
- Community 22
- Community 23
- Community 24
- Community 25
- Community 26
- Community 27
- Community 28
- Community 29
- Community 30
- Community 31
- Community 32
- Community 33
- Community 34
- Community 35
- Community 36
- Community 37
- Community 38
- Community 39
- Community 40
- Community 41
- Community 42
- Community 43
- Community 44
- Community 45
- Community 46
- Community 47
- Community 48
- Community 49
- Community 50
- Community 51
- Community 52
- Community 53
- Community 54
- Community 55
- Community 56
- Community 57
- Community 58
- Community 59
- Community 60
- Community 61
- Community 62
- Community 63
- Community 64
- Community 65
- Community 66
- Community 68
- Community 69
- Community 70
- AuditChangesController.php
- Community 72
- RoleController
- Community 74
- Community 75
- Community 76
- Community 77
- Community 78
- Community 79
- Community 80
- Community 81
- Community 82
- Community 83
- Community 84
- Community 85
- Community 86
- Community 87
- ReportAttachmentCompressor
- Community 90
- Community 91
- Community 92
- Community 93
- Community 94
- Community 95
- Community 96
- Community 98
- autoload-dev
- Community 158
- Community 160
- Community 161
- Community 162
- Community 163
- Community 164
- Community 165
- Community 166
- Community 167
- Community 168
- Community 171
- Community 172
- Community 173
- Community 174
- Community 175
- Community 176
- Community 177
- Community 431
- extra

## God Nodes (most connected - your core abstractions)
1. `User` - 95 edges
2. `ReportSubscription` - 73 edges
3. `PowerBiCapacityService` - 58 edges
4. `Controller` - 56 edges
5. `Report` - 51 edges
6. `Capacity` - 49 edges
7. `Configuration` - 44 edges
8. `PowerBiService` - 44 edges
9. `Application` - 32 edges
10. `ReportSubscriptionScheduler` - 31 edges

## Surprising Connections (you probably didn't know these)
- `sync_lang_assets()` --calls--> `sync-lang-assets.sh script`  [EXTRACTED]
  install.sh → scripts/sync-lang-assets.sh
- `t()` --indirect_call--> `U()`  [INFERRED]
  public/vendor/bootstrap/js/bootstrap.min.js → public/vendor/jquery/jquery.min.js
- `resolve()` --indirect_call--> `e()`  [INFERRED]
  public/vendor/jquery/jquery.js → public/vendor/jquery/jquery.min.js
- `se()` --indirect_call--> `re()`  [INFERRED]
  public/vendor/jquery/jquery.min.js → public/vendor/popper/popper.min.js
- `x()` --indirect_call--> `O()`  [INFERRED]
  public/vendor/overlayScrollbars/js/OverlayScrollbars.min.js → public/vendor/jquery/jquery.min.js

## Import Cycles
- None detected.

## Communities (438 total, 35 thin omitted)

### Community 0 - "Community 0"
Cohesion: 0.06
Nodes (26): DispatchReportSubscriptions, EvaluateCapacities, CheckCapacityInactivityJob, EvaluateCapacitySchedulesJob, ProcessReportSubscriptionRun, SubscriptionDeliveryMail, ReportSubscription, ReportSubscriptionRun (+18 more)

### Community 1 - "Community 1"
Cohesion: 0.06
Nodes (68): applyStyle(), applyStyleOnLoad(), arrow(), attachToScrollParents(), BEHAVIORS, clockwise(), computeAutoPlacement(), computeStyle() (+60 more)

### Community 2 - "Community 2"
Cohesion: 0.06
Nodes (59): a(), at(), bt(), Ct(), Dt(), et(), Ft(), G() (+51 more)

### Community 3 - "Community 3"
Cohesion: 0.07
Nodes (65): applyStyle(), applyStyleOnLoad(), arrow(), attachToScrollParents(), clockwise(), computeAutoPlacement(), computeStyle(), destroy() (+57 more)

### Community 4 - "Community 4"
Cohesion: 0.07
Nodes (65): applyStyle(), applyStyleOnLoad(), arrow(), attachToScrollParents(), clockwise(), computeAutoPlacement(), computeStyle(), destroy() (+57 more)

### Community 5 - "Community 5"
Cohesion: 0.24
Nodes (3): DatasetsSchedulerTick, PowerBiService, GuzzleHttp\Exception\ClientException

### Community 6 - "Community 6"
Cohesion: 0.10
Nodes (9): ProfileController, UsersController, UsersSyncController, Authenticate, EnsureUserIsNotBlocked, Illuminate\Auth\Middleware\Authenticate, Illuminate\Http\RedirectResponse, Illuminate\Http\Request (+1 more)

### Community 7 - "Community 7"
Cohesion: 0.08
Nodes (34): A(), at(), b(), be(), ce(), e(), Ee(), fe() (+26 more)

### Community 8 - "Community 8"
Cohesion: 0.08
Nodes (8): CapacityActivityController, Carbon, CapacityController, AutoCapacityActivity, Carbon, Capacity, Carbon, PowerBiCapacityService

### Community 9 - "Community 9"
Cohesion: 0.05
Nodes (26): AuditAnalyticsController, Carbon, LogSpatiePermissionEvents, Audit, CapacityActivity, CapacityAudit, CapacitySchedule, Carbon (+18 more)

### Community 10 - "Community 10"
Cohesion: 0.10
Nodes (41): a(), b(), c(), ce(), d(), E(), f(), fe() (+33 more)

### Community 11 - "Community 11"
Cohesion: 0.09
Nodes (43): attachToScrollParents(), computeAutoPlacement(), find(), findCommonOffsetParent(), findIndex(), getArea(), getBordersSize(), getBoundaries() (+35 more)

### Community 12 - "Community 12"
Cohesion: 0.10
Nodes (42): attachToScrollParents(), computeAutoPlacement(), find(), findCommonOffsetParent(), findIndex(), getArea(), getBordersSize(), getBoundaries() (+34 more)

### Community 13 - "Community 13"
Cohesion: 0.10
Nodes (42): attachToScrollParents(), computeAutoPlacement(), find(), findCommonOffsetParent(), findIndex(), getArea(), getBordersSize(), getBoundaries() (+34 more)

### Community 14 - "Community 14"
Cohesion: 0.10
Nodes (18): ConfirmablePasswordController, EmailVerificationNotificationController, EmailVerificationPromptController, NewPasswordController, PasswordController, PasswordResetLinkController, RegisteredUserController, VerifyEmailController (+10 more)

### Community 15 - "Community 15"
Cohesion: 0.11
Nodes (42): a(), ae(), b(), be(), c(), ce(), d(), de() (+34 more)

### Community 16 - "Community 16"
Cohesion: 0.10
Nodes (8): AdminController, EntityAuditController, AuditController, DashboardController, FoldersController, UserPortalController, Folder, Illuminate\Contracts\View\View

### Community 17 - "Community 17"
Cohesion: 0.08
Nodes (12): CheckInactivity, ImportCapacities, ListCapacities, PauseCapacity, PowerBiSetCredentials, ResetAudit, ResumeCapacity, SyncEntraUsers (+4 more)

### Community 18 - "Community 18"
Cohesion: 0.06
Nodes (10): computeStyleTests(), dataAttr(), finalPropName(), getData(), NOTE: This can be skipped if there are no unmatched elements (i.e., `matchedCoun, TODO: Now that all calls to _data and _removeData have been replaced, TODO: identify versions, TODO: identify versions (+2 more)

### Community 19 - "Community 19"
Cohesion: 0.07
Nodes (13): allowedAttribute(), _createClass(), _defineProperties(), getSpecialTransitionEndEvent(), _inheritsLoose(), NOTE: 1 DOM access here, NOTE: 1 DOM access here, NOTE: 1 DOM access here (+5 more)

### Community 20 - "Community 20"
Cohesion: 0.12
Nodes (7): GroupsController, ForbidExternalPasswordChange, Group, Illuminate\Notifications\Notifiable, Illuminate\Support\Facades\Validator, Spatie\Permission\Traits\HasRoles, Symfony\Component\HttpFoundation\Response

### Community 21 - "Community 21"
Cohesion: 0.06
Nodes (12): Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, Illuminate\Support\Facades\Notification, AuthenticationTest, EmailVerificationTest, PasswordConfirmationTest, PasswordResetTest, PasswordUpdateTest (+4 more)

### Community 22 - "Community 22"
Cohesion: 0.25
Nodes (28): bring_up_services(), check_env_permissions(), clear_caches(), compose(), composer_install(), detect_node_runner(), ensure_app_key(), ensure_env_file() (+20 more)

### Community 23 - "Community 23"
Cohesion: 0.07
Nodes (27): alpinejs, autoprefixer, axios, concurrently, laravel-vite-plugin, devDependencies, alpinejs, autoprefixer (+19 more)

### Community 24 - "Community 24"
Cohesion: 0.07
Nodes (18): RedirectIfAuthenticated, SetAdminlteTheme, SetLocale, TrackAudit, MailjetTransport, AppServiceProvider, MenuServiceProvider, AuditService (+10 more)

### Community 25 - "Community 25"
Cohesion: 0.25
Nodes (23): a(), b(), c(), d(), e(), f(), g(), h() (+15 more)

### Community 26 - "Community 26"
Cohesion: 0.25
Nodes (26): A(), b(), c(), d(), e(), f(), g(), h() (+18 more)

### Community 27 - "Community 27"
Cohesion: 0.13
Nodes (15): animate(), each(), extend(), FakejQuery(), inArray(), isArrayLike(), isEmptyObject(), isFunction() (+7 more)

### Community 28 - "Community 28"
Cohesion: 0.30
Nodes (24): a(), b(), c(), d(), G(), h(), i(), j() (+16 more)

### Community 29 - "Community 29"
Cohesion: 0.29
Nodes (24): a(), b(), c(), d(), G(), h(), i(), j() (+16 more)

### Community 30 - "Community 30"
Cohesion: 0.12
Nodes (3): User, ReportSubscriptionPolicy, Illuminate\Foundation\Auth\User

### Community 31 - "Community 31"
Cohesion: 0.10
Nodes (8): allowedAttribute(), _createClass(), _defineProperties(), getSpecialTransitionEndEvent(), _inheritsLoose(), sanitizeHtml(), _setPrototypeOf(), setTransitionEndSupport()

### Community 32 - "Community 32"
Cohesion: 0.20
Nodes (4): ReportSubscriptionController, StoreReportSubscriptionRequest, UpdateReportSubscriptionRequest, Illuminate\Http\JsonResponse

### Community 33 - "Community 33"
Cohesion: 0.11
Nodes (5): AuditController, ReportsController, ReportViewerController, Report, ReportAccessService

### Community 34 - "Community 34"
Cohesion: 0.12
Nodes (4): DatasetsController, PowerBiImportController, WorkspacesController, Workspace

### Community 36 - "Community 36"
Cohesion: 0.12
Nodes (5): ApplicationRequest, ProfileUpdateRequest, StoreCompanyRequest, UpdateConfigurationRequest, Illuminate\Foundation\Http\FormRequest

### Community 38 - "Community 38"
Cohesion: 0.28
Nodes (3): ApplicationsController, Application, Illuminate\Database\DatabaseManager

### Community 39 - "Community 39"
Cohesion: 0.21
Nodes (3): ApplicationSectionsController, ApplicationSectionRequest, ApplicationSection

### Community 40 - "Community 40"
Cohesion: 0.21
Nodes (4): Handler, MicrosoftController, Illuminate\Foundation\Exceptions\Handler, Throwable

### Community 41 - "Community 41"
Cohesion: 0.29
Nodes (4): Kernel, DateTimeZone, Illuminate\Console\Scheduling\Schedule, Illuminate\Foundation\Console\Kernel

### Community 42 - "Community 42"
Cohesion: 0.16
Nodes (14): buildFragment(), buildParams(), cloneCopyEvent(), disableScript(), DOMEval(), domManip(), getAll(), isArrayLike() (+6 more)

### Community 44 - "Community 44"
Cohesion: 0.21
Nodes (3): DatasetsPrewarm, PowerBiDoctor, GuzzleHttp\Client

### Community 45 - "Community 45"
Cohesion: 0.14
Nodes (14): scripts, dev, post-autoload-dump, post-root-package-install, post-update-cmd, test, Composer\\Config::disableProcessTimeout, Illuminate\\Foundation\\ComposerScripts::postAutoloadDump (+6 more)

### Community 46 - "Community 46"
Cohesion: 0.20
Nodes (5): ConfigurationSeeder, DatabaseSeeder, PermissionsSeeder, RolesAndPermissionsSeeder, Illuminate\Database\Seeder

### Community 47 - "Community 47"
Cohesion: 0.36
Nodes (13): check_env_permissions(), ensure_app_key(), ensure_storage_dirs(), ensure_zip_extension(), err(), log(), main(), ok() (+5 more)

### Community 49 - "Community 49"
Cohesion: 0.09
Nodes (22): (Add testing instructions here), AllowOverride All, Clone repository, Commit and push, Configure your database and PowerBI settings in .env, Configure your environment (.env file), Create a new branch, Database (+14 more)

### Community 50 - "Community 50"
Cohesion: 0.20
Nodes (12): addCombinator(), condense(), createPositionalPseudo(), elementMatcher(), markFunction(), matcherFromGroupMatchers(), matcherFromTokens(), multipleContexts() (+4 more)

### Community 52 - "Community 52"
Cohesion: 0.18
Nodes (10): autoload-dev, psr-4, description, license, minimum-stability, name, prefer-stable, Tests\\ (+2 more)

### Community 53 - "Community 53"
Cohesion: 0.24
Nodes (13): applyStyleOnLoad(), attachToScrollParents(), computeAutoPlacement(), getArea(), getBoundaries(), getFixedPositionOffsetParent(), getParentNode(), getReferenceNode() (+5 more)

### Community 54 - "Community 54"
Cohesion: 0.19
Nodes (12): clockwise(), enableEventListeners(), findIndex(), flip(), getOppositePlacement(), getOppositeVariation(), getPopperOffsets(), inner() (+4 more)

### Community 55 - "Community 55"
Cohesion: 0.15
Nodes (14): adoptValue(), ajaxConvert(), ajaxHandleResponses(), Animation(), camelCase(), createFxNow(), createTween(), defaultPrefilter() (+6 more)

### Community 57 - "Community 57"
Cohesion: 0.33
Nodes (7): applyStyle(), isNumeric(), offset(), parseOffset(), setAttributes(), setStyles(), toValue()

### Community 58 - "Community 58"
Cohesion: 0.36
Nodes (10): getBordersSize(), getBoundingClientRect(), getClientRect(), getOffsetRectRelativeToArbitraryNode(), getScroll(), getSize(), getViewportOffsetRectRelativeToArtbitraryNode(), getWindowSizes() (+2 more)

### Community 59 - "Community 59"
Cohesion: 0.22
Nodes (8): adminlte::partials.cwrapper.cwrapper-default, adminlte::partials.cwrapper.cwrapper-iframe, adminlte::partials.footer.footer, adminlte::partials.navbar.navbar, adminlte::partials.navbar.navbar-layout-topnav, adminlte::partials.sidebar.left-sidebar, adminlte::partials.sidebar.right-sidebar, adminlte::partials.common.preloader

### Community 62 - "Community 62"
Cohesion: 0.19
Nodes (6): AttachmentTooLargeException, StampEntraOnFirstLogin, EventServiceProvider, Illuminate\Auth\Events\Login, Illuminate\Foundation\Support\Providers\EventServiceProvider, RuntimeException

### Community 63 - "Community 63"
Cohesion: 0.22
Nodes (8): audit.partials.dashboard, audit.partials.reports_inactive, audit.partials.reports_table, audit.partials.user_profile, audit.partials.users_inactive, audit.partials.users_table, portal.partials.base-styles, portal.partials.home-card-styles

### Community 64 - "Community 64"
Cohesion: 0.22
Nodes (9): require, dragonmantank/cron-expression, guzzlehttp/guzzle, jeroennoten/laravel-adminlte, laravel/framework, laravel/tinker, php, phpoffice/phpspreadsheet (+1 more)

### Community 65 - "Community 65"
Cohesion: 0.22
Nodes (9): require-dev, fakerphp/faker, laravel/breeze, laravel/pail, laravel/pint, laravel/sail, mockery/mockery, nunomaduro/collision (+1 more)

### Community 66 - "Community 66"
Cohesion: 0.32
Nodes (8): computeStyle(), findCommonOffsetParent(), getOffsetParent(), getRoot(), getRoundedOffsets(), getSupportedPropertyName(), isOffsetContainer(), preventOverflow()

### Community 69 - "Community 69"
Cohesion: 0.38
Nodes (3): UserFactory, Illuminate\Database\Eloquent\Factories\Factory, static

### Community 70 - "Community 70"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 71 - "AuditChangesController.php"
Cohesion: 0.23
Nodes (3): AuditChangesController, ConfigAuditController, ConfigAudit

### Community 72 - "Community 72"
Cohesion: 0.33
Nodes (5): adminlte::partials.sidebar.menu-item-header, adminlte::partials.sidebar.menu-item-link, adminlte::partials.sidebar.menu-item-search-form, adminlte::partials.sidebar.menu-item-search-menu, adminlte::partials.sidebar.menu-item-treeview-menu

### Community 74 - "Community 74"
Cohesion: 0.40
Nodes (5): destroy(), disableEventListeners(), getWindow(), isModifierEnabled(), removeEventListeners()

### Community 75 - "Community 75"
Cohesion: 0.33
Nodes (5): portal.partials.base-styles, portal.partials.fav-toggle-script, portal.partials.home-card-styles, portal.partials.report-card, portal.partials.report-card-styles

### Community 76 - "Community 76"
Cohesion: 0.33
Nodes (5): portal.partials.base-styles, portal.partials.fav-toggle-script, portal.partials.home-card-styles, portal.partials.report-card, portal.partials.report-card-styles

### Community 77 - "Community 77"
Cohesion: 0.33
Nodes (5): adminlte::partials.common.brand-logo-xl, adminlte::partials.common.brand-logo-xs, adminlte::partials.navbar.menu-item-dropdown-user-menu, adminlte::partials.navbar.menu-item-logout-link, adminlte::partials.navbar.menu-item-right-sidebar-toggler

### Community 78 - "Community 78"
Cohesion: 0.50
Nodes (5): arrow(), find(), getOuterSizes(), hide(), isModifierRequired()

### Community 79 - "Community 79"
Cohesion: 0.60
Nodes (5): err(), log(), ok(), verify.sh script, warn()

### Community 80 - "Community 80"
Cohesion: 0.40
Nodes (4): adminlte::partials.navbar.menu-item-dropdown-menu, adminlte::partials.navbar.menu-item-fullscreen-widget, adminlte::partials.navbar.menu-item-link, adminlte::partials.navbar.menu-item-search-form

### Community 81 - "Community 81"
Cohesion: 0.40
Nodes (4): adminlte::partials.navbar.menu-item-left-sidebar-toggler, adminlte::partials.navbar.menu-item-dropdown-user-menu, adminlte::partials.navbar.menu-item-logout-link, adminlte::partials.navbar.menu-item-right-sidebar-toggler

### Community 83 - "Community 83"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 84 - "Community 84"
Cohesion: 0.40
Nodes (4): portal.partials.base-styles, portal.partials.home-card-styles, subscriptions.partials.create-modal, subscriptions.partials.script

### Community 85 - "Community 85"
Cohesion: 0.40
Nodes (4): portal.partials.base-styles, portal.partials.home-card-styles, subscriptions.partials.create-modal, subscriptions.partials.script

### Community 87 - "Community 87"
Cohesion: 0.50
Nodes (3): adminlte::plugins, scripts, styles

### Community 91 - "Community 91"
Cohesion: 0.50
Nodes (4): expectSync(), leverageNative(), returnTrue(), safeActiveElement()

### Community 98 - "Community 98"
Cohesion: 0.50
Nodes (4): post-create-project-cmd, @php artisan key:generate --ansi, @php artisan migrate --graceful --ansi, @php -r \"file_exists('database/database.sqlite') || touch('database/database.sqlite');\

### Community 99 - "autoload-dev"
Cohesion: 0.67
Nodes (3): keywords, framework, laravel

### Community 160 - "Community 160"
Cohesion: 0.67
Nodes (3): Identity(), resolve(), Thrower()

### Community 431 - "Community 431"
Cohesion: 0.67
Nodes (3): boxModelAdjustment(), curCSS(), getWidthOrHeight()

### Community 434 - "extra"
Cohesion: 0.67
Nodes (3): extra, laravel, dont-discover

## Knowledge Gaps
- **166 isolated node(s):** `Memory_optimizer.sh script`, `$schema`, `name`, `type`, `description` (+161 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **35 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `User` connect `Community 30` to `Community 32`, `Community 0`, `Community 33`, `Community 68`, `Community 6`, `Community 38`, `Community 40`, `Community 9`, `AuditChangesController.php`, `Community 46`, `Community 16`, `Community 51`, `Community 20`, `Community 21`, `Community 56`?**
  _High betweenness centrality (0.012) - this node is a cross-community bridge._
- **Why does `Configuration` connect `Community 43` to `Community 32`, `Community 33`, `Community 34`, `Community 0`, `Community 68`, `Community 5`, `Community 6`, `Community 40`, `Community 8`, `Community 9`, `Community 44`, `Community 14`, `Community 48`, `Community 16`, `Community 21`, `Community 24`, `Community 60`, `Community 62`?**
  _High betweenness centrality (0.012) - this node is a cross-community bridge._
- **Why does `ReportSubscription` connect `Community 0` to `Community 32`, `Community 6`, `Community 9`, `Community 56`, `Community 30`?**
  _High betweenness centrality (0.010) - this node is a cross-community bridge._
- **Are the 40 inferred relationships involving `User` (e.g. with `.index()` and `.formData()`) actually correct?**
  _`User` has 40 INFERRED edges - model-reasoned connections that need verification._
- **What connects `Memory_optimizer.sh script`, `$schema`, `name` to the rest of the system?**
  _166 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Community 0` be split into smaller, more focused modules?**
  _Cohesion score 0.060781786941580755 - nodes in this community are weakly interconnected._
- **Should `Community 1` be split into smaller, more focused modules?**
  _Cohesion score 0.05939629990262902 - nodes in this community are weakly interconnected._