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
                        inaccordance with the indicated measures for the period January-December {{$vpopcr['year']}}
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
                    <strong>{{ $vpopcr['params']['preparedBy'] }}</strong>&nbsp;
                </td>
                <td>
                    <strong>{{ $vpopcr['params']['reviewedBy'] }}</strong>&nbsp;
                </td>
                <td>
                    <strong>{{ $vpopcr['params']['approvedBy'] }}</strong>&nbsp;
                </td>
            </tr>
            {{-- Position --}}
            <tr>
                <td>
                    {{ $vpopcr['params']['preparedByPosition'] }}&nbsp;
                </td>
                <td>
                    {{ $vpopcr['params']['reviewedByPosition'] }}&nbsp;
                </td>
                <td>
                    {{ $vpopcr['params']['approvingPosition'] }}&nbsp;
                </td>
            </tr>
            {{-- Date --}}
            <tr>
                <td>Date: <u>{{ $vpopcr['params']['preparedDate'] }}&nbsp;</u></td>
                <td>Date: <u>{{ $vpopcr['params']['reviewedDate'] }}&nbsp;</u></td>
                <td>Date: <u>{{ $vpopcr['params']['approvedDate'] }}&nbsp;</u></td>
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
            <td> {{$vpopcr['year']}}</td>
            <td>(in Php '000')</td>
        </tr>
        @php
        $coreFunctionDisplayed = false;
        @endphp

        @foreach ($vpopcr['jsonArrayData']['main'] as $coreFunctions)

        @foreach ($coreFunctions as $coreFunction => $programs)


        @if (!$coreFunctionDisplayed)
        <tr>
            <td colspan="11" style="text-align:left;background-color:#b38666;"><strong>{{ $coreFunction }}</strong></td>
        </tr>
        @php
        $coreFunctionDisplayed = true;
        @endphp
        @endif
        @foreach ($programs as $programs => $xx)
        <tr>
            <td colspan="11" style="text-align:left;background-color:#b38666;"><strong>{{ $xx['program'] }}</strong></td>
        </tr>
        @php


        @endphp
        @endforeach
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




</html>