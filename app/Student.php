<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Fadion\Bouncy\BouncyTrait;

class Student extends Model {

    use BouncyTrait;

    protected $fillable = [];

    protected $dates = [];

    public static $rules = [
        // Validation rules
    ];

    // Relationships

}
