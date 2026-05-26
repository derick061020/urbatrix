<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Sidebar Navigation</title>
        <link href="https://fonts.googleapis.com" rel="preconnect" />
        <link
            crossorigin=""
            href="https://fonts.gstatic.com"
            rel="preconnect"
        />
        <link
            href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&amp;display=swap"
            rel="stylesheet"
        />
        <link
            href="https://unpkg.com/primeicons/primeicons.css"
            rel="stylesheet"
        />

        <style>
            #app-root {
                min-height: 100vh;
                display: flex;
            }

            .material-design-icon__svg {
                fill: currentColor;
            }
        </style>
        <style type="text/tailwindcss">
            @tailwind base;
            @tailwind components;
            @tailwind utilities;

            @layer components {
                .lb-admin-main-nav-link {
                    @apply flex items-center px-0 py-1 transition-colors duration-200;
                }
                .lb-admin-man-nav-section-heading {
                    @apply font-semibold text-xxs uppercase tracking-tight-sm mt-5 mb-1;
                }
                .lb-admin-no-spacing {
                    @apply !my-0 !space-y-0;
                }
            }
        </style>
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                important: "#app-root",
                theme: {
                    extend: {
                        colors: {
                            "surface-700": "rgb(0, 0, 0)",
                            "surface-600": "rgb(30, 30, 30)",
                            "surface-200": "rgb(128, 128, 128)",
                            "brand-primary": "rgb(20, 20, 20)",
                            "brand-primary-hover": "rgb(40, 40, 40)",
                            "text-primary-inverse": "rgb(255, 255, 255)",
                            primary: {
                                DEFAULT: '#000000',
                                500:     '#000000',
                                hover:   '#1a1a1a',
                                inverse: '#ffffff',
                            },
                            secondary: {
                                DEFAULT: '#333333',
                            },
                            surface: {
                                50:  '#f5f5f5',
                                200: '#888888',
                                600: '#1a1a1a',
                                700: '#000000',
                            },
                        },
                        fontFamily: {
                            primary: [
                                "Montserrat",
                                "Helvetica",
                                "Arial",
                                "sans-serif",
                            ],
                            secondary: ["Montserrat", "sans-serif"],
                        },
                        fontSize: {
                            xxs: "10px",
                            12: "12px",
                        },
                        letterSpacing: {
                            "tight-sm": "0.25px",
                            "wide-sm": "0.3px",
                        },
                    },
                },
            };
        </script>
    </head>

    <body id="app-root">
        <div
            class="font-primary flex flex-col min-h-screen w-full bg-white overflow-hidden"
        >
            <!-- No global header or footer as per user instructions to reproduce ONLY the component -->
            <main class="flex-grow flex">
                <nav
                    id="admin-sidebar"
                    class="flex flex-col bg-surface-700 relative transition-all duration-300 ease-in-out w-[280px] h-full min-h-[862px]"
                >
                    <div
                        class="px-4 pt-4 flex flex-col bg-surface-600 relative transition-all h-full"
                    >
                        <!-- Close Icon -->
                        <span
                            id="sidebar-toggle"
                            class="absolute right-2 top-0 z-40 text-text-primary-inverse cursor-pointer p-1 hover:bg-white/10 rounded transition-colors"
                            aria-hidden="true"
                        >
                            <svg
                                id="close-icon"
                                class="material-design-icon__svg"
                                width="28"
                                height="28"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    d="M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z"
                                ></path>
                            </svg>
                            <svg
                                id="menu-icon"
                                class="material-design-icon__svg hidden"
                                width="28"
                                height="28"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    d="M3,6H21V8H3V6M3,11H21V13H3V11M3,16H21V18H3V16Z"
                                ></path>
                            </svg>
                        </span>

                        <!-- Logo Section -->
                        <div id="logo-section" class="pt-3 px-4 transition-all duration-300 ease-in-out">
                            <img
                                src="https://dunadevelopment.com/wp-content/cache/seraphinite-accelerator/s/m/d/img/c97274bc659c16e3ced9d91b315e2fd2.63be.png"
                                alt="Makai Residences"
                                class="w-[208px] h-auto object-contain transition-all duration-300 ease-in-out"
                            />
                            <div
                                role="separator"
                                class="relative my-5 mx-0 h-[1px] w-full before:block before:absolute before:left-0 before:top-1/2 before:w-full before:border-t before:border-surface-200"
                            ></div>
                        </div>

                        <!-- CTA Button -->
                        <div id="cta-section" class="mt-5 block transition-all duration-300 ease-in-out">
                            <a href="/" class="block">
                                <button
                                    type="button"
                                    aria-label="View Price List"
                                    class="w-full h-12 relative flex items-center justify-center bg-brand-primary border border-brand-primary text-text-primary-inverse rounded-md font-semibold hover:bg-brand-primary-hover transition duration-200 cursor-pointer select-none"
                                >
                                    <span class="text-16 transition-all duration-300 ease-in-out">View Price List</span>
                                </button>
                            </a>
                        </div>

                        <!-- Navigation List -->
                        <div id="nav-section" class="mt-2 pb-10 overflow-y-auto transition-all duration-300 ease-in-out">
                            <ul class="space-y-1">
                                @if(Auth::user()->role !== 'broker')
                                <!-- Main Section -->
                                <li>
                                    <a
                                        href="/admin/dashboard"
                                        class="lb-admin-main-nav-link {{ $activeRoute === 'dashboard' ? 'bg-[rgba(155,172,158,0.2)] rounded shadow-sm' : '' }}"
                                    >
                                        <span
                                            class="p-1 text-text-primary-inverse"
                                        >
                                            <svg
                                                class="material-design-icon__svg"
                                                width="20"
                                                height="20"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    d="M13,3V9H21V3M13,21H21V11H13M3,21H11V15H3M3,13H11V3H3V13Z"
                                                ></path>
                                            </svg>
                                        </span>
                                        <p
                                            class="nav-text ml-2 text-12 font-semibold text-text-primary-inverse uppercase tracking-wide-sm transition-all duration-300 ease-in-out"
                                        >
                                            Dashboard
                                        </p>
                                    </a>
                                </li>
                                <li>
                                    <div
                                        class="lb-admin-main-nav-link cursor-pointer"
                                    >
                                        <span
                                            class="p-1 text-text-primary-inverse"
                                        >
                                            <svg
                                                class="material-design-icon__svg"
                                                width="20"
                                                height="20"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    d="M12 2C12 2 7 4 7 12C7 15.1 7.76 17.75 8.67 19.83C9 20.55 9.71 21 10.5 21H13.5C14.29 21 15 20.55 15.33 19.83C16.25 17.75 17 15.1 17 12C17 4 12 2 12 2M13.5 19H10.5C9.5 16.76 9 14.41 9 12C9 7.36 10.9 5.2 12 4.33C13.1 5.2 15 7.36 15 12C15 14.41 14.5 16.76 13.5 19M20 22L16.14 20.45C16.84 18.92 17.34 17.34 17.65 15.73M7.86 20.45L4 22L6.35 15.73C6.66 17.34 7.16 18.92 7.86 20.45M12 12C10.9 12 10 11.1 10 10C10 8.9 10.9 8 12 8C13.1 8 14 8.9 14 10C14 11.1 13.1 12 12 12Z"
                                                ></path>
                                            </svg>
                                        </span>
                                        <p
                                            class="nav-text ml-2 text-12 font-semibold text-text-primary-inverse uppercase tracking-wide-sm transition-all duration-300 ease-in-out"
                                        >
                                            Launch Experience
                                        </p>
                                    </div>
                                </li>

                                <!-- Units Section -->
                                <li
                                    class="lb-admin-man-nav-section-heading text-text-primary-inverse !mt-6"
                                >
                                    Units
                                </li>
                                <li>
                                    <a
                                        href="/admin/units"
                                        class="lb-admin-main-nav-link {{ $activeRoute === 'units' ? 'bg-[rgba(155,172,158,0.2)] rounded shadow-sm' : '' }}"
                                    >
                                        <span
                                            class="p-1 text-text-primary-inverse"
                                        >
                                            <svg
                                                class="material-design-icon__svg"
                                                width="20"
                                                height="20"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    d="M10,20V14H14V20H19V12H22L12,3L2,12H5V20H10Z"
                                                ></path>
                                            </svg>
                                        </span>
                                        <p
                                            class="nav-text ml-2 text-12 font-semibold text-text-primary-inverse uppercase tracking-wide-sm transition-all duration-300 ease-in-out"
                                        >
                                            Units
                                        </p>
                                    </a>
                                </li>

                                <!-- Deals Section -->
                                <li
                                    class="lb-admin-man-nav-section-heading text-text-primary-inverse !mt-6"
                                >
                                    Deals
                                </li>
                                <li>
                                    <a
                                        href="/admin/deals"
                                        class="lb-admin-main-nav-link {{ $activeRoute === 'deals' ? 'bg-[rgba(155,172,158,0.2)] rounded shadow-sm' : '' }}"
                                    >
                                        <span
                                            class="p-1 text-text-primary-inverse"
                                        >
                                            <svg
                                                class="material-design-icon__svg"
                                                width="20"
                                                height="20"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    d="M16.5 11L13 7.5L14.4 6.1L16.5 8.2L20.7 4L22.1 5.4L16.5 11M11 7H2V9H11V7M21 13.4L19.6 12L17 14.6L14.4 12L13 13.4L15.6 16L13 18.6L14.4 20L17 17.4L19.6 20L21 18.6L18.4 16L21 13.4M11 15H2V17H11V15Z"
                                                ></path>
                                            </svg>
                                        </span>
                                        <p
                                            class="nav-text ml-2 text-12 font-semibold text-text-primary-inverse uppercase tracking-wide-sm transition-all duration-300 ease-in-out"
                                        >
                                            Deals
                                        </p>
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="/admin/transactions-report"
                                        class="lb-admin-main-nav-link {{ $activeRoute === 'transactions-report' ? 'bg-[rgba(155,172,158,0.2)] rounded shadow-sm' : '' }}"
                                    >
                                        <div
                                            class="w-7 h-7 flex justify-center items-center text-text-primary-inverse"
                                        >
                                            <i
                                                class="pi pi-download text-sm"
                                            ></i>
                                        </div>
                                        <p
                                            class="nav-text ml-2 text-12 font-semibold text-text-primary-inverse uppercase tracking-wide-sm transition-all duration-300 ease-in-out"
                                        >
                                            Transactions Report
                                        </p>
                                    </a>
                                </li>

                                <!-- Clients Section -->
                                <li
                                    class="lb-admin-man-nav-section-heading text-text-primary-inverse !mt-6"
                                >
                                    Clients
                                </li>
                                <li>
                                    <a
                                        href="/admin/profiles"
                                        class="lb-admin-main-nav-link {{ $activeRoute === 'profiles' ? 'bg-[rgba(155,172,158,0.2)] rounded shadow-sm' : '' }}"
                                    >
                                        <span
                                            class="p-1 text-text-primary-inverse"
                                        >
                                            <svg
                                                class="material-design-icon__svg"
                                                width="20"
                                                height="20"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    d="M12,4A4,4 0 0,1 16,8A4,4 0 0,1 12,12A4,4 0 0,1 8,8A4,4 0 0,1 12,4M12,14C16.42,14 20,15.79 20,18V20H4V18C4,15.79 7.58,14 12,14Z"
                                                ></path>
                                            </svg>
                                        </span>
                                        <p
                                            class="nav-text ml-2 text-12 font-semibold text-text-primary-inverse uppercase tracking-wide-sm transition-all duration-300 ease-in-out"
                                        >
                                            Profiles
                                        </p>
                                    </a>
                                </li>
                                <li>
                                    <!-- Active Link state shown in source (9B AC 9E 0.2 background) -->
                                    <a
                                        href="/admin/agents"
                                        class="lb-admin-main-nav-link {{ $activeRoute === 'agents' ? 'bg-[rgba(155,172,158,0.2)] rounded shadow-sm' : '' }}"
                                    >
                                        <span
                                            class="p-1 text-text-primary-inverse"
                                        >
                                            <svg
                                                class="material-design-icon__svg"
                                                width="20"
                                                height="20"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    d="M18.72,14.76C19.07,13.91 19.26,13 19.26,12C19.26,11.28 19.15,10.59 18.96,9.95C18.31,10.1 17.63,10.18 16.92,10.18C13.86,10.18 11.15,8.67 9.5,6.34C8.61,8.5 6.91,10.26 4.77,11.22C4.73,11.47 4.73,11.74 4.73,12A7.27,7.27 0 0,0 12,19.27C13.05,19.27 14.06,19.04 14.97,18.63C15.54,19.72 15.8,20.26 15.78,20.26C14.14,20.81 12.87,21.08 12,21.08C9.58,21.08 7.27,20.13 5.57,18.42C4.53,17.38 3.76,16.11 3.33,14.73H2V10.18H3.09C3.93,6.04 7.6,2.92 12,2.92C14.4,2.92 16.71,3.87 18.42,5.58C19.69,6.84 20.54,8.45 20.89,10.18H22V14.67H22V14.69L22,14.73H21.94L18.38,18L13.08,17.4V15.73H17.91L18.72,14.76M9.27,11.77C9.57,11.77 9.86,11.89 10.07,12.11C10.28,12.32 10.4,12.61 10.4,12.91C10.4,13.21 10.28,13.5 10.07,13.71C9.86,13.92 9.57,14.04 9.27,14.04C8.64,14.04 8.13,13.54 8.13,12.91C8.13,12.28 8.64,11.77 9.27,11.77M14.72,11.77C15.35,11.77 15.85,12.28 15.85,12.91C15.85,13.54 15.35,14.04 14.72,14.04C14.09,14.04 13.58,13.54 13.58,12.91A1.14,1.14 0 0,1 14.72,11.77Z"
                                                ></path>
                                            </svg>
                                        </span>
                                        <p
                                            class="nav-text ml-2 text-12 font-semibold text-text-primary-inverse uppercase tracking-wide-sm transition-all duration-300 ease-in-out"
                                        >
                                            Agents
                                        </p>
                                    </a>
                                </li>

                                <!-- Communication Section -->
                                <li
                                    class="lb-admin-man-nav-section-heading text-text-primary-inverse !mt-6"
                                >
                                    Communication
                                </li>
                                <li>
                                    <a
                                        href="/admin/communication"
                                        class="lb-admin-main-nav-link {{ $activeRoute === 'communication' ? 'bg-[rgba(155,172,158,0.2)] rounded shadow-sm' : '' }}"
                                    >
                                        <div
                                            class="w-7 h-7 flex justify-center items-center text-text-primary-inverse"
                                        >
                                            <i
                                                class="pi pi-envelope text-sm"
                                            ></i>
                                        </div>
                                        <p
                                            class="nav-text ml-2 text-12 font-semibold text-text-primary-inverse uppercase tracking-wide-sm transition-all duration-300 ease-in-out"
                                        >
                                            Communication
                                        </p>
                                    </a>
                                </li>
                                @endif
                                <!-- CRM Operativo Section -->
                                <li
                                    class="lb-admin-man-nav-section-heading text-text-primary-inverse !mt-6"
                                >
                                    CRM Operativo
                                </li>
                                <li>
                                    <a href="/admin/crm/dashboard" class="lb-admin-main-nav-link {{ ($activeRoute ?? '') === 'crm.dashboard' ? 'bg-[rgba(155,172,158,0.2)] rounded shadow-sm' : '' }}">
                                        <span class="p-1 text-text-primary-inverse">
                                            <svg class="material-design-icon__svg" width="20" height="20" viewBox="0 0 24 24"><path d="M13,3V9H21V3M13,21H21V11H13M3,21H11V15H3M3,13H11V3H3V13Z"></path></svg>
                                        </span>
                                        <p class="nav-text ml-2 text-12 font-semibold text-text-primary-inverse uppercase tracking-wide-sm transition-all duration-300 ease-in-out">CRM Dashboard</p>
                                    </a>
                                </li>
                                <li>
                                    <a href="/admin/crm/expedientes" class="lb-admin-main-nav-link {{ ($activeRoute ?? '') === 'crm.expedientes' ? 'bg-[rgba(155,172,158,0.2)] rounded shadow-sm' : '' }}">
                                        <span class="p-1 text-text-primary-inverse">
                                            <svg class="material-design-icon__svg" width="20" height="20" viewBox="0 0 24 24"><path d="M20,6H12L10,4H4A2,2 0 0,0 2,6V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V8A2,2 0 0,0 20,6M20,18H4V8H20V18Z"></path></svg>
                                        </span>
                                        <p class="nav-text ml-2 text-12 font-semibold text-text-primary-inverse uppercase tracking-wide-sm transition-all duration-300 ease-in-out">Expedientes</p>
                                    </a>
                                </li>
                                <li>
                                    <a href="/admin/crm/documentos" class="lb-admin-main-nav-link {{ ($activeRoute ?? '') === 'crm.documentos' ? 'bg-[rgba(155,172,158,0.2)] rounded shadow-sm' : '' }}">
                                        <span class="p-1 text-text-primary-inverse">
                                            <svg class="material-design-icon__svg" width="20" height="20" viewBox="0 0 24 24"><path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"></path></svg>
                                        </span>
                                        <p class="nav-text ml-2 text-12 font-semibold text-text-primary-inverse uppercase tracking-wide-sm transition-all duration-300 ease-in-out">Gestión Documental</p>
                                    </a>
                                </li>
                                <li>
                                    <a href="/admin/crm/contratos" class="lb-admin-main-nav-link {{ ($activeRoute ?? '') === 'crm.contratos' ? 'bg-[rgba(155,172,158,0.2)] rounded shadow-sm' : '' }}">
                                        <span class="p-1 text-text-primary-inverse">
                                            <svg class="material-design-icon__svg" width="20" height="20" viewBox="0 0 24 24"><path d="M9,5A2,2 0 0,1 11,3H13A2,2 0 0,1 15,5H19A2,2 0 0,1 21,7V19A2,2 0 0,1 19,21H5A2,2 0 0,1 3,19V7A2,2 0 0,1 5,5H9M11,5V7H13V5H11M7,11H17V9H7V11M7,15H17V13H7V15M7,19H14V17H7V19Z"></path></svg>
                                        </span>
                                        <p class="nav-text ml-2 text-12 font-semibold text-text-primary-inverse uppercase tracking-wide-sm transition-all duration-300 ease-in-out">Reservas y Contratos</p>
                                    </a>
                                </li>
                                <li>
                                    <a href="/admin/crm/proyectos" class="lb-admin-main-nav-link {{ ($activeRoute ?? '') === 'crm.proyectos' ? 'bg-[rgba(155,172,158,0.2)] rounded shadow-sm' : '' }}">
                                        <span class="p-1 text-text-primary-inverse">
                                            <svg class="material-design-icon__svg" width="20" height="20" viewBox="0 0 24 24"><path d="M14,6H22V8H14V6M14,10H22V12H14V10M14,14H20V16H14V14M2,2H12V12H2V2M4,4V10H10V4H4M2,14H12V22H2V14M4,16V20H10V16H4Z"></path></svg>
                                        </span>
                                        <p class="nav-text ml-2 text-12 font-semibold text-text-primary-inverse uppercase tracking-wide-sm transition-all duration-300 ease-in-out">Proyectos</p>
                                    </a>
                                </li>
                                <li>
                                    <a href="/admin/crm/postventa" class="lb-admin-main-nav-link {{ ($activeRoute ?? '') === 'crm.postventa' ? 'bg-[rgba(155,172,158,0.2)] rounded shadow-sm' : '' }}">
                                        <span class="p-1 text-text-primary-inverse">
                                            <svg class="material-design-icon__svg" width="20" height="20" viewBox="0 0 24 24"><path d="M10,20V14H14V20H19V12H22L12,3L2,12H5V20H10Z"></path></svg>
                                        </span>
                                        <p class="nav-text ml-2 text-12 font-semibold text-text-primary-inverse uppercase tracking-wide-sm transition-all duration-300 ease-in-out">Postventa</p>
                                    </a>
                                </li>
                                <li>
                                    <a href="/admin/crm/aprobaciones" class="lb-admin-main-nav-link {{ ($activeRoute ?? '') === 'crm.aprobaciones' ? 'bg-[rgba(155,172,158,0.2)] rounded shadow-sm' : '' }}">
                                        <span class="p-1 text-text-primary-inverse">
                                            <svg class="material-design-icon__svg" width="20" height="20" viewBox="0 0 24 24"><path d="M21,7L9,19L3.5,13.5L4.91,12.09L9,16.17L19.59,5.59L21,7Z"></path></svg>
                                        </span>
                                        <p class="nav-text ml-2 text-12 font-semibold text-text-primary-inverse uppercase tracking-wide-sm transition-all duration-300 ease-in-out">Aprobaciones</p>
                                    </a>
                                </li>
                                <li>
                                    <a href="/admin/crm/tareas" class="lb-admin-main-nav-link {{ ($activeRoute ?? '') === 'crm.tareas' ? 'bg-[rgba(155,172,158,0.2)] rounded shadow-sm' : '' }}">
                                        <span class="p-1 text-text-primary-inverse">
                                            <svg class="material-design-icon__svg" width="20" height="20" viewBox="0 0 24 24"><path d="M19,3H14.82C14.4,1.84 13.3,1 12,1C10.7,1 9.6,1.84 9.18,3H5A2,2 0 0,0 3,5V21A2,2 0 0,0 5,23H19A2,2 0 0,0 21,21V5A2,2 0 0,0 19,3M12,3A1,1 0 0,1 13,4A1,1 0 0,1 12,5A1,1 0 0,1 11,4A1,1 0 0,1 12,3M7,7H17V5H19V21H5V5H7V7M17,11H7V9H17V11M15,15H7V13H15V15M13,19H7V17H13V19Z"></path></svg>
                                        </span>
                                        <p class="nav-text ml-2 text-12 font-semibold text-text-primary-inverse uppercase tracking-wide-sm transition-all duration-300 ease-in-out">Tareas</p>
                                    </a>
                                </li>

                                @if(Auth::user()->role !== 'broker')
                                <!-- Management Section -->
                                <li
                                    class="lb-admin-man-nav-section-heading text-text-primary-inverse !mt-6"
                                >
                                    Management
                                </li>
                                <li>
                                    <a
                                        href="/admin/settings"
                                        class="lb-admin-main-nav-link {{ $activeRoute === 'settings' ? 'bg-[rgba(155,172,158,0.2)] rounded shadow-sm' : '' }}"
                                    >
                                        <span
                                            class="p-1 text-text-primary-inverse"
                                        >
                                            <svg
                                                class="material-design-icon__svg"
                                                width="20"
                                                height="20"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    d="M12,15.5A3.5,3.5 0 0,1 8.5,12A3.5,3.5 0 0,1 12,8.5A3.5,3.5 0 0,1 15.5,12A3.5,3.5 0 0,1 12,15.5M19.43,12.97C19.47,12.65 19.5,12.33 19.5,12C19.5,11.67 19.47,11.34 19.43,11L21.54,9.37C21.73,9.22 21.78,8.95 21.66,8.73L19.66,5.27C19.54,5.05 19.27,4.96 19.05,5.05L16.56,6.05C16.04,5.66 15.5,5.32 14.87,5.07L14.5,2.42C14.46,2.18 14.25,2 14,2H10C9.75,2 9.54,2.18 9.5,2.42L9.13,5.07C8.5,5.32 7.96,5.66 7.44,6.05L4.95,5.05C4.73,4.96 4.46,5.05 4.34,5.27L2.34,8.73C2.21,8.95 2.27,9.22 2.46,9.37L4.57,11C4.53,11.34 4.5,11.67 4.5,12C4.5,12.33 4.53,12.65 4.57,12.97L2.46,14.63C2.27,14.78 2.21,15.05 2.34,15.27L4.34,18.73C4.46,18.95 4.73,19.03 4.95,18.95L7.44,17.94C7.96,18.34 8.5,18.68 9.13,18.93L9.5,21.58C9.54,21.82 9.75,22 10,22H14C14.25,22 14.46,21.82 14.5,21.58L14.87,18.93C15.5,18.67 16.04,18.34 16.56,17.94L19.05,18.95C19.27,19.03 19.54,18.95 19.66,18.73L21.66,15.27C21.78,15.05 21.73,14.78 21.54,14.63L19.43,12.97Z"
                                                ></path>
                                            </svg>
                                        </span>
                                        <p
                                            class="nav-text ml-2 text-12 font-semibold text-text-primary-inverse uppercase tracking-wide-sm transition-all duration-300 ease-in-out"
                                        >
                                            Settings
                                        </p>
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="/admin/extras"
                                        class="lb-admin-main-nav-link {{ $activeRoute === 'extras' ? 'bg-[rgba(155,172,158,0.2)] rounded shadow-sm' : '' }}"
                                    >
                                        <span
                                            class="p-1 text-text-primary-inverse"
                                        >
                                            <svg
                                                class="material-design-icon__svg"
                                                width="20"
                                                height="20"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    d="M12,15.5A3.5,3.5 0 0,1 8.5,12A3.5,3.5 0 0,1 12,8.5A3.5,3.5 0 0,1 15.5,12A3.5,3.5 0 0,1 12,15.5M19.43,12.97C19.47,12.65 19.5,12.33 19.5,12C19.5,11.67 19.47,11.34 19.43,11L21.54,9.37C21.73,9.22 21.78,8.95 21.66,8.73L19.66,5.27C19.54,5.05 19.27,4.96 19.05,5.05L16.56,6.05C16.04,5.66 15.5,5.32 14.87,5.07L14.5,2.42C14.46,2.18 14.25,2 14,2H10C9.75,2 9.54,2.18 9.5,2.42L9.13,5.07C8.5,5.32 7.96,5.66 7.44,6.05L4.95,5.05C4.73,4.96 4.46,5.05 4.34,5.27L2.34,8.73C2.21,8.95 2.27,9.22 2.46,9.37L4.57,11C4.53,11.34 4.5,11.67 4.5,12C4.5,12.33 4.53,12.65 4.57,12.97L2.46,14.63C2.27,14.78 2.21,15.05 2.34,15.27L4.34,18.73C4.46,18.95 4.73,19.03 4.95,18.95L7.44,17.94C7.96,18.34 8.5,18.68 9.13,18.93L9.5,21.58C9.54,21.82 9.75,22 10,22H14C14.25,22 14.46,21.82 14.5,21.58L14.87,18.93C15.5,18.67 16.04,18.34 16.56,17.94L19.05,18.95C19.27,19.03 19.54,18.95 19.66,18.73L21.66,15.27C21.78,15.05 21.73,14.78 21.54,14.63L19.43,12.97Z"
                                                ></path>
                                            </svg>
                                        </span>
                                        <p
                                            class="nav-text ml-2 text-12 font-semibold text-text-primary-inverse uppercase tracking-wide-sm transition-all duration-300 ease-in-out"
                                        >
                                            Extras
                                        </p>
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="/admin/data-export"
                                        class="lb-admin-main-nav-link {{ $activeRoute === 'data-export' ? 'bg-[rgba(155,172,158,0.2)] rounded shadow-sm' : '' }}"
                                    >
                                        <span
                                            class="p-1 text-text-primary-inverse"
                                        >
                                            <svg
                                                class="material-design-icon__svg"
                                                width="20"
                                                height="20"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    d="M12,15.5A3.5,3.5 0 0,1 8.5,12A3.5,3.5 0 0,1 12,8.5A3.5,3.5 0 0,1 15.5,12A3.5,3.5 0 0,1 12,15.5M19.43,12.97C19.47,12.65 19.5,12.33 19.5,12C19.5,11.67 19.47,11.34 19.43,11L21.54,9.37C21.73,9.22 21.78,8.95 21.66,8.73L19.66,5.27C19.54,5.05 19.27,4.96 19.05,5.05L16.56,6.05C16.04,5.66 15.5,5.32 14.87,5.07L14.5,2.42C14.46,2.18 14.25,2 14,2H10C9.75,2 9.54,2.18 9.5,2.42L9.13,5.07C8.5,5.32 7.96,5.66 7.44,6.05L4.95,5.05C4.73,4.96 4.46,5.05 4.34,5.27L2.34,8.73C2.21,8.95 2.27,9.22 2.46,9.37L4.57,11C4.53,11.34 4.5,11.67 4.5,12C4.5,12.33 4.53,12.65 4.57,12.97L2.46,14.63C2.27,14.78 2.21,15.05 2.34,15.27L4.34,18.73C4.46,18.95 4.73,19.03 4.95,18.95L7.44,17.94C7.96,18.34 8.5,18.68 9.13,18.93L9.5,21.58C9.54,21.82 9.75,22 10,22H14C14.25,22 14.46,21.82 14.5,21.58L14.87,18.93C15.5,18.67 16.04,18.34 16.56,17.94L19.05,18.95C19.27,19.03 19.54,18.95 19.66,18.73L21.66,15.27C21.78,15.05 21.73,14.78 21.54,14.63L19.43,12.97Z"
                                                ></path>
                                            </svg>
                                        </span>
                                        <p
                                            class="nav-text ml-2 text-12 font-semibold text-text-primary-inverse uppercase tracking-wide-sm transition-all duration-300 ease-in-out"
                                        >
                                            Data Export
                                        </p>
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="/admin/email-templates"
                                        class="lb-admin-main-nav-link {{ $activeRoute === 'email-templates' ? 'bg-[rgba(155,172,158,0.2)] rounded shadow-sm' : '' }}"
                                    >
                                        <span
                                            class="p-1 text-text-primary-inverse"
                                        >
                                            <svg
                                                class="material-design-icon__svg"
                                                width="20"
                                                height="20"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    d="M12,15.5A3.5,3.5 0 0,1 8.5,12A3.5,3.5 0 0,1 12,8.5A3.5,3.5 0 0,1 15.5,12A3.5,3.5 0 0,1 12,15.5M19.43,12.97C19.47,12.65 19.5,12.33 19.5,12C19.5,11.67 19.47,11.34 19.43,11L21.54,9.37C21.73,9.22 21.78,8.95 21.66,8.73L19.66,5.27C19.54,5.05 19.27,4.96 19.05,5.05L16.56,6.05C16.04,5.66 15.5,5.32 14.87,5.07L14.5,2.42C14.46,2.18 14.25,2 14,2H10C9.75,2 9.54,2.18 9.5,2.42L9.13,5.07C8.5,5.32 7.96,5.66 7.44,6.05L4.95,5.05C4.73,4.96 4.46,5.05 4.34,5.27L2.34,8.73C2.21,8.95 2.27,9.22 2.46,9.37L4.57,11C4.53,11.34 4.5,11.67 4.5,12C4.5,12.33 4.53,12.65 4.57,12.97L2.46,14.63C2.27,14.78 2.21,15.05 2.34,15.27L4.34,18.73C4.46,18.95 4.73,19.03 4.95,18.95L7.44,17.94C7.96,18.34 8.5,18.68 9.13,18.93L9.5,21.58C9.54,21.82 9.75,22 10,22H14C14.25,22 14.46,21.82 14.5,21.58L14.87,18.93C15.5,18.67 16.04,18.34 16.56,17.94L19.05,18.95C19.27,19.03 19.54,18.95 19.66,18.73L21.66,15.27C21.78,15.05 21.73,14.78 21.54,14.63L19.43,12.97Z"
                                                ></path>
                                            </svg>
                                        </span>
                                        <p
                                            class="nav-text ml-2 text-12 font-semibold text-text-primary-inverse uppercase tracking-wide-sm transition-all duration-300 ease-in-out"
                                        >
                                            Email Templates
                                        </p>
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="/admin/registration-fields"
                                        class="lb-admin-main-nav-link {{ $activeRoute === 'registration-fields' ? 'bg-[rgba(155,172,158,0.2)] rounded shadow-sm' : '' }}"
                                    >
                                        <span
                                            class="p-1 text-text-primary-inverse"
                                        >
                                            <svg
                                                class="material-design-icon__svg"
                                                width="20"
                                                height="20"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    d="M12,15.5A3.5,3.5 0 0,1 8.5,12A3.5,3.5 0 0,1 12,8.5A3.5,3.5 0 0,1 15.5,12A3.5,3.5 0 0,1 12,15.5M19.43,12.97C19.47,12.65 19.5,12.33 19.5,12C19.5,11.67 19.47,11.34 19.43,11L21.54,9.37C21.73,9.22 21.78,8.95 21.66,8.73L19.66,5.27C19.54,5.05 19.27,4.96 19.05,5.05L16.56,6.05C16.04,5.66 15.5,5.32 14.87,5.07L14.5,2.42C14.46,2.18 14.25,2 14,2H10C9.75,2 9.54,2.18 9.5,2.42L9.13,5.07C8.5,5.32 7.96,5.66 7.44,6.05L4.95,5.05C4.73,4.96 4.46,5.05 4.34,5.27L2.34,8.73C2.21,8.95 2.27,9.22 2.46,9.37L4.57,11C4.53,11.34 4.5,11.67 4.5,12C4.5,12.33 4.53,12.65 4.57,12.97L2.46,14.63C2.27,14.78 2.21,15.05 2.34,15.27L4.34,18.73C4.46,18.95 4.73,19.03 4.95,18.95L7.44,17.94C7.96,18.34 8.5,18.68 9.13,18.93L9.5,21.58C9.54,21.82 9.75,22 10,22H14C14.25,22 14.46,21.82 14.5,21.58L14.87,18.93C15.5,18.67 16.04,18.34 16.56,17.94L19.05,18.95C19.27,19.03 19.54,18.95 19.66,18.73L21.66,15.27C21.78,15.05 21.73,14.78 21.54,14.63L19.43,12.97Z"
                                                ></path>
                                            </svg>
                                        </span>
                                        <p
                                            class="nav-text ml-2 text-12 font-semibold text-text-primary-inverse uppercase tracking-wide-sm transition-all duration-300 ease-in-out"
                                        >
                                            Registration Fields
                                        </p>
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="/admin/menu"
                                        class="lb-admin-main-nav-link {{ $activeRoute === 'menu' ? 'bg-[rgba(155,172,158,0.2)] rounded shadow-sm' : '' }}"
                                    >
                                        <span
                                            class="p-1 text-text-primary-inverse"
                                        >
                                            <svg
                                                class="material-design-icon__svg"
                                                width="20"
                                                height="20"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    d="M12,15.5A3.5,3.5 0 0,1 8.5,12A3.5,3.5 0 0,1 12,8.5A3.5,3.5 0 0,1 15.5,12A3.5,3.5 0 0,1 12,15.5M19.43,12.97C19.47,12.65 19.5,12.33 19.5,12C19.5,11.67 19.47,11.34 19.43,11L21.54,9.37C21.73,9.22 21.78,8.95 21.66,8.73L19.66,5.27C19.54,5.05 19.27,4.96 19.05,5.05L16.56,6.05C16.04,5.66 15.5,5.32 14.87,5.07L14.5,2.42C14.46,2.18 14.25,2 14,2H10C9.75,2 9.54,2.18 9.5,2.42L9.13,5.07C8.5,5.32 7.96,5.66 7.44,6.05L4.95,5.05C4.73,4.96 4.46,5.05 4.34,5.27L2.34,8.73C2.21,8.95 2.27,9.22 2.46,9.37L4.57,11C4.53,11.34 4.5,11.67 4.5,12C4.5,12.33 4.53,12.65 4.57,12.97L2.46,14.63C2.27,14.78 2.21,15.05 2.34,15.27L4.34,18.73C4.46,18.95 4.73,19.03 4.95,18.95L7.44,17.94C7.96,18.34 8.5,18.68 9.13,18.93L9.5,21.58C9.54,21.82 9.75,22 10,22H14C14.25,22 14.46,21.82 14.5,21.58L14.87,18.93C15.5,18.67 16.04,18.34 16.56,17.94L19.05,18.95C19.27,19.03 19.54,18.95 19.66,18.73L21.66,15.27C21.78,15.05 21.73,14.78 21.54,14.63L19.43,12.97Z"
                                                ></path>
                                            </svg>
                                        </span>
                                        <p
                                            class="nav-text ml-2 text-12 font-semibold text-text-primary-inverse uppercase tracking-wide-sm transition-all duration-300 ease-in-out"
                                        >
                                            Menu
                                        </p>
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="/admin/landing"
                                        class="lb-admin-main-nav-link {{ $activeRoute === 'landing' ? 'bg-[rgba(155,172,158,0.2)] rounded shadow-sm' : '' }}"
                                    >
                                        <span
                                            class="p-1 text-text-primary-inverse"
                                        >
                                            <svg
                                                class="material-design-icon__svg"
                                                width="20"
                                                height="20"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    d="M12,15.5A3.5,3.5 0 0,1 8.5,12A3.5,3.5 0 0,1 12,8.5A3.5,3.5 0 0,1 15.5,12A3.5,3.5 0 0,1 12,15.5M19.43,12.97C19.47,12.65 19.5,12.33 19.5,12C19.5,11.67 19.47,11.34 19.43,11L21.54,9.37C21.73,9.22 21.78,8.95 21.66,8.73L19.66,5.27C19.54,5.05 19.27,4.96 19.05,5.05L16.56,6.05C16.04,5.66 15.5,5.32 14.87,5.07L14.5,2.42C14.46,2.18 14.25,2 14,2H10C9.75,2 9.54,2.18 9.5,2.42L9.13,5.07C8.5,5.32 7.96,5.66 7.44,6.05L4.95,5.05C4.73,4.96 4.46,5.05 4.34,5.27L2.34,8.73C2.21,8.95 2.27,9.22 2.46,9.37L4.57,11C4.53,11.34 4.5,11.67 4.5,12C4.5,12.33 4.53,12.65 4.57,12.97L2.46,14.63C2.27,14.78 2.21,15.05 2.34,15.27L4.34,18.73C4.46,18.95 4.73,19.03 4.95,18.95L7.44,17.94C7.96,18.34 8.5,18.68 9.13,18.93L9.5,21.58C9.54,21.82 9.75,22 10,22H14C14.25,22 14.46,21.82 14.5,21.58L14.87,18.93C15.5,18.67 16.04,18.34 16.56,17.94L19.05,18.95C19.27,19.03 19.54,18.95 19.66,18.73L21.66,15.27C21.78,15.05 21.73,14.78 21.54,14.63L19.43,12.97Z"
                                                ></path>
                                            </svg>
                                        </span>
                                        <p
                                            class="nav-text ml-2 text-12 font-semibold text-text-primary-inverse uppercase tracking-wide-sm transition-all duration-300 ease-in-out"
                                        >
                                            Landing
                                        </p>
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="/admin/social-chat"
                                        class="lb-admin-main-nav-link {{ $activeRoute === 'social-chat' ? 'bg-[rgba(155,172,158,0.2)] rounded shadow-sm' : '' }}"
                                    >
                                        <span
                                            class="p-1 text-text-primary-inverse"
                                        >
                                            <svg
                                                class="material-design-icon__svg"
                                                width="20"
                                                height="20"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    d="M12,15.5A3.5,3.5 0 0,1 8.5,12A3.5,3.5 0 0,1 12,8.5A3.5,3.5 0 0,1 15.5,12A3.5,3.5 0 0,1 12,15.5M19.43,12.97C19.47,12.65 19.5,12.33 19.5,12C19.5,11.67 19.47,11.34 19.43,11L21.54,9.37C21.73,9.22 21.78,8.95 21.66,8.73L19.66,5.27C19.54,5.05 19.27,4.96 19.05,5.05L16.56,6.05C16.04,5.66 15.5,5.32 14.87,5.07L14.5,2.42C14.46,2.18 14.25,2 14,2H10C9.75,2 9.54,2.18 9.5,2.42L9.13,5.07C8.5,5.32 7.96,5.66 7.44,6.05L4.95,5.05C4.73,4.96 4.46,5.05 4.34,5.27L2.34,8.73C2.21,8.95 2.27,9.22 2.46,9.37L4.57,11C4.53,11.34 4.5,11.67 4.5,12C4.5,12.33 4.53,12.65 4.57,12.97L2.46,14.63C2.27,14.78 2.21,15.05 2.34,15.27L4.34,18.73C4.46,18.95 4.73,19.03 4.95,18.95L7.44,17.94C7.96,18.34 8.5,18.68 9.13,18.93L9.5,21.58C9.54,21.82 9.75,22 10,22H14C14.25,22 14.46,21.82 14.5,21.58L14.87,18.93C15.5,18.67 16.04,18.34 16.56,17.94L19.05,18.95C19.27,19.03 19.54,18.95 19.66,18.73L21.66,15.27C21.78,15.05 21.73,14.78 21.54,14.63L19.43,12.97Z"
                                                ></path>
                                            </svg>
                                        </span>
                                        <p
                                            class="nav-text ml-2 text-12 font-semibold text-text-primary-inverse uppercase tracking-wide-sm transition-all duration-300 ease-in-out"
                                        >
                                            Social Chat
                                        </p>
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="/admin/survey"
                                        class="lb-admin-main-nav-link {{ $activeRoute === 'survey' ? 'bg-[rgba(155,172,158,0.2)] rounded shadow-sm' : '' }}"
                                    >
                                        <span
                                            class="p-1 text-text-primary-inverse"
                                        >
                                            <svg
                                                class="material-design-icon__svg"
                                                width="20"
                                                height="20"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    d="M12,15.5A3.5,3.5 0 0,1 8.5,12A3.5,3.5 0 0,1 12,8.5A3.5,3.5 0 0,1 15.5,12A3.5,3.5 0 0,1 12,15.5M19.43,12.97C19.47,12.65 19.5,12.33 19.5,12C19.5,11.67 19.47,11.34 19.43,11L21.54,9.37C21.73,9.22 21.78,8.95 21.66,8.73L19.66,5.27C19.54,5.05 19.27,4.96 19.05,5.05L16.56,6.05C16.04,5.66 15.5,5.32 14.87,5.07L14.5,2.42C14.46,2.18 14.25,2 14,2H10C9.75,2 9.54,2.18 9.5,2.42L9.13,5.07C8.5,5.32 7.96,5.66 7.44,6.05L4.95,5.05C4.73,4.96 4.46,5.05 4.34,5.27L2.34,8.73C2.21,8.95 2.27,9.22 2.46,9.37L4.57,11C4.53,11.34 4.5,11.67 4.5,12C4.5,12.33 4.53,12.65 4.57,12.97L2.46,14.63C2.27,14.78 2.21,15.05 2.34,15.27L4.34,18.73C4.46,18.95 4.73,19.03 4.95,18.95L7.44,17.94C7.96,18.34 8.5,18.68 9.13,18.93L9.5,21.58C9.54,21.82 9.75,22 10,22H14C14.25,22 14.46,21.82 14.5,21.58L14.87,18.93C15.5,18.67 16.04,18.34 16.56,17.94L19.05,18.95C19.27,19.03 19.54,18.95 19.66,18.73L21.66,15.27C21.78,15.05 21.73,14.78 21.54,14.63L19.43,12.97Z"
                                                ></path>
                                            </svg>
                                        </span>
                                        <p
                                            class="nav-text ml-2 text-12 font-semibold text-text-primary-inverse uppercase tracking-wide-sm transition-all duration-300 ease-in-out"
                                        >
                                            Survey
                                        </p>
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="/admin/cta-cards"
                                        class="lb-admin-main-nav-link {{ $activeRoute === 'cta-cards' ? 'bg-[rgba(155,172,158,0.2)] rounded shadow-sm' : '' }}"
                                    >
                                        <span
                                            class="p-1 text-text-primary-inverse"
                                        >
                                            <svg
                                                class="material-design-icon__svg"
                                                width="20"
                                                height="20"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    d="M12,15.5A3.5,3.5 0 0,1 8.5,12A3.5,3.5 0 0,1 12,8.5A3.5,3.5 0 0,1 15.5,12A3.5,3.5 0 0,1 12,15.5M19.43,12.97C19.47,12.65 19.5,12.33 19.5,12C19.5,11.67 19.47,11.34 19.43,11L21.54,9.37C21.73,9.22 21.78,8.95 21.66,8.73L19.66,5.27C19.54,5.05 19.27,4.96 19.05,5.05L16.56,6.05C16.04,5.66 15.5,5.32 14.87,5.07L14.5,2.42C14.46,2.18 14.25,2 14,2H10C9.75,2 9.54,2.18 9.5,2.42L9.13,5.07C8.5,5.32 7.96,5.66 7.44,6.05L4.95,5.05C4.73,4.96 4.46,5.05 4.34,5.27L2.34,8.73C2.21,8.95 2.27,9.22 2.46,9.37L4.57,11C4.53,11.34 4.5,11.67 4.5,12C4.5,12.33 4.53,12.65 4.57,12.97L2.46,14.63C2.27,14.78 2.21,15.05 2.34,15.27L4.34,18.73C4.46,18.95 4.73,19.03 4.95,18.95L7.44,17.94C7.96,18.34 8.5,18.68 9.13,18.93L9.5,21.58C9.54,21.82 9.75,22 10,22H14C14.25,22 14.46,21.82 14.5,21.58L14.87,18.93C15.5,18.67 16.04,18.34 16.56,17.94L19.05,18.95C19.27,19.03 19.54,18.95 19.66,18.73L21.66,15.27C21.78,15.05 21.73,14.78 21.54,14.63L19.43,12.97Z"
                                                ></path>
                                            </svg>
                                        </span>
                                        <p
                                            class="nav-text ml-2 text-12 font-semibold text-text-primary-inverse uppercase tracking-wide-sm transition-all duration-300 ease-in-out"
                                        >
                                            CTA Cards
                                        </p>
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="/admin/theme"
                                        class="lb-admin-main-nav-link {{ $activeRoute === 'theme' ? 'bg-[rgba(155,172,158,0.2)] rounded shadow-sm' : '' }}"
                                    >
                                        <span
                                            class="p-1 text-text-primary-inverse"
                                        >
                                            <svg
                                                class="material-design-icon__svg"
                                                width="20"
                                                height="20"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    d="M12,15.5A3.5,3.5 0 0,1 8.5,12A3.5,3.5 0 0,1 12,8.5A3.5,3.5 0 0,1 15.5,12A3.5,3.5 0 0,1 12,15.5M19.43,12.97C19.47,12.65 19.5,12.33 19.5,12C19.5,11.67 19.47,11.34 19.43,11L21.54,9.37C21.73,9.22 21.78,8.95 21.66,8.73L19.66,5.27C19.54,5.05 19.27,4.96 19.05,5.05L16.56,6.05C16.04,5.66 15.5,5.32 14.87,5.07L14.5,2.42C14.46,2.18 14.25,2 14,2H10C9.75,2 9.54,2.18 9.5,2.42L9.13,5.07C8.5,5.32 7.96,5.66 7.44,6.05L4.95,5.05C4.73,4.96 4.46,5.05 4.34,5.27L2.34,8.73C2.21,8.95 2.27,9.22 2.46,9.37L4.57,11C4.53,11.34 4.5,11.67 4.5,12C4.5,12.33 4.53,12.65 4.57,12.97L2.46,14.63C2.27,14.78 2.21,15.05 2.34,15.27L4.34,18.73C4.46,18.95 4.73,19.03 4.95,18.95L7.44,17.94C7.96,18.34 8.5,18.68 9.13,18.93L9.5,21.58C9.54,21.82 9.75,22 10,22H14C14.25,22 14.46,21.82 14.5,21.58L14.87,18.93C15.5,18.67 16.04,18.34 16.56,17.94L19.05,18.95C19.27,19.03 19.54,18.95 19.66,18.73L21.66,15.27C21.78,15.05 21.73,14.78 21.54,14.63L19.43,12.97Z"
                                                ></path>
                                            </svg>
                                        </span>
                                        <p
                                            class="nav-text ml-2 text-12 font-semibold text-text-primary-inverse uppercase tracking-wide-sm transition-all duration-300 ease-in-out"
                                        >
                                            Theme
                                        </p>
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="/admin/account"
                                        class="lb-admin-main-nav-link {{ $activeRoute === 'account' ? 'bg-[rgba(155,172,158,0.2)] rounded shadow-sm' : '' }}"
                                    >
                                        <span
                                            class="p-1 text-text-primary-inverse"
                                        >
                                            <svg
                                                class="material-design-icon__svg"
                                                width="20"
                                                height="20"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    d="M12,15.5A3.5,3.5 0 0,1 8.5,12A3.5,3.5 0 0,1 12,8.5A3.5,3.5 0 0,1 15.5,12A3.5,3.5 0 0,1 12,15.5M19.43,12.97C19.47,12.65 19.5,12.33 19.5,12C19.5,11.67 19.47,11.34 19.43,11L21.54,9.37C21.73,9.22 21.78,8.95 21.66,8.73L19.66,5.27C19.54,5.05 19.27,4.96 19.05,5.05L16.56,6.05C16.04,5.66 15.5,5.32 14.87,5.07L14.5,2.42C14.46,2.18 14.25,2 14,2H10C9.75,2 9.54,2.18 9.5,2.42L9.13,5.07C8.5,5.32 7.96,5.66 7.44,6.05L4.95,5.05C4.73,4.96 4.46,5.05 4.34,5.27L2.34,8.73C2.21,8.95 2.27,9.22 2.46,9.37L4.57,11C4.53,11.34 4.5,11.67 4.5,12C4.5,12.33 4.53,12.65 4.57,12.97L2.46,14.63C2.27,14.78 2.21,15.05 2.34,15.27L4.34,18.73C4.46,18.95 4.73,19.03 4.95,18.95L7.44,17.94C7.96,18.34 8.5,18.68 9.13,18.93L9.5,21.58C9.54,21.82 9.75,22 10,22H14C14.25,22 14.46,21.82 14.5,21.58L14.87,18.93C15.5,18.67 16.04,18.34 16.56,17.94L19.05,18.95C19.27,19.03 19.54,18.95 19.66,18.73L21.66,15.27C21.78,15.05 21.73,14.78 21.54,14.63L19.43,12.97Z"
                                                ></path>
                                            </svg>
                                        </span>
                                        <p
                                            class="nav-text ml-2 text-12 font-semibold text-text-primary-inverse uppercase tracking-wide-sm transition-all duration-300 ease-in-out"
                                        >
                                            Account
                                        </p>
                                    </a>
                                </li>

                                <!-- Log Out -->
                                <li>
                                    <button
                                        type="button"
                                        aria-label="Log Out"
                                        class="mt-5 w-full !justify-start !px-2 text-text-primary-inverse relative items-center inline-flex leading-[normal] py-2 rounded-md bg-transparent hover:bg-white/10 transition duration-200 ease-in-out cursor-pointer overflow-hidden select-none"
                                    >
                                        <span
                                            class="pi pi-sign-out mr-2"
                                        ></span>
                                        <span class="nav-text text-12 font-semibold transition-all duration-300 ease-in-out"
                                            >Log Out</span
                                        >
                                    </button>
                                </li>@endif
                            </ul>
                        </div>
                    </div>
                </nav>

                <style>
                    /* PrimeIcons Font Size Adjustment */
                    .pi {
                        font-size: 16px;
                    }
                    
                    /* Sidebar collapsed styles */
                    .sidebar-collapsed {
                        width: 60px !important;
                        min-width: 60px !important;
                        max-width: 60px !important;
                        overflow-x: hidden !important;
                    }
                    
                    .sidebar-collapsed .nav-text {
                        opacity: 0;
                        visibility: hidden;
                        width: 0;
                        overflow: hidden;
                        display: none;
                    }
                    
                    .sidebar-collapsed #logo-section {
                        display: none;
                    }
                    
                    .sidebar-collapsed #cta-section {
                        opacity: 0;
                        visibility: hidden;
                        height: 0;
                        overflow: hidden;
                        margin: 0;
                        padding: 0;
                        display: none;
                    }
                    
                    .sidebar-collapsed .lb-admin-man-nav-section-heading {
                        opacity: 0;
                        visibility: hidden;
                        height: 0;
                        overflow: hidden;
                        margin: 0;
                        display: none;
                    }
                    
                    .sidebar-collapsed .lb-admin-main-nav-link {
                        justify-content: center;
                        padding: 8px 2px !important;
                        width: 100%;
                        display: flex;
                        align-items: center;
                    }
                    
                    .sidebar-collapsed .lb-admin-main-nav-link span {
                        margin: 0 !important;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        width: 100%;
                    }
                    
                    .sidebar-collapsed .lb-admin-main-nav-link span svg {
                        margin: 0 auto;
                        width: 28px !important;
                        height: 28px !important;
                    }
                    
                    .sidebar-collapsed .lb-admin-main-nav-link div {
                        margin: 0 !important;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        width: 100%;
                    }
                    
                    .sidebar-collapsed .lb-admin-main-nav-link div i {
                        margin: 0 auto;
                        font-size: 28px !important;
                    }
                    
                    /* Fix PrimeIcons specific size issues */
                    .sidebar-collapsed .pi {
                        font-size: 28px !important;
                    }
                    
                    .sidebar-collapsed .lb-admin-main-nav-link div svg {
                        width: 28px !important;
                        height: 28px !important;
                    }
                    
                    /* Make menu and logout icons consistent and properly aligned */
                    .sidebar-collapsed #close-icon,
                    .sidebar-collapsed #menu-icon {
                        width: 28px !important;
                        height: 28px !important;
                    }
                    
                    .sidebar-collapsed #sidebar-toggle {
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        width: 100%;
                        padding: 8px 2px !important;
                        position: relative;
                        right: auto !important;
                        left: 0 !important;
                    }
                    
                    /* Hide overflow in sidebar */
                    #admin-sidebar {
                        overflow-x: hidden;
                    }
                    
                    /* Ensure no horizontal scroll */
                    body {
                        overflow-x: hidden;
                    }
                </style>
                
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const sidebar = document.getElementById('admin-sidebar');
                        const toggle = document.getElementById('sidebar-toggle');
                        const closeIcon = document.getElementById('close-icon');
                        const menuIcon = document.getElementById('menu-icon');
                        const logoSection = document.getElementById('logo-section');
                        const ctaSection = document.getElementById('cta-section');
                        const navSection = document.getElementById('nav-section');
                        
                        let isCollapsed = false;
                        
                        toggle.addEventListener('click', function() {
                            isCollapsed = !isCollapsed;
                            
                            if (isCollapsed) {
                                sidebar.classList.add('sidebar-collapsed');
                                closeIcon.classList.add('hidden');
                                menuIcon.classList.remove('hidden');
                            } else {
                                sidebar.classList.remove('sidebar-collapsed');
                                closeIcon.classList.remove('hidden');
                                menuIcon.classList.add('hidden');
                            }
                        });
                    });
                </script>

                <!--- contenido de las vistas ---->

                @yield('content')
                
                <!--- fin contenido de las vistas ---->
            </main>
        </div>
        
        @include('partials.logout-modal')
        @yield('scripts')
    </body>
</html>
