<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        @foreach($links as $link)
            @php
                $label = is_array($link) ? $link['label'] : $link;
                $url = is_array($link) && array_key_exists('url', $link) ? $link['url'] : null;
            @endphp
            @if(!$loop->last && $url)
                <li class="breadcrumb-item">
                    <a href="{{ $url }}">{{ $label }}</a>
                </li>
            @else
                <li class="breadcrumb-item active" aria-current="page">{{ $label }}</li>
            @endif
        @endforeach
    </ol>
</nav> 