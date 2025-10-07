<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Categoria;
use App\Models\Subcategoria;
use App\Models\Recurso;
use App\Models\Estado;

class RecursoForm extends Component
{
    public $categorias = [];
    public $subcategorias = [];
    public $estados = [];

    public $nombre = '';

    public $categoriaId = null;
    public $subcategoriaId = null;
    public $id_estado = null;
    public $serie = '';
    

    public function mount()
    {
        $this->categorias = Categoria::all();
        $this->estados = Estado::all();
        $this->subcategorias = collect(); // importante: arranca vacío
        logger('Categorías cargadas:', Categoria::pluck('nombre_categoria', 'id')->toArray());

    }

    // Este método se dispara automáticamente al cambiar el select de categoría
    public function updatedCategoriaId($value)
{
    logger('Categoría seleccionada:', ['id' => $value]);
    $this->subcategorias = Subcategoria::where('categoria_id', $value)->get();
}


    public function save()
{
    $validated = $this->validate([
        'subcategoriaId' => 'required|exists:subcategoria,id',
        'serie' => 'required|string|max:255',
        'id_estado' => 'required|exists:estado,id',
        'nombre' => 'required|string|max:255',
    ]);

    Recurso::create([
        'id_subcategoria' => $validated['subcategoriaId'],
        'serie' => $validated['serie'],
        'id_estado' => $validated['id_estado'],
        'nombre' => $validated['nombre'],
        'id_usuario_creacion' => auth()->id(),
        'id_usuario_modificacion' => auth()->id(),
    ]);

    session()->flash('success', 'Recurso creado correctamente.');

    $this->reset(['categoriaId', 'subcategoriaId', 'serie', 'id_estado', 'nombre']);
    $this->subcategorias = collect();
}


    public function render()
    {
        return view('livewire.recurso-form');
    }
}


