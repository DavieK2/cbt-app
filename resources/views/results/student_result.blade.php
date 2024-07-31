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
    <div class="min-h-screen w-screen bg-red-300">
        <div>
            <p class="font-extrabold p-8 text-2xl">{{ $title }}</p>
            <div class="font-extrabold px-8">
                <p class="uppercase text-lg">Student Name: {{ $student->first_name }} {{ $student->surname }}</p>
            </div>
            <div class="space-y-8 p-8">
                
                @foreach ($sessions as $index => $session)
                    <div>
                        <p> <span class="font-bold">Question {{ $index + 1 }}:</span> {{ $session['question'] }}</p>
                        <div class="mt-2">
                            <ul class="list-disc">
                                @foreach ($session['options'] as $key => $option)
                                    <li> <span class="font-extrabold">-</span> {{ $option }}</li>
                                @endforeach
                            </ul>
                          
                        </div>

                        <div class="mt-4">
                            <p><span class="font-bold">Student Answer: </span>{{ $session['student_answer'] }} <span class="{{ $session['student_answer'] === $session['correct_answer'] ? 'text-green-500' : 'text-red-500' }}">( {{ $session['student_answer'] === $session['correct_answer'] ? 'Correct' : 'Wrong' }} )</span></p>
                            <p><span class="font-bold">Correct Answer: </span>{{ $session['correct_answer'] }}</p>
                        </div>
                    </div>
                    
                   
                @endforeach
            </div>
            
        </div>
    </div>
</body>
</html>