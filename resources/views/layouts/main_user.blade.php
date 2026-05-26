<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <title>Sidebar Navigation</title>
        <link href="https://fonts.googleapis.com" rel="preconnect" />
        <link
            crossorigin=""
            href="https://fonts.gstatic.com"
            rel="preconnect"
        />
        <link
            href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;family=Inter+Tight:wght@500;600;700&amp;display=swap"
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
                                "Inter",
                                "system-ui",
                                "-apple-system",
                                "sans-serif",
                            ],
                            secondary: ["Inter", "sans-serif"],
                            display:  ['"Inter Tight"', "Inter", "sans-serif"],
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
                    id="user-sidebar"
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
                            <div class="flex items-center gap-3 px-3 py-2.5 rounded-xl bg-white/5 border border-white/10">
                                <span class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0" style="background:#5c7c68">
                                    <span class="block w-6 h-6">
                                        <img src="{{ asset('images/brand/makai-logo-mark.svg') }}" alt="" class="block w-full h-full">
                                    </span>
                                </span>
                                <div class="flex-1 min-w-0 leading-none">
                                    <div class="text-[14px] font-bold text-white tracking-tight">MAKAI</div>
                                    <div class="text-[9px] font-semibold text-white/70 tracking-[0.18em] uppercase mt-1">Duna Development</div>
                                </div>
                            </div>
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
                                <!-- Main Section -->
                                <li>
                                    <a
                                        href="/dashboard"
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

                                <!-- Portal Cliente Section -->
                                <li
                                    class="lb-admin-man-nav-section-heading text-text-primary-inverse !mt-6"
                                >
                                    Portal Cliente
                                </li>
                                <li>
                                    <a
                                        href="/dashboard/progress"
                                        class="lb-admin-main-nav-link {{ $activeRoute === 'progreso' ? 'bg-[rgba(155,172,158,0.2)] rounded shadow-sm' : '' }}"
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
                                                    d="M13,9H18.5L13,3.5V9M6,2H14L20,8V20A2,2 0 0,1 18,22H6C4.89,22 4,21.1 4,20V4C4,2.89 4.89,2 6,2M15,18V16H6V18H15M18,14V12H6V14H18Z"
                                                ></path>
                                            </svg>
                                        </span>
                                        <p
                                            class="nav-text ml-2 text-12 font-semibold text-text-primary-inverse uppercase tracking-wide-sm transition-all duration-300 ease-in-out"
                                        >
                                            Progreso
                                        </p>
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="/dashboard/documents"
                                        class="lb-admin-main-nav-link {{ $activeRoute === 'documents' ? 'bg-[rgba(155,172,158,0.2)] rounded shadow-sm' : '' }}"
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
                                                    d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"
                                                ></path>
                                            </svg>
                                        </span>
                                        <p
                                            class="nav-text ml-2 text-12 font-semibold text-text-primary-inverse uppercase tracking-wide-sm transition-all duration-300 ease-in-out"
                                        >
                                            Documentos
                                        </p>
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="/dashboard/payments"
                                        class="lb-admin-main-nav-link {{ $activeRoute === 'payments' ? 'bg-[rgba(155,172,158,0.2)] rounded shadow-sm' : '' }}"
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
                                                    d="M20,4H4C2.89,4 2,4.89 2,6V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V6C22,4.89 21.1,4 20,4M20,18H4V12H20V18M20,8H4V6H20V8Z"
                                                ></path>
                                            </svg>
                                        </span>
                                        <p
                                            class="nav-text ml-2 text-12 font-semibold text-text-primary-inverse uppercase tracking-wide-sm transition-all duration-300 ease-in-out"
                                        >
                                            Pagos
                                        </p>
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="/dashboard/messages"
                                        class="lb-admin-main-nav-link {{ $activeRoute === 'messages' ? 'bg-[rgba(155,172,158,0.2)] rounded shadow-sm' : '' }}"
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
                                                    d="M20,2H4A2,2 0 0,0 2,4V22L6,18H20A2,2 0 0,0 22,16V4A2,2 0 0,0 20,2M20,16H6L4,18V4H20V16Z"
                                                ></path>
                                            </svg>
                                        </span>
                                        <p
                                            class="nav-text ml-2 text-12 font-semibold text-text-primary-inverse uppercase tracking-wide-sm transition-all duration-300 ease-in-out"
                                        >
                                            Mensajes
                                        </p>
                                    </a>
                                </li>

                                <li class="pt-4 mt-4 border-t border-gray-700">
                                    <form method="POST" action="/logout" class="block" data-logout-confirm>
                                        @csrf
                                        <button type="submit" class="lb-admin-main-nav-link w-full text-left flex items-center gap-3 px-4 py-3 text-gray-300 hover:text-white hover:bg-gray-700 rounded-lg transition-colors">
                                            <svg class="material-design-icon__svg text-text-primary-inverse" width="20" height="20" viewBox="0 0 24 24">
                                                <path d="M16,17V14H9V10H16V7L21,12L16,17M14,2A2,2 0 0,1 16,4V6H14V4H5V20H14V18H16V20A2,2 0 0,1 14,22H5A2,2 0 0,1 3,20V4A2,2 0 0,1 5,2H14Z"></path>
                                            </svg>
                                            <p class="nav-text text-sm font-medium transition-all duration-300 ease-in-out">
                                                Cerrar sesión
                                            </p>
                                        </button>
                                    </form>
                                </li>
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
                    #user-sidebar {
                        overflow-x: hidden;
                    }
                    
                    /* Ensure no horizontal scroll */
                    body {
                        overflow-x: hidden;
                    }
                </style>
                
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const sidebar = document.getElementById('user-sidebar');
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
    </body>
</html>
