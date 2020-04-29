<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light text-sm">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item d-block d-sm-none">
            <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
        </li>
{{--        <li class="nav-item d-none d-sm-inline-block">--}}
{{--            <a href="../../index3.html" class="nav-link">Home</a>--}}
{{--        </li>--}}
{{--        <li class="nav-item d-none d-sm-inline-block">--}}
{{--            <a href="#" class="nav-link">Contact</a>--}}
{{--        </li>--}}
    </ul>

    <!-- SEARCH FORM -->
{{--    <form class="form-inline ml-3 d-none d-lg-block d-xl-block" data-submit="toast::error & doc::preventDefault" data-param="Not working yet! && Error" target>--}}
{{--        <div class="input-group input-group-sm">--}}
{{--            <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">--}}
{{--            <div class="input-group-append">--}}
{{--                <button class="btn btn-navbar" type="submit">--}}
{{--                    <i class="fas fa-search"></i>--}}
{{--                </button>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </form>--}}

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto" @watch>
        @foreach(gets()->lte->menu->nested_collect->where('badge')->where('link') as $menu)
            @php
                $counter = isset($menu['badge']['instructions']) && $menu['badge']['instructions'] ?
                    eloquent_instruction($menu['badge']['text'], $menu['badge']['instructions'])->count() :
                    $menu['badge']['text'];
                $link = isset($menu['link']) ? (isset($menu['badge']['params']) ? makeUrlWithParams($menu['link'], $menu['badge']['params']) : $menu['link']) : 'javascript:void(0)';
            @endphp
            @if($counter)
                <li class="nav-item">
                    <a class="nav-link" href="{{$link}}" title="{{$menu['badge']['title'] ?? $menu['title']}}">
                        <i class="{{$menu['icon']}}"></i>
                        <span class="badge badge-{{isset($menu['badge']['type']) ? $menu['badge']['type'] : 'info'}} navbar-badge">{{$counter}}</span>
                    </a>
                </li>
            @endif
        @endforeach

        @foreach(gets()->lte->menu->nested_collect->where('nav_bar_view') as $menu)
            @include($menu['nav_bar_view'], $menu['params'])
        @endforeach
        <li>
            <a class="nav-link" target="_blank" href="{{config('app.url')}}" title="Open homepage in new tab"><i class="fas fa-external-link-square-alt"></i></a>
        </li>
        <li>
            <a class="nav-link" href="javascript:void(0)" data-click="alert::confirm" data-params="LogOut {{admin()->name}}? && {{route('lte.profile.logout')}} >> $jax.get" title="LogOut"><i class="fas fa-sign-out-alt"></i></a>
        </li>
        @if(config('layout.lang_mode'))
        <!-- Messages Dropdown Menu -->
            <li class="nav-item dropdown language_dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="{{isset(config('lte.lang_flags')[App::getLocale()]) ? config('lte.lang_flags')[App::getLocale()] : ''}}"></i> {{strtoupper(App::getLocale())}}
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    @foreach(config('layout.languages') as $lang)
                        <a class="dropdown-item {{App::getLocale() == $lang ? 'active' : ''}}" href="{{remake_lang_url($lang)}}">
                            <i class="{{isset(config('lte.lang_flags')[$lang]) ? config('lte.lang_flags')[$lang] : ''}}"></i> {{strtoupper($lang)}}
                        </a>
                    @endforeach
                </div>
            </li>
        @endif

        {{--
        <!-- Notifications Dropdown Menu -->
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <i class="far fa-bell"></i>
                <span class="badge badge-warning navbar-badge">15</span>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <span class="dropdown-item dropdown-header">15 Notifications</span>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                    <i class="fas fa-envelope mr-2"></i> 4 new messages
                    <span class="float-right text-muted text-sm">3 mins</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                    <i class="fas fa-users mr-2"></i> 8 friend requests
                    <span class="float-right text-muted text-sm">12 hours</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                    <i class="fas fa-file mr-2"></i> 3 new reports
                    <span class="float-right text-muted text-sm">2 days</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
            </div>
        </li>
        --}}
        {{--
        <li class="nav-item">
            <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#">
                <i class="fas fa-th-large"></i>
            </a>
        </li>
        --}}
    </ul>
</nav>
<!-- /.navbar -->