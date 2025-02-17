<?php
namespace App\Http\Action;

use App\Models\User;

class createNewUser
{
    public function __construct()
    {
        //
    }

    public function createNewUser(){
        return [
            'newUser' => 'newUser',
        ];
    }
}