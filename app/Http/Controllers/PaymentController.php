<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Payments/Index');
    }
}
