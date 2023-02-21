<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class Edplog extends Sximo
{
  protected $table = 'tbl_';
  protected $primaryKey = '';

  public function __construct() {
    parent::__construct();

  }
}
