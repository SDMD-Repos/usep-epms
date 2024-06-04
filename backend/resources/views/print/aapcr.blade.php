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
        #tb1 tr td,#tb2 tr td,#tb3 tr td,#tb4 tr td,#tb5 tr td {
            border: 1px solid black;
            width: 100%;
        }
        #tb3 tr td{
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>
<body>
    {{-- <div class="container"> --}}
        <table style="border: none; !important;">
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
                            inaccordance with the indicated measures for the period January-December 2023 
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
                {{-- Name --}}
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
            <tr >
                <td>
                    <span style="text-transform: uppercase;"><b>important:</b></span>
                    <br>
                    1. Respective OPCRs of offices and CPCRs must be based on the Catch Up Plan of the University.
                </td>
            </tr>
        </table>

        <table id="tb3">
            <tr>
                <td rowspan="3" width="21%">Performance Indicators</td>
                <td colspan="2" width="15%">SUCCESS INDICATORS</td>
                <td rowspan="2" width="7%">Allocated Budget</td>
                <td rowspan="3" width="11%">Targets Basis</td>
                <td colspan="2" width="16%">Office/s Accountable</td>
                <td rowspan="3" width="16%">Other Remarks</td>
                {{-- <td colspan="3" rows style="border-bottom: none;">Accomplishment</td> --}}
                <td colspan="3" rowspan="2" width="14%">Accomplishment</td>
            </tr>
            <tr>
                <td>Target (per Fiscal Year)</td>
                <td rowspan="2">Measures</td>
                <td rowspan=2>Implmenting Office</td>
                <td rowspan=2>Support Office</td>
            </tr>
            <tr>
                <td>
                    2023
                </td>
                <td>
                    (in Php '000')
                </td>
                <td>Actual</td>
                <td>Score</td>
                <td>Rating</td>
            </tr>
            <tr>
                <td colspan="11" style="text-align: left; font-weight:bold;">
                    I. CORE FUNCTIONS
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    HIGHER EDUCATION PROGRAM
                </td>
                <td>1,385,028</td>
                <td colspan="7">&nbsp;</td>
            </tr>
            <tr>
                <td colspan="11" style="text-align:left;">
                    PREXC indicators
                </td>
            </tr>
            <tr>
                <td>
                    Outcome indicators
                </td>
                <td colspan="10"></td>
            </tr>
            <tr>
                <td>
                    1. Percentage of first-time licensure exam-takers that pass the licensure exams
                </td>
                <td>
                    75%
                </td>
                <td>
                    Measure 2a
                </td>
                <td>&nbsp;</td>
                <td>
                    Budget Proposal-Form B
                </td>
                <td>
                    OVPAA
                </td>
                <td>
                    CED, CTET, CARS, COE, CT
                </td>
                <td>
                    <span>
                        For colleges with board courses.; Target percentage is 75% but the numerator and denominator will change since majority of the board examinations in the FY 2020 and FY 2021 were postponed due to covid19.
                    </span>
                </td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>

            <tr>
                <td>
                    Output indicators
                </td>
                <td colspan="10">&nbsp;</td>
            </tr>

            <tr>
                <td>
                    1. Percentage of undergraduate student population enrolled in CHED-identified and RDC-identified priority programs
                </td>
                <td>
                    95% (7880/8295)
                </td>
                <td>
                    Measure 2a
                </td>
                <td>&nbsp;</td>
                <td>
                    Budget Proposal- Form B
                </td>
                <td>
                    OVPAA
                </td>
                <td>
                    Colleges and OUR
                </td>
                <td>
                    Timeframe:1st sem of SY 2022-2023
                </td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>

            <tr>
                <td>LOCALLY-FUNDED PROJECTS</td>
                <td colspan="10">&nbsp;</td>
            </tr>
            <tr>
                <td>
                    Percentage of completion of the following infrastructure projects according to timeline:
                </td>
                <td colspan="10">&nbsp;</td>
            </tr>
            <tr>
                <td>
                    Completion of the Administrative Building in USeP Obrero Campus (Phase 3 of 3)
                </td>
                <td>
                    100%
                </td>
                <td>
                    Measure 3
                </td>
                <td>
                    90,000
                </td>
                <td>
                    Budget Proposal- Form 202
                </td>
                <td>
                    VPAD, PDD
                </td>
                <td>
                    IPD, Finance, BAC
                </td>
                <td>
                    MOA with DPWH
                </td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>

            <tr>
                <td>TOTAL BUDGET</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>2,008.212</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td colspan="11" style="text-align:center;">
                    OVER-ALL RATING
                </td>
            </tr>
        </table>

        <table id="tb4">
            <tr>
                <td>
                    I. CORE FUNCTIONS (80%)
                </td>
                <td>
                    RATING
                </td>
            </tr>
            <tr>
                <td>Higher Education Program</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>Advanced Education Program</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>Research Program</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>Technical Advisory and Advanced Program</td>
                <td>&nbsp;</td>
            </tr>

            <tr>
                <td>
                    II. SUPPORT FUNCTIONS (20%)
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>Support to Operations</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>
                    General Administration and Support
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td style="text-align: right">TOTAL Over-All Rating</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td style="text-align: right">Descriptive Equivalent</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td colspan="2">
                    NOTE: This area will only be signed after consolidation and rating is done by the end of rating period.
                </td>
            </tr>
        </table>

        <table id="tb5">
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
                {{-- Name --}}
                <tr>
                    <td>
                        <strong>DAISY T. BESING</strong>
                    </td>
                    <td>
                        <strong>TAMARA CHER R. MERCADO</strong>
                    </td>
                    <td>
                        <strong>LOURDES C. GENERALAO</strong>
                    </td>
                </tr>
                {{-- Position --}}
                <tr>
                    <td>
                        Director, Institutional Planning Division
                    </td>
                    <td>
                        Chair, Performance Management Team
                    </td>
                    <td>
                        President
                    </td>
                </tr>
                {{-- Date --}}
                <tr>
                    <td>Date: <u>11 February 2021</u></td>
                    <td>Date: <u>11 February 2021</u></td>
                    <td>Date: <u>11 February 2021</u></td>
                </tr>
            </tbody>
        </table>
        {{-- <div class="row">
            <div class="col-2">
                <img src="{{ asset('images\usep-logo.png')}}" id="useplogo" class="useplogo" alt="USeP-Logo">
            </div>
        </div> --}}
    {{-- </div> --}}
</body>
</html>