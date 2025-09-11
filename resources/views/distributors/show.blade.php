@extends('layouts.app')

@section('title', 'تفاصيل الموزع')

@section('content')
<div class="row">
    <div class="col-md-12">
        <h3>تفاصيل الموزع: {{ $distributor->name }}</h3>

        <div class="card mb-3">
            <div class="card-body">
                <p><strong>الهاتف:</strong> {{ $distributor->phone }}</p>
                <p><strong>النوع:</strong> {{ $distributor->type == 'distributor' ? 'موزع' : 'نقطة بيع' }}</p>
                <p><strong>المبلغ المتبقي:</strong> {{ number_format($stats['remaining_amount'], 2) }} ش.ج</p>
                <p><strong>إجمالي الكروت:</strong> {{ $stats['total_cards'] }}</p>
            </div>
        </div>

        <a href="{{ route('distributors.add-cards', $distributor) }}" class="btn btn-success mb-3">إضافة كروت</a>

        <h5>الكروت المتاحة</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>الخدمة</th>
                    <th>الكمية المتاحة</th>
                    <th>سعر الكرت</th>
                    <th>المبلغ المتبقي</th>
                </tr>
            </thead>
            <tbody>
                @foreach($distributor->distributorCards as $card)
                <tr>
                    <td>{{ $card->service->name_ar ?? $card->service->name }}</td>
                    <td>{{ $card->quantity_available }}</td>
                    <td>{{ number_format($card->card_price,2) }} ش.ج</td>
                    <td>{{ number_format($card->remaining_amount,2) }} ش.ج</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
