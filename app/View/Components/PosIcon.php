<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class PosIcon extends Component
{
    public function __construct(
        public string $name,
        public string $class = 'w-5 h-5',
    ) {}

    public function render(): View
    {
        return view('components.pos-icon');
    }
}
