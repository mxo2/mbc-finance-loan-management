@extends('layouts.app')
@section('page-title')
    {{ __('Dashboard') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item" aria-current="page">{{ __('Dashboard') }}</li>
@endsection
@push('script-page')
    {{-- <script>
        var options = {
            chart: {
                type: 'bar',
                height: 400,
                toolbar: {
                    show: false
                }
            },
            colors: ['#2ca58d', '#0a2342'],
            dataLabels: {
                enabled: false
            },
            legend: {
                show: true,
                position: 'top'
            },
            markers: {
                size: 1,
                colors: ['#fff', '#fff', '#fff'],
                strokeColors: ['#2ca58d', '#0a2342'],
                strokeWidth: 1,
                shape: 'circle',
                hover: {
                    size: 4
                }
            },
            stroke: {
                width: 2,
                curve: 'smooth'
            },
            fill: {
                // type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    type: 'vertical',
                    inverseColors: false,
                    opacityFrom: 0.5,
                    opacityTo: 0
                }
            },
            grid: {
                show: false
            },
            series: [{
                    name: "{{ __('Amount') }}",
                    data: {!! json_encode($result['paymentByMonth']['repayment']) !!}
                },

            ],
            xaxis: {
                categories: {!! json_encode($result['paymentByMonth']['label']) !!},
                tooltip: {
                    enabled: false
                },
                labels: {
                    hideOverlappingLabels: true
                },
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false
                }
            }
        };
        var chart = new ApexCharts(document.querySelector('#repayment'), options);
        chart.render();


    </script> --}}

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @foreach ($result['loanDetails'] as $index => $loan)
                var options_pie_chart_{{ $index }} = {
                    chart: {
                        height: 320,
                        type: 'pie'
                    },
                    labels: ['Paid', 'Pending'],
                    series: [{{ $loan['paid'] }}, {{ $loan['pending'] }}],
                    colors: ['#04A9F5', '#F44236'], // blue = paid, red = pending
                    legend: {
                        show: true,
                        position: 'bottom'
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function(val, opts) {
                            return val.toFixed(1) + '%';
                        },
                        dropShadow: {
                            enabled: false
                        }
                    },
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }]
                };

                var chart_pie_chart_{{ $index }} = new ApexCharts(document.querySelector(
                    "#pie-chart-{{ $index }}"), options_pie_chart_{{ $index }});
                chart_pie_chart_{{ $index }}.render();
            @endforeach
        });
    </script>
@endpush
@section('content')
    <div class="row">
        @foreach ($result['loans'] as $loan)
            <div class="col-12 mb-3">
                <div class="alert alert-danger d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between shadow-sm border-0"
                    style="border-left: 5px solid #dc3545; background-color: #fff0f0;">

                    <div class="d-flex flex-column flex-md-row w-100 align-items-md-center justify-content-between">
                        <div class="d-flex align-items-center me-3 mb-2 mb-md-0">
                            <i data-feather="alert-triangle" class="text-danger me-2" width="18" height="18"></i>
                            <span class="fw-semibold text-danger me-2">Upcoming EMI Alert</span>
                        </div>
                        <div class="d-flex flex-wrap fz-md gap-3 mb-2 mb-md-0">
                            <div><strong>{{ __('Loan ID') }} :</strong> {{ loanPrefix() . $loan->Loans->loan_id }}</div>
                            <div><strong>{{ __('Due Date') }} :</strong>
                                {{ \Carbon\Carbon::parse($loan->due_date)->format('d M Y') }}</div>
                            <div><strong>{{ __('Total Amount') }} :</strong> ₹{{ number_format($loan->total_amount, 2) }}
                            </div>
                        </div>

                        <div class="text-end">

                            <a href="{{ route('schedule.payment', encrypt($loan->id)) }}" class="btn btn-sm btn-danger">
                                <i data-feather="credit-card" class="me-1"></i> {{ __('Pay Now') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row">
        @foreach ($result['loanDetails'] as $index => $loan)
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Loan') }}: {{ loanPrefix() . $loan['loan'] }}</h5>
                        <small><b>{{ __('Total Loan') }} </b>: {{ priceFormat($loan['totalAmount']) }}</small>&nbsp;&nbsp;
                        <small><b>{{ __('Total Interest') }} </b>: {{ priceFormat($loan['total']- $loan['totalAmount']) }}</small>&nbsp;&nbsp;
                        <small><b>{{ __('Total Amount') }} </b>: {{ priceFormat($loan['total']) }}</small>
                    </div>
                    <div class="card-body">
                        <div id="pie-chart-{{ $index }}" style="width: 100%"></div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>



@endsection
