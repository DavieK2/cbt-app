<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Results</title>


    <style>
        .rotated_text {
            writing-mode: vertical-lr;
            transform: rotate(-180deg)
        }

        th{
            padding: 16px;
            border: 2px solid
        }
        
        table{
            border-collapse: collapse;
            border: 3px solid
        }
    </style>
</head>
<body>
    <center>
        <h1>YEAR TWO NURSING EXAMS RESULT - OCTOBER 2021</h1>

        <table>
            <tr>
                <th>SN</th>
                <th>STUDENT NAME</th>
                <th >EXAM NO</th>
                @foreach ($subjects as $key => $value)
                    <th class="rotated_text" colspan="{{ count($assessments) + 1 }}">{{ $value }}</th>
                @endforeach
                <th>GPA</th>
            </tr>
            <tr>
                <th></th>
                <th></th>
                <th></th>
                @foreach ($subjects as $key => $value)
                    @foreach ($assessments as $index => $assessment)
                        <th class="rotated_text" >{{ $assessment }}</th>
                    @endforeach
                    <th class="rotated_text">TOTAL</th>
                @endforeach
                <th></th>
            </tr>
            @foreach ($results as $key => $result)
                <tr>
                    <th>{{ $key + 1 }}</th>
                    <th>{{ $result['student_name'] }}</th>
                    <th>{{ $result['exam_no'] }}</th>
                    @foreach ( $subjects as $index => $subject )
                        @foreach ($assessments as $assessment_index => $assessment)
                            <th>{{ $result['results'][$index]['results'][$assessment_index]['score'] }}</th>
                        @endforeach
                        <th>{{ $result['results'][$index]['total'] }}</th>
                    @endforeach
                    <th>{{ $result['gpa'] }}</th>
                </tr>
            @endforeach
        </table>
    </center>
   
</body>
</html>