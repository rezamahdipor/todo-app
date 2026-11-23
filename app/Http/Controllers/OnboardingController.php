<?php

namespace App\Http\Controllers;

use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OnboardingController extends Controller
{
    private const SCHOOL = ['high', 'vocational'];
    private const UNI = ['associate', 'bachelor', 'master', 'phd'];

    public function show(Request $request)
    {
        $user = $request->user();

        return view('onboarding', [
            'profile' => UserProfile::firstOrNew(['user_id' => $user->id]),
            'editing' => $user->onboarded_at !== null,
            'levels'  => UserProfile::EDUCATION,
            'fields'  => UserProfile::FIELDS,
            'grades'  => UserProfile::GRADES,
            'sources' => UserProfile::REFERRAL,
            'goals'   => UserProfile::GOALS,
            'scale'   => UserProfile::ORGANIZED,
        ]);
    }

    public function save(Request $request)
    {
        $data = $this->validated($request);
        $user = $request->user();
        $first = $user->onboarded_at === null;

        UserProfile::updateOrCreate(['user_id' => $user->id], $data);

        if ($first) {
            $user->forceFill(['onboarded_at' => now()])->save();

            return redirect()->route('dashboard');
        }

        return redirect()->route('account')->with('status', 'مشخصات ذخیره شد ✅');
    }

    public function skip(Request $request)
    {
        $user = $request->user();

        if ($user->onboarded_at === null) {
            $user->forceFill(['onboarded_at' => now()])->save();
        }

        return redirect()->route('dashboard');
    }

    private function validated(Request $request): array
    {
        $edu = $request->input('education');

        $rules = [
            'education' => ['nullable', Rule::in(array_keys(UserProfile::EDUCATION))],
            'referral'  => ['nullable', Rule::in(array_keys(UserProfile::REFERRAL))],
            'organized' => ['nullable', 'integer', 'between:1,5'],
            'goals'     => ['nullable', 'array', 'max:6'],
            'goals.*'   => [Rule::in(array_keys(UserProfile::GOALS))],
            'field'     => ['nullable', 'string', 'max:60'],
            'grade'     => ['nullable'],
        ];

        // دبیرستان و هنرستان: رشته و پایه اجباریه
        if (is_string($edu) && in_array($edu, self::SCHOOL, true)) {
            $rules['field'] = ['required', Rule::in(array_keys(UserProfile::FIELDS[$edu]))];
            $rules['grade'] = ['required', Rule::in(array_keys(UserProfile::GRADES))];
        }

        $data = $request->validate($rules, [
            'field.required'     => 'رشته‌ی تحصیلی رو انتخاب کن.',
            'grade.required'     => 'پایه رو انتخاب کن.',
            'field.max'          => 'رشته‌ی تحصیلی حداکثر ۶۰ کاراکتر باشه.',
            'organized.between'  => 'امتیاز باید بین ۱ تا ۵ باشه.',
            'in'                 => 'مقدار انتخاب‌شده معتبر نیست.',
            'integer'            => 'مقدار معتبر نیست.',
            'array'              => 'مقدار معتبر نیست.',
            'max.array'          => 'حداکثر ۶ مورد انتخاب کن.',
        ]);

        $level = $data['education'] ?? null;

        if (! in_array($level, self::SCHOOL, true)) {
            $data['grade'] = null;

            if (! in_array($level, self::UNI, true)) {
                $data['field'] = null;
            }
        }

        if (isset($data['field'])) {
            $data['field'] = trim(strip_tags($data['field'])) ?: null;
        }

        $data['goals'] = array_values(array_unique($data['goals'] ?? []));
        $data['organized'] = isset($data['organized']) ? (int) $data['organized'] : null;

        return array_merge([
            'education' => null, 'field' => null, 'grade' => null,
            'referral' => null, 'organized' => null, 'goals' => [],
        ], $data);
    }
}