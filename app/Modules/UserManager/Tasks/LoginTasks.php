<?php

namespace App\Modules\UserManager\Tasks;

use App\Contracts\BaseTasks;
use App\Modules\UserManager\Models\UserModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginTasks extends BaseTasks{

    public function checkIfUserExists()
    {
        if( ! isset($this->item['email']) ){
            throw ValidationException::withMessages(['email' => 'Email field is required']);
        }

        $user = UserModel::firstWhere('email', $this->item['email']);

        if( ! $user ){
            throw new ModelNotFoundException("User does not exist");
        }

        return new static([ ...$this->item, 'user' => $user ]);
    }


    public function login()
    {
        if( ! isset($this->item['user']) && ! isset($this->item['password']) ){
            throw ValidationException::withMessages(['credentials' => 'Please provide both email and password fields']);
        }

        if( ! Hash::check($this->item['password'], $this->item['user']->password) ){
            throw ValidationException::withMessages(['credentials' => 'The provided credentials is invalid']);
        }

        $user =  $this->item['user'];

        Auth::login($user);

        $token = $user->createToken('auth', ['view_auth'])->plainTextToken;

        Cookie::queue('token', $token);

        $url = url()->current();

        if( $user->is_teacher ){

            $url = url('/teacher/dashboard');
        }

        elseif($user->role->role_name === 'capture'){
            
            $url = url('/students/create');
        }
        else {
            
            $url = url('/assessments');
        }

        return new static([ ...$this->item, 'token' =>   $token, 'url' => $url ]);
    }
    
}