<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Rol;
use App\Models\EstadoUsuario;


class UserTabs extends Component
{
    public $tab = 'todos';
    public $search = '';

    public function setTab($tab)
    {
        $this->tab = $tab;
    }

    public function render()
    {
        $usuarios = User::with('rol', 'estado')
            ->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
            ->orWhere('email', 'like', '%' . $this->search . '%');
            })

            ->get();

        $roles = Rol::with('usuarios')->get();

        return view('livewire.user-tabs', compact('usuarios', 'roles'));
    }
}

