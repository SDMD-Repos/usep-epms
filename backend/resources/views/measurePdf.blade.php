<!DOCTYPE html>
<html>
    <head>
        <style>
            table, th, td {
                font-size: 10px;
                border: 0.5px solid black;
                border-collapse: collapse;
            }

            th, td {
                padding: 5px;
            }

            .pdfHeader {
                background-color: #d0cece;
                font-weight: bold;
                font-size: 20px !important;
            }

            .textAlignCenter {
                margin: auto;
                text-align: center;
            }

            .colRating {
                background-color: #d0cece;
            }
        </style>
    </head>
    <body>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th colspan="{{ $tableColumnCount }}" class="pdfHeader textAlignCenter">
                      <div>RATING SCALE</div>
                    </th>
                </tr>
                <tr>
                    <th class="textAlignCenter" rowspan="2" style="width: 70px">Numerical Rating</th>
                    @foreach($measures as $m)
                        <th colspan="{{ count($m->categories) }}" style="background-color: {{ $m->bg_color }}">
                            <div class="textAlignCenter">{{ $m->name . " (" . $m->description . ")" }}</div>
                        </th>
                    @endforeach
                    <th class="textAlignCenter" rowspan="2" colspan="3">Rating Equivalent</th>
                </tr>
                <tr>
                    @foreach($measures as $m)
                        <th class="textAlignCenter" colspan="{{ count($m->categories) }}" style="background-color: {{ $m->bg_color }}">
                            {{ $m->variable_equivalent }}
                        </th>
                    @endforeach
                </tr>
                <tr>
                    <th class="textAlignCenter">Elements</th>
                    @foreach($measures as $m)
                        <th colspan="{{ count($m->categories) }}" style="background-color: {{ $m->bg_color }}"><p>{{ $m->elements }}</p></th>
                    @endforeach
                    <th class="textAlignCenter" rowspan="2" style="width: 86px">Average Point Score</th>
                    <th class="textAlignCenter" rowspan="2" style="width: 88px">Adjectival Rating</th>
                    <th class="textAlignCenter" rowspan="2" style="width: 151px">Description</th>
                </tr>
                <tr>
                    <th class="textAlignCenter" >Category</th>
                    @foreach($measures as $m)
                        @foreach($m->categories as $c)
                            <th style="background-color: {{ $m->bg_color }}">{{ $c->numbering ? $c->numbering . ". " . $c->name : $c->name }}</th>
                        @endforeach
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($ratings as $r)
                    <tr>
                        <td class="colRating textAlignCenter">{{ $r->numerical_rating }}</td>
                        @foreach($measures as $m)
                            @foreach($m->categories as $c)
                                <td style="background-color: {{ $m->bg_color }}">
                                    @foreach($c->items as $i)
                                        @if($i->rating === $r->id)
                                            <span>{{ $i->description }}</span>
                                        @endif
                                    @endforeach
                                </td>
                            @endforeach
                        @endforeach
                        <td >{{ $r->aps_from . " - " . $r->aps_to }}</td>
                        <td>{{ $r->adjectival_rating }}</td>
                        <td>{{ $r->description }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </body>
</html>
