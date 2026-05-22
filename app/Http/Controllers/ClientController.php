<?php

namespace App\Http\Controllers;

use App\Repositories\ClientRepository;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    public function __construct(private ClientRepository $clients) {}

    public function index(): Response
    {
        return Inertia::render('Clients/Index', [
            'clients' => $this->clients->paginate(),
        ]);
    }
}
