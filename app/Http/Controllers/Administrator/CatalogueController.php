<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use Closure;
use Illuminate\Database\DeadlockException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use PDOException;

abstract class CatalogueController extends Controller
{
    protected function mutate(Closure $action, string $route, array $parameters = []): RedirectResponse
    {
        try {
            $action();
        } catch (DeadlockException) {
            throw ValidationException::withMessages(['catalogue' => 'The railway configuration is busy. Retry this operation; no partial change was saved.']);
        } catch (PDOException) {
            throw ValidationException::withMessages(['catalogue' => 'This change conflicts with existing railway records. Check unique codes, numbers and related records.']);
        }

        return redirect()->route($route, $parameters)->with('status', 'Railway configuration saved.');
    }
}
