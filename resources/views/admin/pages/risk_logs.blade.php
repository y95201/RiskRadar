@extends('layouts.app')

@section('content')
    <div class="owl-iframe-page">
        <h1>巡检日志</h1>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            function reportHeight() {
                window.parent.postMessage({
                    type: 'owl:iframe-height',
                    height: document.documentElement.scrollHeight || document.body.scrollHeight
                }, window.location.origin);
            }

            window.addEventListener('load', reportHeight);
            window.addEventListener('resize', reportHeight);
            setTimeout(reportHeight, 300);
        })();
    </script>
@endpush
