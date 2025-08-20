<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AccountSettingsController extends Controller
{
    // عرض الصفحة
    public function index(Request $request)
    {
        $user = $request->user(); // المستخدم الحالي
        return view('account.settings', compact('user'));
    }

    // تحديث الاسم والإيميل
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();

        return redirect()->route('account.settings')->with('success', 'تم تحديث معلومات الحساب بنجاح.');
    }

    // تحديث كلمة المرور
    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_password' => ['required', function ($attribute, $value, $fail) use ($user) {
                if (!Hash::check($value, $user->password)) {
                    $fail('كلمة المرور الحالية غير صحيحة.');
                }
            }],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        if ($request->password) {
            $user->password = Hash::make($request->password);
            $user->save();
        }

        return redirect()->route('account.settings')->with('success', 'تم تحديث كلمة المرور بنجاح.');
    }
}
