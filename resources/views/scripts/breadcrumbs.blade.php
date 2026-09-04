<nav aria-label="Breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        @if(empty($breadcrumbItems ?? []))
            <li class="breadcrumb-item active" aria-current="page">Deployment &amp; Monitoring</li>
        @else
            <li class="breadcrumb-item"><a href="{{ route('deployment-scripts.index') }}">Deployment &amp; Monitoring</a></li>
        @endif
        @foreach($breadcrumbItems ?? [] as $item)
            @if($loop->last)
                <li class="breadcrumb-item active" aria-current="page">{{ $item['label'] }}</li>
            @else
                <li class="breadcrumb-item"><a href="{{ $item['url'] }}">{{ $item['label'] }}</a></li>
            @endif
        @endforeach
    </ol>
</nav>