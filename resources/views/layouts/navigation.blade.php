<nav x-data="{ open: false }" class="bg-white border-b border-gray-200 shadow-card">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2 group">
                        <x-application-logo class="h-9 w-9 shrink-0" />
                        <span class="hidden sm:flex flex-col leading-tight">
                            <span class="text-sm font-bold text-ceeac-primary tracking-tight group-hover:text-ceeac-bright transition-colors">BUDGET-ENGAGE</span>
                            <span class="text-[10px] text-gray-500 font-medium hidden md:block max-w-[11rem] truncate">{{ __('landing.slogan') }}</span>
                        </span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-6 sm:-my-px sm:ms-10 sm:flex sm:items-center">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('dashboard.title') }}
                    </x-nav-link>

                    @can('referentials.manage')
                        <x-dropdown align="left" width="72">
                            <x-slot name="trigger">
                                <button type="button" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none {{ request()->routeIs('referentials.*') ? 'border-ceeac-bright text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                    {{ __('Référentiels') }}
                                    <svg class="ms-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('referentials.departements.index')">{{ __('Départements') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('referentials.directions.index')">{{ __('Directions') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('referentials.services.index')">{{ __('Services') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('referentials.natures-depenses.index')">{{ __('Natures de dépense') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('referentials.sources-financement.index')">{{ __('Sources de financement') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('referentials.fournisseurs.index')">{{ __('Fournisseurs') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('referentials.agents.index')">{{ __('Agents') }}</x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    @endcan

                    @can('viewAny', App\Models\Engagement::class)
                        <x-nav-link :href="route('engagements.index')" :active="request()->routeIs('engagements.*')">
                            {{ __('Engagements') }}
                        </x-nav-link>
                    @endcan

                    @if(auth()->user()->can('reports.view') || auth()->user()->can('reports.view_limited') || auth()->user()->can('exercises.manage') || auth()->user()->can('audit.view'))
                        <x-dropdown align="left" width="56">
                            <x-slot name="trigger">
                                <button type="button" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none {{ request()->routeIs('reports.*') ? 'border-ceeac-bright text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                    {{ __('Rapports') }}
                                    <svg class="ms-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                @if(auth()->user()->can('reports.view') || auth()->user()->can('reports.view_limited') || auth()->user()->can('exercises.manage') || auth()->user()->can('audit.view'))
                                    <x-dropdown-link :href="route('reports.execution.index')">{{ __("Rapport d'exécution") }}</x-dropdown-link>
                                @endif
                                @if(auth()->user()->can('audit.view') || auth()->user()->can('reports.view') || auth()->user()->can('exercises.manage'))
                                    <x-dropdown-link :href="route('reports.activity.index')">{{ __('Journal des opérations budgétaires') }}</x-dropdown-link>
                                @endif
                            </x-slot>
                        </x-dropdown>
                    @endif

                    @canany(['audit.view', 'audit.alerts.manage'])
                        <x-nav-link :href="route('audit.index')" :active="request()->routeIs('audit.*')">
                            {{ __('audit.title') }}
                        </x-nav-link>
                    @endcanany

                    @can('exercises.manage')
                        <x-dropdown align="left" width="56">
                            <x-slot name="trigger">
                                <button type="button" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none {{ request()->routeIs('budget.exercices.*') || request()->routeIs('budget.lignes.*') ? 'border-ceeac-bright text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                    {{ __('Budget') }}
                                    <svg class="ms-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('budget.exercices.index')">{{ __('Exercices budgétaires') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('budget.lignes.index')">{{ __('Lignes budgétaires') }}</x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    @endcan

                    @can('parameters.manage')
                        <x-nav-link :href="route('budget.parametres.index')" :active="request()->routeIs('budget.parametres.*')">
                            {{ __('Paramètres de contrôle') }}
                        </x-nav-link>
                    @endcan

                    @can('users.manage')
                        <x-nav-link :href="route('admin.users.create')" :active="request()->routeIs('admin.users.create')">
                            {{ __('Nouvel utilisateur') }}
                        </x-nav-link>
                    @endcan
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('dashboard.title') }}
            </x-responsive-nav-link>
            @can('referentials.manage')
                <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Référentiels') }}</div>
                <x-responsive-nav-link :href="route('referentials.departements.index')" :active="request()->routeIs('referentials.departements.*')">{{ __('Départements') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('referentials.directions.index')" :active="request()->routeIs('referentials.directions.*')">{{ __('Directions') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('referentials.services.index')" :active="request()->routeIs('referentials.services.*')">{{ __('Services') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('referentials.natures-depenses.index')" :active="request()->routeIs('referentials.natures-depenses.*')">{{ __('Natures de dépense') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('referentials.sources-financement.index')" :active="request()->routeIs('referentials.sources-financement.*')">{{ __('Sources de financement') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('referentials.fournisseurs.index')" :active="request()->routeIs('referentials.fournisseurs.*')">{{ __('Fournisseurs') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('referentials.agents.index')" :active="request()->routeIs('referentials.agents.*')">{{ __('Agents') }}</x-responsive-nav-link>
            @endcan
            @can('viewAny', App\Models\Engagement::class)
                <x-responsive-nav-link :href="route('engagements.index')" :active="request()->routeIs('engagements.*')">{{ __('Engagements') }}</x-responsive-nav-link>
            @endcan
            @if(auth()->user()->can('reports.view') || auth()->user()->can('reports.view_limited') || auth()->user()->can('exercises.manage') || auth()->user()->can('audit.view'))
                <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Rapports') }}</div>
                @if(auth()->user()->can('reports.view') || auth()->user()->can('reports.view_limited') || auth()->user()->can('exercises.manage') || auth()->user()->can('audit.view'))
                    <x-responsive-nav-link :href="route('reports.execution.index')" :active="request()->routeIs('reports.execution.index')">{{ __("Rapport d'exécution") }}</x-responsive-nav-link>
                @endif
                @if(auth()->user()->can('audit.view') || auth()->user()->can('reports.view') || auth()->user()->can('exercises.manage'))
                    <x-responsive-nav-link :href="route('reports.activity.index')" :active="request()->routeIs('reports.activity.index')">{{ __('Journal des opérations budgétaires') }}</x-responsive-nav-link>
                @endif
            @endif
            @canany(['audit.view', 'audit.alerts.manage'])
                <x-responsive-nav-link :href="route('audit.index')" :active="request()->routeIs('audit.*')">{{ __('audit.title') }}</x-responsive-nav-link>
            @endcanany
            @can('exercises.manage')
                <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Budget') }}</div>
                <x-responsive-nav-link :href="route('budget.exercices.index')" :active="request()->routeIs('budget.exercices.*')">{{ __('Exercices budgétaires') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('budget.lignes.index')" :active="request()->routeIs('budget.lignes.*')">{{ __('Lignes budgétaires') }}</x-responsive-nav-link>
            @endcan
            @can('parameters.manage')
                <x-responsive-nav-link :href="route('budget.parametres.index')" :active="request()->routeIs('budget.parametres.*')">{{ __('Paramètres de contrôle') }}</x-responsive-nav-link>
            @endcan
            @can('users.manage')
                <x-responsive-nav-link :href="route('admin.users.create')" :active="request()->routeIs('admin.users.create')">
                    {{ __('Nouvel utilisateur') }}
                </x-responsive-nav-link>
            @endcan
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
