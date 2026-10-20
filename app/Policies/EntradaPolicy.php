<?php

namespace App\Policies;

use App\Models\Entrada;
use App\Models\User;

class EntradaPolicy
{
    public function update(User $user, Entrada $entrada): bool
    {
        return (int) $user->id === (int) $entrada->autor_id;
    }

    public function delete(User $user, Entrada $entrada): bool
    {
        return $this->update($user, $entrada);
    }
}
