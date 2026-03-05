<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Shop;
use App\Models\Distributor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Register a new user (shop owner or distributor).
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'phone'      => 'required|string|unique:users,phone',
            'password'   => ['required', 'confirmed', Password::min(8)],
            'role'       => 'required|in:shop_owner,distributor',
            'locale'     => 'in:ar,fr,en',

            // Shop fields
            'shop_name'     => 'required_if:role,shop_owner|string|max:255',
            'shop_address'  => 'required_if:role,shop_owner|string',
            'shop_city'     => 'required_if:role,shop_owner|string',
            'shop_latitude' => 'required_if:role,shop_owner|numeric',
            'shop_longitude'=> 'required_if:role,shop_owner|numeric',

            // Distributor fields
            'company_name'     => 'required_if:role,distributor|string|max:255',
            'company_address'  => 'required_if:role,distributor|string',
            'company_city'     => 'required_if:role,distributor|string',
            'company_latitude' => 'required_if:role,distributor|numeric',
            'company_longitude'=> 'required_if:role,distributor|numeric',
            'service_radius_km'=> 'nullable|numeric|min:1|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'phone'    => $request->phone,
                'password' => Hash::make($request->password),
                'role'     => $request->role,
                'locale'   => $request->locale ?? 'fr',
            ]);

            // Create shop or distributor profile
            if ($request->role === 'shop_owner') {
                Shop::create([
                    'user_id'    => $user->id,
                    'shop_name'  => $request->shop_name,
                    'address'    => $request->shop_address,
                    'city'       => $request->shop_city,
                    'latitude'   => $request->shop_latitude,
                    'longitude'  => $request->shop_longitude,
                    'phone'      => $request->phone,
                ]);
                $user->load('shop');
            } elseif ($request->role === 'distributor') {
                Distributor::create([
                    'user_id'           => $user->id,
                    'company_name'      => $request->company_name,
                    'address'           => $request->company_address,
                    'city'              => $request->company_city,
                    'latitude'          => $request->company_latitude,
                    'longitude'         => $request->company_longitude,
                    'service_radius_km' => $request->service_radius_km ?? 50,
                    'phone'             => $request->phone,
                ]);
                $user->load('distributor');
            }

            return $user;
        });

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => __('auth.registered'),
            'data'    => [
                'user'  => $user,
                'token' => $token,
            ],
        ], 201);
    }

    /**
     * Login with email/phone + password.
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'login'    => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Find user by email or phone
        $user = User::where('email', $request->login)
            ->orWhere('phone', $request->login)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => __('auth.failed'),
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => __('auth.disabled'),
            ], 403);
        }

        // Load profile
        if ($user->isShopOwner()) $user->load('shop');
        if ($user->isDistributor()) $user->load('distributor');

        // Update FCM token if provided
        if ($request->has('fcm_token')) {
            $user->update(['fcm_token' => $request->fcm_token]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => __('auth.success'),
            'data'    => [
                'user'  => $user,
                'token' => $token,
            ],
        ]);
    }

    /**
     * Logout (revoke current token).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => __('auth.logged_out'),
        ]);
    }

    /**
     * Get authenticated user profile.
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isShopOwner()) $user->load('shop');
        if ($user->isDistributor()) $user->load('distributor');

        return response()->json([
            'success' => true,
            'data'    => $user,
        ]);
    }

    /**
     * Update profile.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name'   => 'sometimes|string|max:255',
            'phone'  => 'sometimes|string|unique:users,phone,' . $user->id,
            'locale' => 'sometimes|in:ar,fr,en',
            'avatar' => 'sometimes|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $request->only(['name', 'phone', 'locale']);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = $path;
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => __('profile.updated'),
            'data'    => $user->fresh(),
        ]);
    }

    /**
     * Update FCM token for push notifications.
     */
    public function updateFcmToken(Request $request): JsonResponse
    {
        $request->validate(['fcm_token' => 'required|string']);
        $request->user()->update(['fcm_token' => $request->fcm_token]);

        return response()->json(['success' => true]);
    }
}
