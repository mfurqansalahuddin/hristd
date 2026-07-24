@if (session('success'))
    <div class="mb-6">
        <x-ui.alert variant="success" :message="session('success')" />
    </div>
@endif
