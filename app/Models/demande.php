<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class demande extends Model
{
    use HasFactory;
        protected $fillable = ['id','raison','dateDemande','dateSortie','dateRetour','statutId','emailEmployee'];

}
