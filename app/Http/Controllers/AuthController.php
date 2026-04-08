<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6'
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password'])
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $user
        ]);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        /* if (!Auth::attempt($data)) {...} 的寫法等同如下
        
        $user = User::where('email', $data['email'])->first();
        if (!$user || !Hash::check($data['password'], $user->password)) {
            // fail
        }
        */
        if (!Auth::attempt($data)) { //Auth::attempt($data) 會使用 Hash::check() 來比對加密的 password 了，所以不用另外 verify password 了
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials'
            ], 401);
        }

        /** @var User|null $user */
        $user = Auth::user();

        if (! $user instanceof User) {
            return response()->json([
                'status' => 'error',
                'message' => 'Authenticated user not found'
            ], 401);
        }

        // 🔥 產生 token
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'token' => $token
        ]);
    }
}
