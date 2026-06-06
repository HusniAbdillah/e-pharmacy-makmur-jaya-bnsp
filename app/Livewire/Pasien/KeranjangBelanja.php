<?php

namespace App\Livewire\Pasien;

use Livewire\Component;

class KeranjangBelanja extends Component
{
    public function increment(int $obatId): void
    {
        $cart = session()->get('cart', []);
        if (isset($cart[$obatId])) {
            $cart[$obatId]['quantity']++;
            session()->put('cart', $cart);
        }
    }

    public function decrement(int $obatId): void
    {
        $cart = session()->get('cart', []);
        if (isset($cart[$obatId]) && $cart[$obatId]['quantity'] > 1) {
            $cart[$obatId]['quantity']--;
            session()->put('cart', $cart);
        }
    }

    public function remove(int $obatId): void
    {
        $cart = session()->get('cart', []);
        unset($cart[$obatId]);
        session()->put('cart', $cart);
        session()->flash('success', 'Item dihapus dari keranjang.');
    }

    public function clearCart(): void
    {
        session()->forget('cart');
        session()->flash('success', 'Keranjang dikosongkan.');
    }

    public function render()
    {
        $cart = session()->get('cart', []);
        $total = collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']);

        return view('livewire.pasien.keranjang-belanja', [
            'cart'  => $cart,
            'total' => $total,
        ]);
    }
}
