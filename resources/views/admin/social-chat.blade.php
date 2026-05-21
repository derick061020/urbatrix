@extends('layouts.main_admin')

@section('title', 'Social Chat - Admin Panel')

@php
    $activeRoute = 'social-chat';
@endphp

@php
$attendants = [
    [
        'name'        => 'Angel Ramirez',
        'label'       => 'Development Expert',
        'app'         => 'whatsapp',
        'phone_number'       => '+18097109044',
        'href'        => '-',
        'username'    => '-',
        'user_app_id' => '-',
        'disabled'    => false,
        'avatar'      => 'https://firebasestorage.googleapis.com/v0/b/makai-savyo.firebasestorage.app/o/images%2Fattendants%2Fab6e3c82-0a5a-4aa3-9e5c-f52b02b82e10.png?alt=media&token=72a08a87-3a6a-4bb8-9ae4-1655b900b400',
    ],
    [
        'name'        => 'Ernesto Rivas',
        'label'       => 'Development Expert',
        'app'         => 'whatsapp',
        'phone_number'       => '+18097271122',
        'href'        => '-',
        'username'    => '-',
        'user_app_id' => '-',
        'disabled'    => false,
        'avatar'      => 'https://firebasestorage.googleapis.com/v0/b/makai-savyo.firebasestorage.app/o/images%2Fattendants%2F6b301dff-0901-4959-9e55-6d0bb4ae415d.png?alt=media&token=4ce38092-a502-444b-a6c9-0463c21a9ba6',
    ],
    [
        'name'        => 'Maria Virginia',
        'label'       => 'Development Expert',
        'app'         => 'whatsapp',
        'phone_number'       => '+18096707043',
        'href'        => '-',
        'username'    => '-',
        'user_app_id' => '-',
        'disabled'    => false,
        'avatar'      => 'https://firebasestorage.googleapis.com/v0/b/makai-savyo.firebasestorage.app/o/images%2Fattendants%2F5b4575b4-633a-41cb-b038-08da46aaef3f.png?alt=media&token=f5bcb7fd-9df6-4411-b032-625af05417e0',
    ],
    [
        'name'        => 'Vanessa Garcia',
        'label'       => 'Development Expert',
        'app'         => 'whatsapp',
        'phone_number'       => '+18096738236',
        'href'        => '-',
        'username'    => '-',
        'user_app_id' => '-',
        'disabled'    => false,
        'avatar'      => 'https://firebasestorage.googleapis.com/v0/b/makai-savyo.firebasestorage.app/o/images%2Fattendants%2F8d96b8e7-c00c-475e-b2b0-592993835e99.png?alt=media&token=fef82b2a-2b66-4e9c-8608-3aa6d343520e',
    ],
];
$attendants = json_encode($attendants);
$attendants = json_decode($attendants);
@endphp

