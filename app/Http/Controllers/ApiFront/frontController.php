<?php

namespace App\Http\Controllers\ApiFront;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use DB;

class frontController extends Controller
{
    // public function getDashboardIndex()
    // {
    //     return view('contratos.front.dashboard');
    // }
    // public function getCreateContract()
    // {
    //     return view('contratos.front.createContract');
    // }
    // public function getEDP()
    // {
    //     return view('contratos.front.paymentStatus');
    // }
    // public function getPaymentStatusReview()
    // {
    //     return view('contratos.front.paymentStatusReview');
    // }
    // public function getProfileSettings()
    // {
    //     return view('contratos.front.profileSettings');
    // }

    // public function getEDPList()
    // {
    //     return view('contratos.front.edpList');
    // }
    
    public function getSpa()
    {
        return view('layouts.appvue');
    }
    
}
