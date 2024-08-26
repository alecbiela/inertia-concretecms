<?php
/**
 * TODO: Refactor this for Concrete CMS
 */
namespace Inertia;

use Concrete\Core\Http\Request;

class Controller
{
    public function __invoke(Request $request): Response
    {
        return Inertia::render(
            $request->route()->defaults['component'],
            $request->route()->defaults['props']
        );
    }
}
