@php
    $pendingFaqQuestionsCount = auth()->check() && auth()->user()->can('faq_access')
        ? \App\Models\FaqQuestion::query()->pending()->count()
        : 0;
@endphp

<div id="sidebar" class="c-sidebar c-sidebar-fixed c-sidebar-lg-show">

    <div class="c-sidebar-brand d-md-down-none">
        <a class="c-sidebar-brand-full brand-lockup" href="{{ route('admin.home') }}">
            <span class="brand-mark">S</span>
            <span class="brand-title">SmartSeat</span>
        </a>
    </div>

    <div class="px-3 pt-3">
        <input
            id="sidebarMenuSearch"
            type="text"
            class="form-control form-control-sm"
            placeholder="Search menu..."
            autocomplete="off"
        >
    </div>

    <ul class="c-sidebar-nav">
        <li class="c-sidebar-nav-item">
            <a href="{{ route('admin.home') }}" class="c-sidebar-nav-link">
                <i class="c-sidebar-nav-icon fas fa-fw fa-tachometer-alt"></i>
                Dashboard
            </a>
        </li>

        @can('user_management_access')
            <li class="c-sidebar-nav-dropdown {{ request()->is('admin/users*') || request()->is('admin/login-activities*') || request()->is('admin/roles*') || request()->is('admin/permissions*') ? 'c-show' : '' }}">
                <a class="c-sidebar-nav-dropdown-toggle" href="#">
                    <i class="fa-fw fas fa-users c-sidebar-nav-icon"></i>
                    User Management
                </a>
                <ul class="c-sidebar-nav-dropdown-items">
                    @can('user_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route('admin.users.index') }}" class="c-sidebar-nav-link {{ request()->is('admin/users*') ? 'c-active' : '' }}">
                                <i class="fa-fw fas fa-user c-sidebar-nav-icon"></i>
                                Users
                            </a>
                        </li>
                    @endcan
                    @can('login_activity_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route('admin.login-activities.index') }}" class="c-sidebar-nav-link {{ request()->is('admin/login-activities*') ? 'c-active' : '' }}">
                                <i class="fa-fw fas fa-sign-in-alt c-sidebar-nav-icon"></i>
                                Login Attempts
                            </a>
                        </li>
                    @endcan
                    @can('role_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route('admin.roles.index') }}" class="c-sidebar-nav-link {{ request()->is('admin/roles*') ? 'c-active' : '' }}">
                                <i class="fa-fw fas fa-briefcase c-sidebar-nav-icon"></i>
                                Roles
                            </a>
                        </li>
                    @endcan
                    @can('permission_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route('admin.permissions.index') }}" class="c-sidebar-nav-link {{ request()->is('admin/permissions*') ? 'c-active' : '' }}">
                                <i class="fa-fw fas fa-unlock-alt c-sidebar-nav-icon"></i>
                                Permission
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcan

        @can('setting_access')
            <li class="c-sidebar-nav-dropdown {{ request()->is('admin/frontend/header*') || request()->is('admin/frontend/auth-page*') || request()->is('admin/frontend/footer*') || request()->is('admin/frontend/pages*') || request()->is('admin/frontend/page-access*') || request()->is('admin/frontend/seo-titles*') ? 'c-show' : '' }}">
                <a class="c-sidebar-nav-dropdown-toggle" href="#">
                    <i class="c-sidebar-nav-icon fas fa-window-maximize"></i>
                    Front End
                </a>
                <ul class="c-sidebar-nav-dropdown-items">
                    <li class="c-sidebar-nav-item">
                        <a href="{{ route('admin.frontend.header.edit') }}" class="c-sidebar-nav-link {{ request()->is('admin/frontend/header*') ? 'c-active' : '' }}">
                            <i class="fa-fw fas fa-heading c-sidebar-nav-icon"></i>
                            Header
                        </a>
                    </li>
                    <li class="c-sidebar-nav-item">
                        <a href="{{ route('admin.frontend.auth-page.edit') }}" class="c-sidebar-nav-link {{ request()->is('admin/frontend/auth-page*') ? 'c-active' : '' }}">
                            <i class="fa-fw fas fa-user-plus c-sidebar-nav-icon"></i>
                            Sign In/Out Pages
                        </a>
                    </li>
                    <li class="c-sidebar-nav-item">
                        <a href="{{ route('admin.frontend.footer.edit') }}" class="c-sidebar-nav-link {{ request()->is('admin/frontend/footer*') ? 'c-active' : '' }}">
                            <i class="fa-fw fas fa-shoe-prints c-sidebar-nav-icon"></i>
                            Footer
                        </a>
                    </li>
                    <li class="c-sidebar-nav-item">
                        <a href="{{ route('admin.frontend.pages.index') }}" class="c-sidebar-nav-link {{ request()->is('admin/frontend/pages*') ? 'c-active' : '' }}">
                            <i class="fa-fw fas fa-file-alt c-sidebar-nav-icon"></i>
                            Pages
                        </a>
                    </li>
                    <li class="c-sidebar-nav-item">
                        <a href="{{ route('admin.frontend.page-access.edit') }}" class="c-sidebar-nav-link {{ request()->is('admin/frontend/page-access*') ? 'c-active' : '' }}">
                            <i class="fa-fw fas fa-lock-open c-sidebar-nav-icon"></i>
                            Page Access
                        </a>
                    </li>
                    <li class="c-sidebar-nav-item">
                        <a href="{{ route('admin.frontend.seo-titles.index') }}" class="c-sidebar-nav-link {{ request()->is('admin/frontend/seo-titles*') ? 'c-active' : '' }}">
                            <i class="fa-fw fas fa-search c-sidebar-nav-icon"></i>
                            Page Titles
                        </a>
                    </li>
                </ul>
            </li>
        @endcan

        @can('setting_access')
            <li class="c-sidebar-nav-dropdown {{ request()->is('admin/frontend/download*') || request()->is('admin/download-media-items*') ? 'c-show' : '' }}">
                <a class="c-sidebar-nav-dropdown-toggle" href="#">
                    <i class="fa-fw fas fa-download c-sidebar-nav-icon"></i>
                    Download
                </a>
                <ul class="c-sidebar-nav-dropdown-items">
                    <li class="c-sidebar-nav-item">
                        <a href="{{ route('admin.frontend.download.edit') }}" class="c-sidebar-nav-link {{ request()->is('admin/frontend/download*') ? 'c-active' : '' }}">
                            <i class="fa-fw fas fa-file-alt c-sidebar-nav-icon"></i>
                            Download Page
                        </a>
                    </li>
                    <li class="c-sidebar-nav-item">
                        <a href="{{ route('admin.download-media-items.index') }}" class="c-sidebar-nav-link {{ request()->is('admin/download-media-items*') ? 'c-active' : '' }}">
                            <i class="fa-fw fas fa-images c-sidebar-nav-icon"></i>
                            Download Items
                        </a>
                    </li>
                </ul>
            </li>
        @endcan

        @canany(['faq_access', 'faq_category_access'])
            <li class="c-sidebar-nav-dropdown {{ request()->is('admin/faqs*') || request()->is('admin/faq-categories*') || request()->is('admin/faq-questions*') ? 'c-show' : '' }}">
                <a class="c-sidebar-nav-dropdown-toggle" href="#">
                    <i class="c-sidebar-nav-icon fas fa-question-circle"></i>
                    FAQ's
                    @if($pendingFaqQuestionsCount > 0)
                        <span class="ml-2" style="display:inline-block;width:10px;height:10px;background:#ef4444;border-radius:999px;"></span>
                    @endif
                </a>
                <ul class="c-sidebar-nav-dropdown-items">
                    @can('faq_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route('admin.faqs.index') }}" class="c-sidebar-nav-link {{ request()->is('admin/faqs*') ? 'c-active' : '' }}">
                                <i class="c-sidebar-nav-icon fas fa-question-circle"></i>
                                FAQ's
                            </a>
                        </li>
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route('admin.faq-questions.index') }}" class="c-sidebar-nav-link {{ request()->is('admin/faq-questions*') ? 'c-active' : '' }}">
                                <i class="c-sidebar-nav-icon fas fa-inbox"></i>
                                Asked Questions
                                @if($pendingFaqQuestionsCount > 0)
                                    <span class="badge badge-danger ml-2">{{ $pendingFaqQuestionsCount }}</span>
                                @endif
                            </a>
                        </li>
                    @endcan
                    @can('faq_category_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route('admin.faq-categories.index') }}" class="c-sidebar-nav-link {{ request()->is('admin/faq-categories*') ? 'c-active' : '' }}">
                                <i class="c-sidebar-nav-icon fas fa-tags"></i>
                                FAQ Categories
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcanany

        @can('member_access')
            <li class="c-sidebar-nav-item">
                <a href="{{ route('admin.members.index') }}" class="c-sidebar-nav-link {{ request()->is('admin/members*') ? 'c-active' : '' }}">
                    <i class="c-sidebar-nav-icon fas fa-user-tie"></i>
                    Members
                </a>
            </li>
        @endcan

        @can('setting_access')
            <li class="c-sidebar-nav-dropdown {{ request()->is('admin/frontend/pages*') ? 'c-show' : '' }}">
                <a class="c-sidebar-nav-dropdown-toggle" href="#">
                    <i class="c-sidebar-nav-icon fas fa-gavel"></i>
                    Terms & Condition
                </a>
                <ul class="c-sidebar-nav-dropdown-items">
                    <li class="c-sidebar-nav-item">
                        <a href="{{ route('admin.frontend.pages.index') }}" class="c-sidebar-nav-link {{ request()->is('admin/frontend/pages*') ? 'c-active' : '' }}">
                            <i class="fa-fw fas fa-file-alt c-sidebar-nav-icon"></i>
                            Terms & Condition
                        </a>
                    </li>
                    <li class="c-sidebar-nav-item">
                        <a href="{{ route('admin.frontend.pages.index') }}" class="c-sidebar-nav-link {{ request()->is('admin/frontend/pages*') ? 'c-active' : '' }}">
                            <i class="fa-fw fas fa-file-alt c-sidebar-nav-icon"></i>
                            Privacy Policy
                        </a>
                    </li>
                    <li class="c-sidebar-nav-item">
                        <a href="{{ route('admin.frontend.pages.index') }}" class="c-sidebar-nav-link {{ request()->is('admin/frontend/pages*') ? 'c-active' : '' }}">
                            <i class="fa-fw fas fa-file-alt c-sidebar-nav-icon"></i>
                            Rules & Regulations
                        </a>
                    </li>
                    <li class="c-sidebar-nav-item">
                        <a href="{{ route('admin.frontend.pages.index') }}" class="c-sidebar-nav-link {{ request()->is('admin/frontend/pages*') ? 'c-active' : '' }}">
                            <i class="fa-fw fas fa-file-alt c-sidebar-nav-icon"></i>
                            Code of Conduct
                        </a>
                    </li>
                </ul>
            </li>
        @endcan

        @can('setting_access')
            <li class="c-sidebar-nav-item">
                <a href="{{ route('admin.frontend.contact.edit') }}" class="c-sidebar-nav-link {{ request()->is('admin/frontend/contact*') ? 'c-active' : '' }}">
                    <i class="fa-fw fas fa-envelope c-sidebar-nav-icon"></i>
                    Contact Us
                </a>
            </li>
        @endcan

        @can('detail_access')
            <li class="c-sidebar-nav-dropdown {{ request()->is('admin/cities*') || request()->is('admin/states*') || request()->is('admin/countries*') ? 'c-show' : '' }}">
                <a class="c-sidebar-nav-dropdown-toggle" href="#">
                    <i class="c-sidebar-nav-icon fas fa-map"></i>
                    Details
                </a>
                <ul class="c-sidebar-nav-dropdown-items">
                    @can('city_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route('admin.cities.index') }}" class="c-sidebar-nav-link {{ request()->is('admin/cities*') ? 'c-active' : '' }}">
                                <i class="c-sidebar-nav-icon fas fa-map-marker-alt"></i>
                                City
                            </a>
                        </li>
                    @endcan
                    @can('state_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route('admin.states.index') }}" class="c-sidebar-nav-link {{ request()->is('admin/states*') ? 'c-active' : '' }}">
                                <i class="c-sidebar-nav-icon fas fa-map-signs"></i>
                                State
                            </a>
                        </li>
                    @endcan
                    @can('country_access')
                        <li class="c-sidebar-nav-item">
                            <a href="{{ route('admin.countries.index') }}" class="c-sidebar-nav-link {{ request()->is('admin/countries*') ? 'c-active' : '' }}">
                                <i class="c-sidebar-nav-icon fas fa-flag"></i>
                                Country
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcan

        @can('setting_access')
            <li class="c-sidebar-nav-item">
                <a href="{{ route('admin.my-contacts.index') }}" class="c-sidebar-nav-link {{ request()->is('admin/my-contacts*') ? 'c-active' : '' }}">
                    <i class="fa-fw fas fa-address-book c-sidebar-nav-icon"></i>
                    My Contacts
                </a>
            </li>
            <li class="c-sidebar-nav-item">
                <a href="{{ route('admin.settings.index') }}" class="c-sidebar-nav-link {{ request()->is('admin/settings*') ? 'c-active' : '' }}">
                    <i class="fa-fw fas fa-cogs c-sidebar-nav-icon"></i>
                    Settings
                </a>
            </li>
        @endcan

        @can('profile_password_edit')
            <li class="c-sidebar-nav-item">
                <a href="{{ route('profile.password.edit') }}" class="c-sidebar-nav-link {{ request()->is('profile/password*') ? 'c-active' : '' }}">
                    <i class="fa-fw fas fa-key c-sidebar-nav-icon"></i>
                    Change Password
                </a>
            </li>
        @endcan

        <li class="c-sidebar-nav-item">
            <a href="#" class="c-sidebar-nav-link"
                onclick="event.preventDefault(); document.getElementById('logoutform').submit();">
                <i class="c-sidebar-nav-icon fas fa-fw fa-sign-out-alt"></i>
                Sign Out
            </a>
        </li>
    </ul>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('sidebarMenuSearch');
        const sidebarNav = document.querySelector('#sidebar .c-sidebar-nav');
        if (!searchInput || !sidebarNav) return;

        const topItems = Array.from(sidebarNav.children).filter(function (item) {
            return item.classList.contains('c-sidebar-nav-item') || item.classList.contains('c-sidebar-nav-dropdown');
        });

        topItems.forEach(function (item) {
            if (item.classList.contains('c-sidebar-nav-dropdown')) {
                item.dataset.originalShow = item.classList.contains('c-show') ? '1' : '0';
            }
        });

        function normalize(text) {
            return (text || '').toLowerCase().replace(/\s+/g, ' ').trim();
        }

        function textMatches(text, query) {
            return normalize(text).includes(query);
        }

        function filterMenu() {
            const query = normalize(searchInput.value);

            topItems.forEach(function (item) {
                if (item.classList.contains('c-sidebar-nav-dropdown')) {
                    const toggle = item.querySelector(':scope > .c-sidebar-nav-dropdown-toggle');
                    const children = Array.from(item.querySelectorAll(':scope > .c-sidebar-nav-dropdown-items > .c-sidebar-nav-item'));

                    const parentMatch = toggle ? textMatches(toggle.textContent, query) : false;
                    let childMatchCount = 0;

                    children.forEach(function (child) {
                        const childMatch = query === '' || textMatches(child.textContent, query) || parentMatch;
                        child.style.display = childMatch ? '' : 'none';
                        if (childMatch) childMatchCount++;
                    });

                    const visible = query === '' || parentMatch || childMatchCount > 0;
                    item.style.display = visible ? '' : 'none';

                    if (query !== '' && visible) {
                        item.classList.add('c-show');
                    } else if (query === '') {
                        if (item.dataset.originalShow === '1') {
                            item.classList.add('c-show');
                        } else {
                            item.classList.remove('c-show');
                        }
                    }
                } else {
                    const visible = query === '' || textMatches(item.textContent, query);
                    item.style.display = visible ? '' : 'none';
                }
            });
        }

        searchInput.addEventListener('input', filterMenu);
    });
</script>
