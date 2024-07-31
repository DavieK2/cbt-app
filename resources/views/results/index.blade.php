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
    <div class="min-h-screen w-screen bg-red-300">
        <div class="p-8">
            <p class="p-8 text-2xl font-extrabold">Assessments</p>
           
            <div class="border-2 p-4 rounded-lg">
                <table>
                    <thead class="text-left uppercase">
                        <th>S/N</th>
                        <th>Title</th>
                        <th>Action</th>
                    </thead>
                    <tbody>
                        @foreach ($assessments as $index => $assessment)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $assessment['title'] }}</td>
                                <td>
                                    <a class="text-blue-500" href="{{ url("/results/assessments/{$assessment['uuid']}") }}">View Results</a>
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