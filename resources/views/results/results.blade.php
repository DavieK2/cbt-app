<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Assessment Results</title>
    @vite(['resources/css/app.css'])  
</head>
<body>
    <div class="flex flex-col mt-8 pt-8 items-center min-h-screen w-screen">
        <div class="space-y-8 border-2 rounded-lg p-8">
            <p class="font-extrabold text-2xl p-8">{{ $title }}</p>
            
            <div class="p-8">
                <form action="{{  url("/results/assessments/$assessmentId") }}" method="post">
                    @csrf
                    <div class="flex space-x-3">
                        <select class="border rounded-lg p-2.5 w-full" name="class_id" id="">
                            @foreach ($assessment_classes as $class)
                                <option value="{{ $class['uuid'] }}">{{ $class['class_name'] }}</option>
                            @endforeach
                        </select>
                        <select class="border rounded-lg p-2.5 w-full" name="subject_id" id="">
                            @foreach ($assessment_subjects as $subject)
                                <option value="{{ $subject['subjectId'] }}">{{ $subject['subjectName'] }} ( {{ $subject['subjectCode'] }} )</option>
                            @endforeach
                        </select>
                        <button class="bg-gray-900 hover:bg-gray-700 text-white rounded-lg p-2.5 w-full" type="submit">Get Results</button>
                    </div>
                </form>
            </div>
            
        </div>
    </div>
</body>
</html>