<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 *
 */
class UserController extends ApiController
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Bad request!', $validator->messages()->toArray());
        }

        $error = false;

        /** @var User $user */
        $user = User::where('email', $request->get('email'))->first();
        if (!$user) {
            $error = true;
        } else {
            if (!Hash::check($request->get('password'), $user->password)) {
                $error = true;
            }
        }

        if ($error) {
            return $this->sendError('Bad credentials!');
        }

        $token = $user->createToken('Login');

        return $this->sendResponse([
            'token' => $token->plainTextToken,
            'user' => $user->toArray()
        ]);
    }

    /**
     * @param request
     * @return JsonResponse
     */
    public function register (Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:50',
            'email' => 'required|unique:users,email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Bad request!', $validator->messages()->toArray());
        }

        $name = $request->get('name');
        $email = $request->get('email');
        $password = $request->get('password');

        $user = new User();
        $user->name = $name;
        $user->email = $email;
        $user->password = Hash::make($password);
        $user->save();

        $token = $user->createToken('Register');

        return $this->sendResponse([
            'response' => 'User Added to Database',
            'token' => $token->plainTextToken,
            'user' => $user->toArray()
        ]);
    }
}
