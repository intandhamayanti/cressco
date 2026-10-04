<?php

namespace App\View\Components;

use App\Models\Tenant;
use Illuminate\View\Component;
use Illuminate\View\View;

class TutorLayout extends Component
{
    public function __construct(
        public ?Tenant $tenant = null,
        public ?string $title = null,
        public ?string $breadcrumbSub = null,
    ) {}

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.tutor');
    }
}
