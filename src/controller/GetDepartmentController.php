<?php

namespace App\controller;

use App\model\Departement;

class GetDepartmentController {

    protected $departments = array();

    public function getAllDepartments() {
        return Departement::orderBy('nom_departement')->get()->toArray();
    }
}