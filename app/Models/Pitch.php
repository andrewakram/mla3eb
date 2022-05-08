<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pitch extends Model
{
    use HasFactory;

    protected $table= 'pitches';

    protected $fillable = ['name','stadium_id','slot'];
    protected $hidden = ['created_at','updated_at'];
    protected $appends = ['stadium_name'];

    public function Stadium(){
        return $this->belongsTo(Stadium::class,'stadium_id');
    }

    public function getStadiumNameAttribute(){
        return $this->Stadium()->first()->name;
    }


}
