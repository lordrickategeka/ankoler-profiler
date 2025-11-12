<?php

namespace App\Livewire;

use Livewire\Component;

class PersonProducts extends Component
{
    public $showProductForm = false;
    public $productForm = [
        'name' => '',
        'category' => '',
        'stock' => '',
        'price' => '',
        'status' => '',
        'image' => null,
    ];

    protected $rules = [
        'productForm.name' => 'required|string|max:255',
        'productForm.category' => 'required|string|max:255',
        'productForm.stock' => 'required|integer|min:0',
        'productForm.price' => 'required|numeric|min:0',
        'productForm.status' => 'required|string',
        'productForm.image' => 'nullable|image|max:2048',
    ];

    public function showProductForm()
    {
        $this->showProductForm = true;
    }

    public function hideProductForm()
    {
        $this->showProductForm = false;
        $this->reset('productForm');
    }

    public function createProduct()
    {
        $this->validate();
        // Here you would save the product to the database or array
        // For now, just reset and close modal
        $this->hideProductForm();
        session()->flash('success', 'Product created (not persisted).');
    }

    public function render()
    {
        return view('livewire.person-products');
    }
}
