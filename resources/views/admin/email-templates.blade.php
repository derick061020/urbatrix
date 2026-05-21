@extends('layouts.main_admin')

@section('title', 'Email Templates - Admin Panel')

@php
    $activeRoute = 'email-templates';
@endphp


@section('content')
<div class="bg-gray-100 min-h-screen w-full">
    <div class="p-8">

    <h1 class="text-3xl font-semibold text-[#5c4a32] mb-1">Email Templates</h1>
    <p class="text-gray-500 mb-6">Add and edit email templates.</p>
    <hr class="border-gray-300 mb-6">

    <div class="bg-white rounded-lg shadow overflow-hidden ">
        <table class="w-full text-sm text-left">
            <thead>
                <tr class="border-b border-gray-200 text-xs text-gray-600 uppercase bg-gray-50">
                    <th class="py-3 px-6 font-semibold">Edit</th>
                    <th class="py-3 px-6 font-semibold">Disabled</th>
                    <th class="py-3 px-6 font-semibold">Name</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <tr class="hover:bg-gray-50">
                    <td class="py-3 px-6">
                        <button type="button"
                            class="inline-flex items-center justify-center w-10 h-9 rounded-full bg-transparent text-[#667b6a] hover:bg-[#667b6a]/10 transition duration-200 cursor-pointer">
                            <i class="pi pi-pencil"></i>
                        </button>
                    </td>
                    <td class="py-3 px-6"><input type="checkbox" class="accent-[#5c6b4a]"></td>
                    <td class="py-3 px-6 text-gray-700">contact-email</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="py-3 px-6">
                        <button type="button"
                            class="inline-flex items-center justify-center w-10 h-9 rounded-full bg-transparent text-[#667b6a] hover:bg-[#667b6a]/10 transition duration-200 cursor-pointer">
                            <i class="pi pi-pencil"></i>
                        </button>
                    </td>
                    <td class="py-3 px-6"><input type="checkbox" checked class="accent-[#5c6b4a]"></td>
                    <td class="py-3 px-6 text-gray-700">contact-email-user</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="py-3 px-6">
                        <button type="button"
                            class="inline-flex items-center justify-center w-10 h-9 rounded-full bg-transparent text-[#667b6a] hover:bg-[#667b6a]/10 transition duration-200 cursor-pointer">
                            <i class="pi pi-pencil"></i>
                        </button>
                    </td>
                    <td class="py-3 px-6"><input type="checkbox" class="accent-[#5c6b4a]"></td>
                    <td class="py-3 px-6 text-gray-700">forgot-password</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="py-3 px-6">
                        <button type="button"
                            class="inline-flex items-center justify-center w-10 h-9 rounded-full bg-transparent text-[#667b6a] hover:bg-[#667b6a]/10 transition duration-200 cursor-pointer">
                            <i class="pi pi-pencil"></i>
                        </button>
                    </td>
                    <td class="py-3 px-6"><input type="checkbox" class="accent-[#5c6b4a]"></td>
                    <td class="py-3 px-6 text-gray-700">no-available-units-email</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="py-3 px-6">
                        <button type="button"
                            class="inline-flex items-center justify-center w-10 h-9 rounded-full bg-transparent text-[#667b6a] hover:bg-[#667b6a]/10 transition duration-200 cursor-pointer">
                            <i class="pi pi-pencil"></i>
                        </button>
                    </td>
                    <td class="py-3 px-6"><input type="checkbox" class="accent-[#5c6b4a]"></td>
                    <td class="py-3 px-6 text-gray-700">shortlist-email</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="py-3 px-6">
                        <button type="button"
                            class="inline-flex items-center justify-center w-10 h-9 rounded-full bg-transparent text-[#667b6a] hover:bg-[#667b6a]/10 transition duration-200 cursor-pointer">
                            <i class="pi pi-pencil"></i>
                        </button>
                    </td>
                    <td class="py-3 px-6"><input type="checkbox" class="accent-[#5c6b4a]"></td>
                    <td class="py-3 px-6 text-gray-700">unit-pending-backoffice</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="py-3 px-6">
                        <button type="button"
                            class="inline-flex items-center justify-center w-10 h-9 rounded-full bg-transparent text-[#667b6a] hover:bg-[#667b6a]/10 transition duration-200 cursor-pointer">
                            <i class="pi pi-pencil"></i>
                        </button>
                    </td>
                    <td class="py-3 px-6"><input type="checkbox" checked class="accent-[#5c6b4a]"></td>
                    <td class="py-3 px-6 text-gray-700">unit-reserved</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="py-3 px-6">
                        <button type="button"
                            class="inline-flex items-center justify-center w-10 h-9 rounded-full bg-transparent text-[#667b6a] hover:bg-[#667b6a]/10 transition duration-200 cursor-pointer">
                            <i class="pi pi-pencil"></i>
                        </button>
                    </td>
                    <td class="py-3 px-6"><input type="checkbox" class="accent-[#5c6b4a]"></td>
                    <td class="py-3 px-6 text-gray-700">unit-reserved-backoffice</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="py-3 px-6">
                        <button type="button"
                            class="inline-flex items-center justify-center w-10 h-9 rounded-full bg-transparent text-[#667b6a] hover:bg-[#667b6a]/10 transition duration-200 cursor-pointer">
                            <i class="pi pi-pencil"></i>
                        </button>
                    </td>
                    <td class="py-3 px-6"><input type="checkbox" class="accent-[#5c6b4a]"></td>
                    <td class="py-3 px-6 text-gray-700">user-detail-survey-backoffice</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="py-3 px-6">
                        <button type="button"
                            class="inline-flex items-center justify-center w-10 h-9 rounded-full bg-transparent text-[#667b6a] hover:bg-[#667b6a]/10 transition duration-200 cursor-pointer">
                            <i class="pi pi-pencil"></i>
                        </button>
                    </td>
                    <td class="py-3 px-6"><input type="checkbox" checked class="accent-[#5c6b4a]"></td>
                    <td class="py-3 px-6 text-gray-700">user-shortlist-email</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="py-3 px-6">
                        <button type="button"
                            class="inline-flex items-center justify-center w-10 h-9 rounded-full bg-transparent text-[#667b6a] hover:bg-[#667b6a]/10 transition duration-200 cursor-pointer">
                            <i class="pi pi-pencil"></i>
                        </button>
                    </td>
                    <td class="py-3 px-6"><input type="checkbox" checked class="accent-[#5c6b4a]"></td>
                    <td class="py-3 px-6 text-gray-700">user-sign-up</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="py-3 px-6">
                        <button type="button"
                            class="inline-flex items-center justify-center w-10 h-9 rounded-full bg-transparent text-[#667b6a] hover:bg-[#667b6a]/10 transition duration-200 cursor-pointer">
                            <i class="pi pi-pencil"></i>
                        </button>
                    </td>
                    <td class="py-3 px-6"><input type="checkbox" class="accent-[#5c6b4a]"></td>
                    <td class="py-3 px-6 text-gray-700">users-sign-up-backoffice</td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

</div>
@endsection