<?php

namespace App\Models;

use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use Notifiable, HasFactory;
    protected $table = 'users';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'uuid',
        'name',
        'name_complete',
        'password',
        'role_id',
        'estado',
        'created_at',
        'updated_at'
    ];

    public static function storeUser($userRequest)
    {
        return User::create([
            'uuid' => Str::uuid(),
            'name' => $userRequest['name'],
            'name_complete' => $userRequest['name_complete'],
            'password' => Hash::make($userRequest['password']),
            'role_id' => $userRequest['role_id'],
            'estado' => 1,
            'created_at' => now()
        ]);
    }

    public function updateUser($userRequest)
    {
        $data = [
            'name_complete' => $userRequest['name_complete_edit'],
            'role_id' => $userRequest['role_id_edit'],
            'updated_at' => now()
        ];

        if (!empty($userRequest['password_edit'])) {
            $data['password'] = md5($userRequest['password_edit']);
        }
        $this->update($data);
    }

    public function deleteUser()
    {
        $this->update([
            'estado' => 0,
            'updated_at' => now()
        ]);
    }

    //Relaciones con tablas 
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}