@section('content')
<div class="bg-gray-100 min-h-screen w-full">
    <div class="p-8">

        <h1 class="text-3xl font-semibold text-[#5c4a32] mb-1">Social Chat Settings</h1>
        <p class="text-gray-500 mb-6">Edit the Social Chat Settings interactive Social Chat list settings.</p>
        <hr class="border-gray-300 mb-6">

        {{-- Social Chat Platforms --}}
        <div class="mb-5">
            <label class="block text-sm text-gray-600 mb-1">Social Chat Platforms:</label>
            <select class="w-72 border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-700 bg-white focus:outline-none focus:ring-1 focus:ring-[#4a5240]">
                <option>WhatsApp</option>
            </select>
        </div>

        {{-- Chat Style / Header / Icon Src / Icon Alt --}}
        <div class="grid grid-cols-4 gap-4 mb-5">
            <div>
                <label class="block text-sm text-gray-600 mb-1">Chat Style:</label>
                <select class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-700 bg-white focus:outline-none focus:ring-1 focus:ring-[#4a5240]">
                    <option>WhatsApp Style</option>
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Header Message Platform:</label>
                <select class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-700 bg-white focus:outline-none focus:ring-1 focus:ring-[#4a5240]">
                    <option>WhatsApp</option>
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Icon Src:</label>
                <select class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-700 bg-white focus:outline-none focus:ring-1 focus:ring-[#4a5240]">
                    <option>WhatsApp Icon Src</option>
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Icon Alt:</label>
                <select class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-700 bg-white focus:outline-none focus:ring-1 focus:ring-[#4a5240]">
                    <option>WhatsApp Icon Alt</option>
                </select>
            </div>
        </div>

        {{-- Footer Details --}}
        <div class="mb-6">
            <label class="block text-sm text-gray-600 mb-1">Footer Details:</label>
            <select class="w-72 border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-700 bg-white focus:outline-none focus:ring-1 focus:ring-[#4a5240]">
                <option>No Footer</option>
            </select>
        </div>

        <hr class="border-gray-300 mb-6">

        {{-- Attendants Card --}}
        <div class="bg-white rounded-lg shadow p-5">

            {{-- Header --}}
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm text-gray-700">Attendants: <span class="font-bold">4</span></p>
                <button type="button"
                    class="px-4 py-2 bg-[#4a5240] text-white text-sm rounded-md hover:bg-[#3b4233] transition duration-200 cursor-pointer">
                    Add Attendant
                </button>
            </div>

            {{-- Search --}}
            <div class="relative w-64 mb-4">
                <input type="text" placeholder="Search by Attendant Name"
                    class="w-full border border-gray-300 rounded-md pl-3 pr-9 py-2 text-sm text-gray-600 focus:outline-none focus:ring-1 focus:ring-[#4a5240]">
                <i class="pi pi-search absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="border-b border-gray-200 text-xs text-gray-600 uppercase bg-gray-50">
                            <th class="py-3 px-4 font-semibold">Edit</th>
                            <th class="py-3 px-4 font-semibold">Disabled</th>
                            <th class="py-3 px-4 font-semibold">Avatar</th>
                            <th class="py-3 px-4 font-semibold">Name</th>
                            <th class="py-3 px-4 font-semibold">Label</th>
                            <th class="py-3 px-4 font-semibold">App</th>
                            <th class="py-3 px-4 font-semibold">Phone Number</th>
                            <th class="py-3 px-4 font-semibold">Href</th>
                            <th class="py-3 px-4 font-semibold">Username</th>
                            <th class="py-3 px-4 font-semibold">User App ID</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($attendants as $attendant)
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2">
                                    <button type="button" class="text-gray-400 hover:text-gray-600 cursor-pointer">
                                        <i class="pi pi-bars text-xs"></i>
                                    </button>
                                    <button type="button"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-full text-[#667b6a] hover:bg-[#667b6a]/10 transition duration-200 cursor-pointer">
                                        <i class="pi pi-pencil text-xs"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <input type="checkbox" class="accent-[#4a5240] w-4 h-4 cursor-pointer"
                                    {{ $attendant->disabled ? 'checked' : '' }}>
                            </td>
                            <td class="py-3 px-4">
                                <img src="{{ $attendant->avatar }}" alt="{{ $attendant->name }}"
                                    class="w-10 h-10 rounded-full object-cover">
                            </td>
                            <td class="py-3 px-4 text-gray-700">{{ $attendant->name }}</td>
                            <td class="py-3 px-4 text-gray-700">{{ $attendant->label }}</td>
                            <td class="py-3 px-4 text-gray-700">{{ $attendant->app }}</td>
                            <td class="py-3 px-4 text-gray-700">{{ $attendant->phone_number }}</td>
                            <td class="py-3 px-4 text-gray-500">{{ $attendant->href ?? '-' }}</td>
                            <td class="py-3 px-4 text-gray-500">{{ $attendant->username ?? '-' }}</td>
                            <td class="py-3 px-4 text-gray-500">{{ $attendant->user_app_id ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="flex items-center justify-center gap-2 mt-5">
                <button class="px-2 py-1 text-gray-400 hover:text-gray-600 text-sm">&laquo;</button>
                <button class="px-2 py-1 text-gray-400 hover:text-gray-600 text-sm">&lsaquo;</button>
                <span class="px-3 py-1 text-sm text-gray-700">1</span>
                <button class="px-2 py-1 text-gray-400 hover:text-gray-600 text-sm">&rsaquo;</button>
                <button class="px-2 py-1 text-gray-400 hover:text-gray-600 text-sm">&raquo;</button>
                <select class="border border-gray-300 rounded-md px-2 py-1 text-sm text-gray-700 bg-white focus:outline-none">
                    <option>10</option>
                    <option>25</option>
                    <option>50</option>
                </select>
            </div>

        </div>
        {{-- End Attendants Card --}}

        {{-- Save Button --}}
        <div class="flex justify-end mt-6">
            <button type="button"
                class="px-6 py-2 bg-[#4a5240] text-white text-sm rounded-md hover:bg-[#3b4233] transition duration-200 cursor-pointer">
                Save
            </button>
        </div>

    </div>
</div>
@endsection