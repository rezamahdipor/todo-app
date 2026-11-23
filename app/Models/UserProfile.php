<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    public const EDUCATION = [
        'middle'     => 'متوسطه‌ی اول (هفتم تا نهم)',
        'high'       => 'دبیرستان (متوسطه‌ی دوم)',
        'vocational' => 'هنرستان / فنی‌وحرفه‌ای',
        'diploma'    => 'دیپلم (فارغ‌التحصیل)',
        'associate'  => 'کاردانی',
        'bachelor'   => 'کارشناسی',
        'master'     => 'کارشناسی ارشد',
        'phd'        => 'دکتری',
        'other'      => 'سایر',
    ];

    public const FIELDS = [
        'high' => [
            'math'       => 'ریاضی و فیزیک',
            'science'    => 'علوم تجربی',
            'humanities' => 'علوم انسانی',
            'islamic'    => 'معارف اسلامی',
            'other'      => 'سایر',
        ],
        'vocational' => [
            'industry' => 'صنعت',
            'service'  => 'خدمات',
            'agri'     => 'کشاورزی',
            'art'      => 'هنر',
            'other'    => 'سایر',
        ],
    ];

    public const GRADES = ['10' => 'دهم', '11' => 'یازدهم', '12' => 'دوازدهم'];

    public const REFERRAL = [
        'friend'    => 'دوستان و آشناها',
        'telegram'  => 'تلگرام',
        'instagram' => 'اینستاگرام',
        'google'    => 'جستجوی گوگل',
        'github'    => 'گیت‌هاب',
        'other'     => 'جای دیگه',
    ];

    public const GOALS = [
        'tasks'   => '📋 مدیریت کارها',
        'habits'  => '🔥 ساختن عادت',
        'study'   => '📚 برنامه‌ی درسی و مطالعه',
        'work'    => '💼 کار و پروژه',
        'writing' => '✍️ نوشتن در وبلاگ',
        'chat'    => '💬 چت و آشنایی',
    ];

    public const ORGANIZED = [1 => 'اصلاً', 2 => 'کم', 3 => 'متوسط', 4 => 'زیاد', 5 => 'خیلی'];

    protected $fillable = ['user_id', 'education', 'field', 'grade', 'referral', 'organized', 'goals'];

    protected $casts = [
        'goals' => 'array',
    ];
}