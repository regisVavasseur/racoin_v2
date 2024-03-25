<?php

namespace App\model;

class Annonce extends \Illuminate\Database\Eloquent\Model {
    protected $table = 'annonce';
    protected $primaryKey = 'id_annonce';
    public $timestamps = false;
    public $links = null;


    public function annonceur()
    {
        return $this->belongsTo('App\model\Annonceur', 'id_annonceur');
    }

    public function photo()
    {
        return $this->hasMany('App\model\Photo', 'id_photo');
    }

}
?>
