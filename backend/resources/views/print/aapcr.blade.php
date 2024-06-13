<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <title>AAPCR</title>
    <style>
        body {
            font-size: 11px;
            font-family: Arial, Helvetica, sans-serif;
        }

        #useplogo {
            width: 100px;
        }

        table {
            width: 100%;
        }

        #tb1 tr td,
        #tb2 tr td,
        #tb3 tr td,
        #tb4 tr td,
        #tb5 tr td {
            border: 1px solid black;
            width: 100%;
        }

        #tb3 tr td {
            text-align: center;
            font-weight: bold;
        }

        #tb4 tr td {
            border: 1px solid black;
            width: 100%;

        }
    </style>
</head>

<body>
    {{-- <div class="container"> --}}
    <table>
        <thead>
            <tr>
                <td rowspan="5">
                    <img src="{{ public_path('images/usep-logo.png')}}" id="useplogo" class="useplogo" alt="USeP-Logo">
                </td>
            </tr>
            <tr>
                <td>University of Southeastern Philippines</td>
            </tr>
            <tr>
                <td><strong>FY 2023 AGENCY ANNUAL PERFORMANCE COMMITMENT AND REVIEW (AAPCR) FORM</strong></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>
                    <p>
                        The University of Southeastern Philippines (USeP) commits to deliver and agree to be rated on the attainment of the following targets
                        inaccordance with the indicated measures for the period January-December {{$aapcr['year']}}
                    </p>
                </td>
            </tr>
        </thead>
    </table>

    <table id="tb1">
        <tbody>
            <tr>
                <td><strong>Prepapred by:</strong></td>
                <td><strong>Reviewed by:</strong></td>
                <td><strong>Approved by:</strong></td>
            </tr>
            {{-- E-sig --}}
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <!-- {{-- Name --}} -->
            <tr>
                <td>
                    <strong>{{ $aapcr['params']['preparedBy'] }}</strong>&nbsp;
                </td>
                <td>
                    <strong>{{ $aapcr['params']['reviewedBy'] }}</strong>&nbsp;
                </td>
                <td>
                    <strong>{{ $aapcr['params']['approvedBy'] }}</strong>&nbsp;
                </td>
            </tr>
            {{-- Position --}}
            <tr>
                <td>
                    {{ $aapcr['params']['preparedByPosition'] }}&nbsp;
                </td>
                <td>
                    {{ $aapcr['params']['reviewedByPosition'] }}&nbsp;
                </td>
                <td>
                    {{ $aapcr['params']['approvingPosition'] }}&nbsp;
                </td>
            </tr>
            {{-- Date --}}
            <tr>
                <td>Date: <u>{{ $aapcr['params']['preparedDate'] }}&nbsp;</u></td>
                <td>Date: <u>{{ $aapcr['params']['reviewedDate'] }}&nbsp;</u></td>
                <td>Date: <u>{{ $aapcr['params']['approvedDate'] }}&nbsp;</u></td>
            </tr>
        </tbody>
    </table>

    <table id="tb2">
        <tr>
            <td>
                <span style="text-transform: uppercase;"><b>important:</b></span>
                <br>
                1. Respective OPCRs of offices and CPCRs must be based on the Catch Up Plan of the University.
            </td>
        </tr>
    </table>

    <table id="tb3">
        <tr style="border:1px;background-color: #c4c3fa;">
            <td rowspan="3" width="21%">Performance Indicators</td>
            <td colspan="2" width="15%">SUCCESS INDICATORS</td>
            <td rowspan="2" width="7%">Allocated Budget</td>
            <td rowspan="3" width="11%">Targets Basis</td>
            <td colspan="2" width="16%">Office/s Accountable</td>
            <td rowspan="3" width="25%">Other Remarks</td>
            <td colspan="3" width="20%">Accomplishment</td>

        </tr>
        <tr style="border:1px;background-color: #c4c3fa;">
            <td>Target (per Fiscal Year)</td>
            <td rowspan="2">Measures</td>
            <td rowspan="2">Implementing Office</td>
            <td rowspan="2">Support Office</td>
            <td rowspan="2" width="33%">Actual</td>
            <td rowspan="2" width="33%">Score</td>
            <td rowspan="2" width="33%">Rating</td>
        </tr>
        <tr style="border:1px;background-color: #c4c3fa;">
            <td> {{$aapcr['year']}}</td>
            <td>(in Php '000')</td>
        </tr>
        @foreach ($aapcr['jsonArrayData']['main'] as $coreFunction => $programs)
        <tr>
            <td colspan="11" style="text-align:left;background-color:#b38666;"><strong>{{ $coreFunction }}</strong></td>
        </tr>
        @foreach ($programs as $programKey => $programData)
        @if (is_array($programData) && isset($programData['program']))
        <tr>
            <td colspan="3" width="33%" style="text-align:left">
                &nbsp;&nbsp;{{ $programData['program'] }}
            </td>
            <td name="totalAllocatedBalance">
                {{ isset($programData['total_budget']) ? number_format($programData['total_budget'], 2) : '' }}
            </td>
            <td colspan="7"></td>
        </tr>
        @if (isset($programData['parent_indicator']))
        <tr>
            <td colspan="2" style="text-align:left;border-right:none;background-color:#f6dcf7;">
                &nbsp;&nbsp; &nbsp;{{ $programData['parent_indicator']['pi_name'] }}
            </td>
            <td colspan="9" style="border-left:none;text-align:left;background-color:#f6dcf7;"></td>

        </tr>
        @if (isset($programData['childDetails']))
        @foreach ($programData['childDetails'] as $subCategoryId => $childDetailGroup)

        <tr>
            <td colspan="11" style="text-align:left;background-color:#e5e3e6;">
                &nbsp;&nbsp;&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;{{ $childDetailGroup['sub_category_name'] }}
            </td>
        </tr>
        @php
        $counter = 1;
        @endphp
        @foreach ($childDetailGroup['details'] as $childDetail)
        <tr>
            <td colspan="1">
                &nbsp;&nbsp;&nbsp;{{ $counter }}.){{ $childDetail['pi_name'] }}
            </td>

            <td>{{ $childDetail['target'] }}</td>
            <td>
                {{ implode(', ', $childDetail['measures']) }}
            </td>
            <td></td>
            <td>{{ $childDetail['target_basis'] }}</td>


            <td colspan="1">
                @foreach ($childDetail['implementing_offices'] as $office)
                {{ $office['office_name'] }}@if (!$loop->last), @endif
                @endforeach
            </td>
            <td colspan="1">
                @foreach ($childDetail['support_offices'] as $office)
                {{ $office['office_name'] }}@if (!$loop->last), @endif
                @endforeach
            </td>
            <td>{{ $childDetail['other_remarks'] }}</td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        @php
        $counter++;
        @endphp
        @endforeach
        @endforeach
        @endif
        @endif
        @endif
        @endforeach
        @endforeach
        <tr>
            <td colspan="1" style="  text-align: left; ">
                &nbsp;&nbsp;&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;Total Budget
            </td>
            <td colspan="1" style="  text-align: left;">
            </td>
            <td colspan="1" style="  text-align: left;">

            </td>
            <td colspan="1" style="  text-align: left;">
                amount here
            </td>
            <td colspan="1" style="  text-align: left;">
            </td>
            <td colspan="1" style="  text-align: left;">
            </td>
            <td colspan="1" style="  text-align: left;">
            </td>
            <td colspan="1" style="  text-align: left;">
            </td>
            <td colspan="1" style="  text-align: left;">
            </td>
            <td colspan="1" style="  text-align: left;">
            </td>
            <td colspan="1" style="  text-align: left;">
            </td>
        </tr>
    </table>
    <table id="tb4">
        <tr>
            <td colspan="10" style="text-align: center;background-color:#85a3f0;"><strong>OVER-ALL RATING</strong> </td>
            <td rowspan="1" style="text-align: center;background-color:#85a3f0;"><strong>RATING</strong> </td>
        </tr>
        @php
        $groupedData = [];
        foreach ($aapcr['jsonArrayData']['programsDataSet'] as $program) {
        $categoryName = $program['categoryName'];
        if (!isset($groupedData[$categoryName])) {
        $groupedData[$categoryName] = [
        'percentage' => $program['categoryPercentage'],
        'programs' => []
        ];
        }
        $groupedData[$categoryName]['programs'][] = $program['programName'];
        }
        @endphp
        @foreach ($groupedData as $category => $data)
        <tr style="background-color:#aaec92;">
            <td colspan="1" style="border-right: none; background-color:#aaec92;"><strong>{{ $category }} ({{ $data['percentage'] }}%)</strong></td>
            <td colspan="1" style="border:none ; border-bottom: 1px solid black;">&nbsp;</td>
            <td colspan="1" style="border:none ; border-bottom: 1px solid black;">&nbsp;</td>
            <td colspan="1" style="border:none ;border-bottom: 1px solid black;">&nbsp;</td>
            <td colspan="1" style="border:none ;border-bottom: 1px solid black;">&nbsp;</td>
            <td colspan="1" style="border:none ;border-bottom: 1px solid black;">&nbsp;</td>
            <td colspan="1" style="border:none ;border-bottom: 1px solid black;">&nbsp;</td>
            <td colspan="1" style="border:none ;border-bottom: 1px solid black;">&nbsp;</td>
            <td colspan="1" style="border:none ;border-bottom: 1px solid black;">&nbsp;</td>
            <td colspan="1" style="border:none ;border-bottom: 1px solid black;">&nbsp;</td>
            <td colspan="1" style="background-color:white;">&nbsp;</td>
        </tr>
        @foreach ($data['programs'] as $program)
        <tr>
            <td colspan="10">{{ $program }}</td>
            <td colspan="1">&nbsp;</td>
        </tr>
        @endforeach
        @endforeach

    </table>
    <table id="tb4">
        <tr style="background-color:#b5cbf7;">
            <td colspan="1" style="border:none ;border-bottom: 1px solid black;border-left: 1px solid black;"></td>
            <td colspan="1" style="border:none ; border-bottom: 1px solid black;">&nbsp;</td>
            <td colspan="1" style="border:none ; border-bottom: 1px solid black;">&nbsp;</td>
            <td colspan="1" style="border:none ;border-bottom: 1px solid black;">&nbsp;</td>
            <td colspan="1" style="border:none ;border-bottom: 1px solid black;">&nbsp;</td>
            <td colspan="1" style="border:none ;border-bottom: 1px solid black;">&nbsp;</td>
            <td colspan="1" style="border:none ;border-bottom: 1px solid black;">&nbsp;</td>
            <td colspan="1" style="border:none ;border-bottom: 1px solid black;">&nbsp;</td>
            <td colspan="1" style="border:none ;border-bottom: 1px solid black;">&nbsp;</td>
            <td colspan="1" style="text-align:right;border:none;border-bottom: 1px solid black;"><strong>Over-All Rating&nbsp;</strong></td>
            <td colspan="1" style="border: 1px solid black;">&nbsp;</td>
        </tr>
        <tr style="background-color:#b5cbf7;">
            <td colspan="1" style="border:none ; border-bottom: 1px solid black;border-left: 1px solid black;"></td>
            <td colspan="1" style="border:none ; border-bottom: 1px solid black;">&nbsp;</td>
            <td colspan="1" style="border:none ; border-bottom: 1px solid black;">&nbsp;</td>
            <td colspan="1" style="border:none ;border-bottom: 1px solid black;">&nbsp;</td>
            <td colspan="1" style="border:none ;border-bottom: 1px solid black;">&nbsp;</td>
            <td colspan="1" style="border:none ;border-bottom: 1px solid black;">&nbsp;</td>
            <td colspan="1" style="border:none ;border-bottom: 1px solid black;">&nbsp;</td>
            <td colspan="1" style="border:none ;border-bottom: 1px solid black;">&nbsp;</td>
            <td colspan="1" style="border:none ;border-bottom: 1px solid black;">&nbsp;</td>
            <td colspan="1" style="text-align:right;border:none ;border-bottom: 1px solid black;"><strong>Descriptive Equivalent&nbsp;</strong></td>
            <td colspan="1" style="border: 1px solid black;">&nbsp;</td>

        </tr>
        <tr>
            <td colspan="11">
                NOTE: This area will only be signed after consolidation and rating is done by the end of the rating period.
            </td>
        </tr>
    </table>
    <table id="tb5">
        <tbody>
            <tr>
                <td><strong>Assessed by:</strong></td>
                <td><strong>Reviewed by:</strong></td>
                <td><strong>Approved by:</strong></td>
            </tr>
            {{-- E-sig --}}
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <!-- {{-- Name --}} -->
            <tr>
                <td>
                    <strong>{{ $aapcr['params']['preparedBy'] }}</strong>&nbsp;
                </td>
                <td>
                    <strong>{{ $aapcr['params']['reviewedBy'] }}</strong>&nbsp;
                </td>
                <td>
                    <strong>{{ $aapcr['params']['approvedBy'] }}</strong>&nbsp;
                </td>
            </tr>
            {{-- Position --}}
            <tr>
                <td>
                    {{ $aapcr['params']['preparedByPosition'] }}&nbsp;
                </td>
                <td>
                    {{ $aapcr['params']['reviewedByPosition'] }}&nbsp;
                </td>
                <td>
                    {{ $aapcr['params']['approvingPosition'] }}&nbsp;
                </td>
            </tr>
            {{-- Date --}}
            <tr>
                <td>Date: <u>{{ $aapcr['params']['preparedDate'] }}&nbsp;</u></td>
                <td>Date: <u>{{ $aapcr['params']['reviewedDate'] }}&nbsp;</u></td>
                <td>Date: <u>{{ $aapcr['params']['approvedDate'] }}&nbsp;</u></td>
            </tr>
        </tbody>
    </table>



</html>