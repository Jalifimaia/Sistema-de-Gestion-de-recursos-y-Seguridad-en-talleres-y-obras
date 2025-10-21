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
    protected $listeners = ['usuarioActualizado' => '$refresh'];


    public function setTab($tab)
    {
        $this->tab = $tab;
    }

    public function render()
    {
        $usuarios = User::with(['rol', 'estado'])
            ->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                      ->orWhereHas('rol', function ($q) {
                          $q->where('nombre_rol', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('estado', function ($q) {
                        $q->where('nombre', 'like', '%' . $this->search . '%');
                      });
            })
            ->get();

        $roles = Rol::with('usuarios')->get();

        return view('livewire.user-tabs', [
            'usuarios' => $usuarios,
            'roles' => $roles,
        ]);
    }

public function darDeBaja($usuarioId)
{
    $usuario = User::find($usuarioId);

    if ($usuario) {
        $usuario->id_estado = 2;
        $usuario->save();
    }

    // Forzar re-render del componente
    $this->render(); // opcional si usás propiedades computadas
}



}
