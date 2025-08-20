@extends('layouts.app')

@section('title', 'جلسات المستخدمين')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">جلسات دخول النظام</h2>

    <table class="table table-striped table-bordered">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>المستخدم</th>
                <th>عنوان IP</th>
                <th>الجهاز / المتصفح</th>
                <th>آخر نشاط</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sessions as $session)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $session->user_name }}</td>
                    <td>{{ $session->ip_address }}</td>
                    <td>
                        @php
                            $agent = new Jenssegers\Agent\Agent();
                            $agent->setUserAgent($session->user_agent);
                        @endphp
                        {{ $agent->platform() ?? 'Unknown OS' }} - {{ $agent->browser() ?? 'Unknown Browser' }}
                    </td>
                    <td>{{ $session->last_activity }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
