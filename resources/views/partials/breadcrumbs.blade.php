@if(isset($breadcrumbs) && count($breadcrumbs) > 0)
<style>
    .custom-itrac-breadcrumb {
        background-color: transparent !important;
        padding: 0 !important;
        margin-bottom: 0 !important;
    }
    .custom-itrac-breadcrumb .breadcrumb-item + .breadcrumb-item::before {
        content: ">" !important;
        color: #888ea8;
    }
    .custom-itrac-breadcrumb .breadcrumb-item a {
        color: #515365;
        text-decoration: none;
        font-weight: 500;
    }
    body.dark .custom-itrac-breadcrumb .breadcrumb-item a {
        color: #bfc9d4;
    }
    .custom-itrac-breadcrumb .breadcrumb-item a:hover {
        text-decoration: underline;
    }
    .custom-itrac-breadcrumb .breadcrumb-item.active {
        color: #8B0000 !important;
        font-weight: 600;
    }
    body.dark .custom-itrac-breadcrumb .breadcrumb-item.active {
        color: #e7515a !important;
    }
</style>
<div class="row mt-4 mb-3">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb custom-itrac-breadcrumb">
                @foreach($breadcrumbs as $breadcrumb)
                    @if($loop->last)
                        <li class="breadcrumb-item active" aria-current="page">
                            @if(isset($breadcrumb['icon']))
                                {!! $breadcrumb['icon'] !!}<span class="inner-text">{{ $breadcrumb['title'] }}</span>
                            @else
                                {{ $breadcrumb['title'] }}
                            @endif
                        </li>
                    @else
                        <li class="breadcrumb-item">
                            <a href="{{ $breadcrumb['url'] ?? '#' }}">
                                @if(isset($breadcrumb['icon']))
                                    {!! $breadcrumb['icon'] !!}<span class="inner-text">{{ $breadcrumb['title'] }}</span>
                                @else
                                    {{ $breadcrumb['title'] }}
                                @endif
                            </a>
                        </li>
                    @endif
                @endforeach
            </ol>
        </nav>
    </div>
</div>
@endif
