<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Assessment Results</title>
    @vite(['resources/css/app.css'])  

    <style>
        table{
            border-collapse: collapse;
            margin: 20px;
            padding: 12px
        }
        tr,th{
            padding: 12px
        }
        tr,td{
            padding: 12px
        }
    </style>
</head>
<body>
    <div class="min-h-screen w-screen p-8">
        <div>
            <p class="flex text-center justify-center w-full uppercase text-2xl font-bold">{{ $title }} Student Results</p>
            <div class="mt-6">
                <table class="min-w-full">
                    <thead class="text-left">
                       <tr>
                            <th>S/N</th>
                            <th>STUDENT NAME</th>
                            <th>REG NO</th>
                            <th>COURSE</th>
                            <th>TOTAL SCORE</th>
                            <th>GRADE</th>
                            <th>ACTION</th>
                       </tr>
                    </thead>
                    <tbody>
                        @foreach ($assessment_results as $index => $result)
                            <tr class="">
                                <td class="mr-3">{{ $index + 1 }}</td>
                                <td class="mr-3">{{ $result['STUDENT NAME'] }}</td>
                                <td class="mr-3">{{ $result['REG NO'] }}</td>
                                <td class="mr-3">{{ $result['COURSE'] }}</td>
                                <td class="mr-3">{{ $result['TOTAL SCORE'] }}</td>
                                <td class="mr-3">{{ $result['GRADE'] }}</td>
                                <td class="mr-3">
                                    <a class="text-blue-500 underline" href="{{ url("/student/exam/{$assessmentId}/{$subject_id}/{$result['studentId']}") }}">View Answer Sheet</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
        </div>
    </div>
</body>
</html>