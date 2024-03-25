<?php

namespace App\model;

class Photo extends \Illuminate\Database\Eloquent\Model {
    protected $table = 'photo';
    protected $primaryKey = 'id_photo';
    public $timestamps = false;

    public function annonce()
    {
        return $this->belongsTo('App\model\Annonce', 'id_annonce');
    }
}

?>