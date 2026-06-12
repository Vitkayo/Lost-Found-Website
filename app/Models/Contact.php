<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'gender', 'email', 'phone', 'address', 'company_id'])]
class Contact extends Model
{
}
