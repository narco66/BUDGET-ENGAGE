@extends('pdf.layout')

@section('title', $report['title'])

@php $pdfGeneratedAt = $generatedAt->format('d/m/Y H:i'); @endphp

@section('subtitle')
    {{ __('Module Liquidation') }} — {{ count($report['rows']) }} {{ __('ligne(s)') }}
@endsection

@section('content')
    @include('pdf.partials.report-table', ['report' => $report])
@endsection

@section('signatures')
@endsection
