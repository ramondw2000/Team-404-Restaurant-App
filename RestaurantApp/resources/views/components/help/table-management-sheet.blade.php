@php
    $markdown = file_get_contents(resource_path('markdown/table-management-help.md'));
@endphp

<x-ui.sheet name="table-management-help" title="How to use Table Management" maxWidth="lg">
    <div class="prose prose-sm max-w-none prose-headings:text-gray-900 prose-a:text-blue-600 prose-strong:text-gray-900 prose-code:text-pink-600 prose-code:bg-gray-100 prose-code:px-1 prose-code:py-0.5 prose-code:rounded prose-code:before:content-none prose-code:after:content-none">
        @markdown($markdown)
    </div>
</x-ui.sheet>
